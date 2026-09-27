<?php
namespace App\Models;

use App\Core\Database;

class Product
{
    public static function paginate(string $search = '', ?int $categoryId = null, int $page = 1, int $perPage = 10): array
    {
        $where = ['1=1'];
        $params = [];

        if ($search !== '') {
            $where[] = '(p.name LIKE ? OR p.generic_name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
            $like = "%$search%";
            array_push($params, $like, $like, $like, $like);
        }
        if ($categoryId) {
            $where[] = 'p.category_id = ?';
            $params[] = $categoryId;
        }

        $whereSql = implode(' AND ', $where);
        $offset = max(0, ($page - 1) * $perPage);

        $total = (int)Database::query(
            "SELECT COUNT(*) c FROM products p WHERE $whereSql", $params
        )->fetch()['c'];

        $rows = Database::query(
            "SELECT p.*, c.name AS category_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE $whereSql
             ORDER BY p.created_at DESC
             LIMIT $perPage OFFSET $offset",
            $params
        )->fetchAll();

        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function find(int $id): ?array
    {
        $row = Database::query('SELECT * FROM products WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(array $d): int
    {
        Database::query(
            'INSERT INTO products (sku, barcode, name, image_path, generic_name, category_id, unit, reorder_level, cost_price, selling_price, requires_prescription, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $d['sku'], $d['barcode'] ?: null, $d['name'], $d['image_path'] ?: null,
                $d['generic_name'] ?: null,
                $d['category_id'] ?: null, $d['unit'], $d['reorder_level'],
                $d['cost_price'], $d['selling_price'],
                !empty($d['requires_prescription']) ? 1 : 0,
                !empty($d['is_active']) ? 1 : 0,
            ]
        );
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        Database::query(
            'UPDATE products SET sku=?, barcode=?, name=?, image_path=?, generic_name=?, category_id=?, unit=?, reorder_level=?, cost_price=?, selling_price=?, requires_prescription=?, is_active=?
            WHERE id=?',
            [
                $d['sku'], $d['barcode'] ?: null, $d['name'], $d['image_path'] ?: null,
                $d['generic_name'] ?: null,
                $d['category_id'] ?: null, $d['unit'], $d['reorder_level'],
                $d['cost_price'], $d['selling_price'],
                !empty($d['requires_prescription']) ? 1 : 0,
                !empty($d['is_active']) ? 1 : 0,
                $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        // Delete associated image first
        $row = self::find($id);
        if ($row && !empty($row['image_path'])) {
            delete_upload($row['image_path']);
        }
        Database::query('DELETE FROM products WHERE id = ?', [$id]);
    }

    public static function skuExists(string $sku, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) c FROM products WHERE sku = ?';
        $params = [$sku];
        if ($exceptId) { $sql .= ' AND id != ?'; $params[] = $exceptId; }
        return (int)Database::query($sql, $params)->fetch()['c'] > 0;
    }

    public static function barcodeExists(string $barcode, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) c FROM products WHERE barcode = ?';
        $params = [$barcode];
        if ($exceptId) { $sql .= ' AND id != ?'; $params[] = $exceptId; }
        return (int)Database::query($sql, $params)->fetch()['c'] > 0;
    }

    public static function allActive(): array
    {
        return Database::query(
            'SELECT id, sku, name, unit, selling_price FROM products WHERE is_active = 1 ORDER BY name'
        )->fetchAll();
    }

    public static function imageUrl(?array $product): ?string
    {
        if (!$product || empty($product['image_path'])) return null;
        return base_url($product['image_path']);
    }
}