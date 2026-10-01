<?php
namespace App\Models;

use App\Core\Database;

class Batch
{
    /** Expiry thresholds, read from Settings (falls back to safe defaults) */
    public static function expiryDays(): array
    {
        return [
            'expiry_alert_days'    => Setting::getInt('expiry_alert_days', 90),
            'critical_expiry_days' => Setting::getInt('critical_expiry_days', 30),
        ];
    }

    /** @return 'expired'|'critical'|'near'|'valid' */
    public static function expiryState(string $expiryDate): string
    {
        $today    = new \DateTimeImmutable('today');
        $expiry   = new \DateTimeImmutable($expiryDate);
        $daysLeft = (int)$today->diff($expiry)->format('%r%a');
        $cfg      = self::expiryDays();

        if ($daysLeft < 0)                             return 'expired';
        if ($daysLeft <= $cfg['critical_expiry_days']) return 'critical';
        if ($daysLeft <= $cfg['expiry_alert_days'])    return 'near';
        return 'valid';
    }

    /**
     * Paginated list with optional filters.
     * @param array{q?:string,state?:string,product_id?:int} $filters
     */
    public static function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR b.batch_no LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['product_id'])) {
            $where[] = 'b.product_id = ?';
            $params[] = (int)$filters['product_id'];
        }
        if (!empty($filters['state'])) {
            $cfg = self::expiryDays();
            switch ($filters['state']) {
                case 'expired':
                    $where[] = 'b.expiry_date < CURDATE()'; break;
                case 'critical':
                    $where[] = "b.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$cfg['critical_expiry_days']} DAY)"; break;
                case 'near':
                    $where[] = "b.expiry_date > DATE_ADD(CURDATE(), INTERVAL {$cfg['critical_expiry_days']} DAY)
                                AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL {$cfg['expiry_alert_days']} DAY)"; break;
                case 'valid':
                    $where[] = "b.expiry_date > DATE_ADD(CURDATE(), INTERVAL {$cfg['expiry_alert_days']} DAY)"; break;
                case 'with_stock':
                    $where[] = 'b.quantity_remaining > 0'; break;
                case 'depleted':
                    $where[] = 'b.quantity_remaining = 0'; break;
            }
        }

        $whereSql = implode(' AND ', $where);
        $offset   = max(0, ($page - 1) * $perPage);

        $total = (int)Database::query(
            "SELECT COUNT(*) c FROM batches b JOIN products p ON p.id = b.product_id WHERE $whereSql",
            $params
        )->fetch()['c'];

        $rows = Database::query(
            "SELECT b.*, p.name AS product_name, p.sku, p.unit,
                    s.name AS supplier_name
             FROM batches b
             JOIN products p ON p.id = b.product_id
             LEFT JOIN suppliers s ON s.id = b.supplier_id
             WHERE $whereSql
             ORDER BY b.expiry_date ASC
             LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        foreach ($rows as &$r) {
            $r['expiry_state']   = self::expiryState($r['expiry_date']);
            $r['days_to_expiry'] = (int)(new \DateTimeImmutable('today'))
                ->diff(new \DateTimeImmutable($r['expiry_date']))->format('%r%a');
        }

        return [
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => max(1, (int)ceil($total / $perPage)),
        ];
    }

    /** Full details of one batch */
    public static function find(int $id): ?array
    {
        $row = Database::query(
            'SELECT b.*, p.name AS product_name, p.sku, p.unit,
                    s.name AS supplier_name, u.name AS receiver_name
             FROM batches b
             JOIN products p ON p.id = b.product_id
             LEFT JOIN suppliers s ON s.id = b.supplier_id
             LEFT JOIN users u ON u.id = b.received_by
             WHERE b.id = ?',
            [$id]
        )->fetch();
        if (!$row) return null;
        $row['expiry_state']   = self::expiryState($row['expiry_date']);
        $row['days_to_expiry'] = (int)(new \DateTimeImmutable('today'))
            ->diff(new \DateTimeImmutable($row['expiry_date']))->format('%r%a');
        return $row;
    }

    /** Create a batch + record 'in' movement (atomic) */
    public static function receive(array $d): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query(
                'INSERT INTO batches
                 (product_id, supplier_id, batch_no, quantity_received, quantity_remaining,
                  cost_price, selling_price, manufacture_date, expiry_date, received_by, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $d['product_id'], $d['supplier_id'] ?: null, $d['batch_no'],
                    $d['quantity'], $d['quantity'],
                    $d['cost_price'], $d['selling_price'],
                    $d['manufacture_date'] ?: null, $d['expiry_date'],
                    $d['received_by'], $d['notes'] ?: null,
                ]
            );
            $batchId = (int)$pdo->lastInsertId();

            Database::query(
                'INSERT INTO stock_movements (batch_id, product_id, type, quantity, reference, user_id)
                 VALUES (?, ?, "in", ?, ?, ?)',
                [$batchId, $d['product_id'], $d['quantity'], 'Batch ' . $d['batch_no'], $d['received_by']]
            );

            $pdo->commit();
            return $batchId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Available batches (with stock), sorted by FEFO */
    public static function availableForProduct(int $productId): array
    {
        return Database::query(
            'SELECT * FROM batches
             WHERE product_id = ? AND quantity_remaining > 0 AND expiry_date >= CURDATE()
             ORDER BY expiry_date ASC, id ASC',
            [$productId]
        )->fetchAll();
    }

    /** Total stock in for one product (excluding expired by default) */
    public static function stockForProduct(int $productId, bool $includeExpired = false): int
    {
        $sql    = 'SELECT COALESCE(SUM(quantity_remaining),0) c FROM batches WHERE product_id = ?';
        $params = [$productId];
        if (!$includeExpired) $sql .= ' AND expiry_date >= CURDATE()';
        return (int)Database::query($sql, $params)->fetch()['c'];
    }

    /** Adjustment (writes movement + updates remaining) */
    public static function adjust(int $batchId, int $newQty, string $reason, int $userId): void
    {
        $batch = self::find($batchId);
        if (!$batch) throw new \RuntimeException('Batch not found.');

        $delta = $newQty - (int)$batch['quantity_remaining'];
        if ($delta === 0) return;

        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            Database::query('UPDATE batches SET quantity_remaining = ? WHERE id = ?', [$newQty, $batchId]);
            Database::query(
                'INSERT INTO stock_movements (batch_id, product_id, type, quantity, reference, user_id)
                 VALUES (?, ?, "adjustment", ?, ?, ?)',
                [$batchId, (int)$batch['product_id'], $delta, $reason ?: 'Manual adjustment', $userId]
            );
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Aggregates for dashboard / reports */
    public static function summaryCounts(): array
    {
        $cfg = self::expiryDays();
        $row = Database::query(
            "SELECT
              SUM(CASE WHEN expiry_date < CURDATE() AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS expired,
              SUM(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$cfg['critical_expiry_days']} DAY) AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS critical,
              SUM(CASE WHEN expiry_date > DATE_ADD(CURDATE(), INTERVAL {$cfg['critical_expiry_days']} DAY)
                       AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL {$cfg['expiry_alert_days']} DAY) AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS near,
              SUM(CASE WHEN quantity_remaining > 0 THEN 1 ELSE 0 END) AS total_with_stock
             FROM batches"
        )->fetch();
        return array_map('intval', $row ?: []);
    }
}