<?php
namespace App\Models;

use App\Core\Database;

class Supplier
{
    public static function all(): array
    {
        return Database::query(
            'SELECT s.*, (SELECT COUNT(*) FROM batches b WHERE b.supplier_id = s.id) AS batch_count
             FROM suppliers s ORDER BY s.name ASC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::query('SELECT * FROM suppliers WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(array $d): int
    {
        Database::query(
            'INSERT INTO suppliers (name, contact_person, phone, email, address)
             VALUES (?, ?, ?, ?, ?)',
            [$d['name'], $d['contact_person'] ?: null, $d['phone'] ?: null,
             $d['email'] ?: null, $d['address'] ?: null]
        );
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        Database::query(
            'UPDATE suppliers SET name=?, contact_person=?, phone=?, email=?, address=? WHERE id=?',
            [$d['name'], $d['contact_person'] ?: null, $d['phone'] ?: null,
             $d['email'] ?: null, $d['address'] ?: null, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::query('DELETE FROM suppliers WHERE id = ?', [$id]);
    }
}