<?php
namespace App\Models;

use App\Core\Database;

class Sale
{
    /**
     * Complete a sale atomically.
     * $cart: [ ['product_id'=>int,'quantity'=>int], ... ]
     * Returns [sale_id, invoice_no]
     */
    public static function checkout(array $data, array $cart, int $userId): array
    {
        if (empty($cart)) {
            throw new \RuntimeException('Cart is empty.');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // 1. Allocate stock from batches using FEFO
            $allocations = [];   // [ [batch, product, qty], ... ]
            $subtotal = 0.0;

            foreach ($cart as $line) {
                $pid = (int)$line['product_id'];
                $qty = (int)$line['quantity'];
                if ($qty <= 0) throw new \RuntimeException('Invalid quantity.');

                $product = Product::find($pid);
                if (!$product || !(int)$product['is_active']) {
                    throw new \RuntimeException('Product not available.');
                }

                $batches = Batch::availableForProduct($pid); // FEFO, non-expired only
                $available = array_sum(array_column($batches, 'quantity_remaining'));

                if ($available < $qty) {
                    throw new \RuntimeException(
                        "Insufficient stock for {$product['name']}. Available: {$available}, requested: {$qty}."
                    );
                }

                $remaining = $qty;
                foreach ($batches as $b) {
                    if ($remaining <= 0) break;
                    $take = min($remaining, (int)$b['quantity_remaining']);
                    if ($take <= 0) continue;

                    $unitPrice = (float)$b['selling_price'];
                    $lineTotal = round($unitPrice * $take, 2);
                    $subtotal += $lineTotal;

                    $allocations[] = [
                        'batch'      => $b,
                        'product'    => $product,
                        'quantity'   => $take,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                    ];
                    $remaining -= $take;
                }
            }

            // 2. Compute totals
            $discount = max(0, (float)($data['discount'] ?? 0));
            $taxRate  = (float)Database::query(
                "SELECT `value` FROM settings WHERE `key` = 'tax_rate'"
            )->fetch()['value'] ?? 0;
            $taxBase  = max(0, $subtotal - $discount);
            $tax      = round($taxBase * ($taxRate / 100), 2);
            $total    = round($taxBase + $tax, 2);
            $paid     = max(0, (float)($data['amount_paid'] ?? 0));
            $change   = round(max(0, $paid - $total), 2);

            if ($paid + 0.001 < $total) {
                throw new \RuntimeException('Amount paid is less than total.');
            }

            // 3. Invoice number (INV-YYYY-00001)
            $invoiceNo = self::nextInvoiceNumber();

            // 4. Insert sale header
            Database::query(
                'INSERT INTO sales
                 (invoice_no, user_id, customer_name, customer_phone, subtotal, discount, tax, total,
                  amount_paid, change_due, payment_method, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $invoiceNo, $userId,
                    $data['customer_name'] ?: null, $data['customer_phone'] ?: null,
                    $subtotal, $discount, $tax, $total,
                    $paid, $change,
                    $data['payment_method'] ?? 'cash',
                    $data['notes'] ?: null,
                ]
            );
            $saleId = (int)$pdo->lastInsertId();

            // 5. Insert sale items + deduct batches + record movements
            foreach ($allocations as $a) {
                $b = $a['batch'];
                $p = $a['product'];

                Database::query(
                    'INSERT INTO sale_items
                     (sale_id, batch_id, product_id, product_name, batch_no, expiry_date,
                      quantity, unit_price, line_total)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [
                        $saleId, $b['id'], $p['id'], $p['name'], $b['batch_no'], $b['expiry_date'],
                        $a['quantity'], $a['unit_price'], $a['line_total'],
                    ]
                );

                Database::query(
                    'UPDATE batches SET quantity_remaining = quantity_remaining - ? WHERE id = ?',
                    [$a['quantity'], $b['id']]
                );

                Database::query(
                    'INSERT INTO stock_movements (batch_id, product_id, type, quantity, reference, user_id)
                     VALUES (?, ?, "out", ?, ?, ?)',
                    [$b['id'], $p['id'], -$a['quantity'], $invoiceNo, $userId]
                );
            }

            $pdo->commit();
            return ['sale_id' => $saleId, 'invoice_no' => $invoiceNo];

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function nextInvoiceNumber(): string
    {
        $year = (int)date('Y');
        Database::query(
            'INSERT INTO invoice_counters (`year`, `counter`) VALUES (?, 1)
             ON DUPLICATE KEY UPDATE `counter` = `counter` + 1',
            [$year]
        );
        $count = (int)Database::query(
            'SELECT `counter` FROM invoice_counters WHERE `year` = ?', [$year]
        )->fetch()['counter'];
        return sprintf('INV-%d-%05d', $year, $count);
    }

    public static function find(int $id): ?array
    {
        $sale = Database::query(
            'SELECT s.*, u.name AS cashier_name
             FROM sales s JOIN users u ON u.id = s.user_id
             WHERE s.id = ?', [$id]
        )->fetch();
        if (!$sale) return null;

        $sale['items'] = Database::query(
            'SELECT * FROM sale_items WHERE sale_id = ? ORDER BY id ASC', [$id]
        )->fetchAll();

        return $sale;
    }

    public static function findByInvoice(string $invoiceNo): ?array
    {
        $row = Database::query('SELECT id FROM sales WHERE invoice_no = ?', [$invoiceNo])->fetch();
        return $row ? self::find((int)$row['id']) : null;
    }

    public static function paginate(array $filters = [], int $page = 1, int $perPage = 15): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = '(s.invoice_no LIKE ? OR s.customer_name LIKE ? OR s.customer_phone LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['from'])) { $where[] = 'DATE(s.created_at) >= ?'; $params[] = $filters['from']; }
        if (!empty($filters['to']))   { $where[] = 'DATE(s.created_at) <= ?'; $params[] = $filters['to']; }
        if (!empty($filters['user_id'])) { $where[] = 's.user_id = ?'; $params[] = (int)$filters['user_id']; }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $total = (int)Database::query(
            "SELECT COUNT(*) c FROM sales s WHERE $whereSql", $params
        )->fetch()['c'];

        $rows = Database::query(
            "SELECT s.*, u.name AS cashier_name,
                    (SELECT COUNT(*) FROM sale_items si WHERE si.sale_id = s.id) AS item_count
             FROM sales s JOIN users u ON u.id = s.user_id
             WHERE $whereSql
             ORDER BY s.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        return [
            'rows'    => $rows,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => max(1, (int)ceil($total / $perPage)),
        ];
    }

    public static function todaySummary(): array
    {
        $row = Database::query(
            "SELECT COUNT(*) AS sales_count, COALESCE(SUM(total),0) AS revenue
             FROM sales WHERE DATE(created_at) = CURDATE()"
        )->fetch();
        return ['count' => (int)$row['sales_count'], 'revenue' => (float)$row['revenue']];
    }
}