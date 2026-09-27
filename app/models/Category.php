<?php
namespace App\Models;

use App\Core\Database;

class Category
{
    public static function all(): array
    {
        return Database::query(
            'SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS product_count
             FROM categories c ORDER BY c.name ASC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::query('SELECT * FROM categories WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO categories (name, description) VALUES (?, ?)',
            [$data['name'], $data['description'] ?? null]
        );
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::query(
            'UPDATE categories SET name = ?, description = ? WHERE id = ?',
            [$data['name'], $data['description'] ?? null, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM categories WHERE id = ?', [$id]);
    }

    public static function nameExists(string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) c FROM categories WHERE name = ?';
        $params = [$name];
        if ($exceptId) { $sql .= ' AND id != ?'; $params[] = $exceptId; }
        return (int)Database::query($sql, $params)->fetch()['c'] > 0;
    }
}