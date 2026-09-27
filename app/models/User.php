<?php
namespace App\Models;

use App\Core\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $row = Database::query('SELECT * FROM users WHERE email = ? LIMIT 1', [$email])->fetch();
        return $row ?: null;
    }

    public static function findById(int $id): ?array
    {
        $row = Database::query('SELECT * FROM users WHERE id = ? LIMIT 1', [$id])->fetch();
        return $row ?: null;
    }

    public static function all(): array
    {
        return Database::query(
            'SELECT id, name, email, role, is_active, last_login_at, created_at
             FROM users ORDER BY created_at DESC'
        )->fetchAll();
    }

    public static function create(array $d): int
    {
        Database::query(
            'INSERT INTO users (name, email, password_hash, role, is_active, created_by)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $d['name'],
                $d['email'],
                password_hash($d['password'], PASSWORD_BCRYPT),
                $d['role'],
                !empty($d['is_active']) ? 1 : 0,
                $d['created_by'] ?? null,
            ]
        );
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        Database::query(
            'UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?',
            [$d['name'], $d['email'], $d['role'], !empty($d['is_active']) ? 1 : 0, $id]
        );
    }

    public static function updatePassword(int $id, string $newPlain): void
    {
        Database::query(
            'UPDATE users SET password_hash = ?, last_password_change = NOW() WHERE id = ?',
            [password_hash($newPlain, PASSWORD_BCRYPT), $id]
        );
    }

    public static function deactivate(int $id): void
    {
        Database::query('UPDATE users SET is_active = 0 WHERE id = ?', [$id]);
    }

    public static function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) c FROM users WHERE email = ?';
        $params = [$email];
        if ($exceptId) { $sql .= ' AND id != ?'; $params[] = $exceptId; }
        return (int)Database::query($sql, $params)->fetch()['c'] > 0;
    }

    public static function updateLastLogin(int $id): void
    {
        Database::query('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$id]);
    }

    public static function logAttempt(string $email, string $ip): void
    {
        Database::query(
            'INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)',
            [$email, $ip]
        );
    }

    public static function recentAttempts(string $email, int $minutes = 15): int
    {
        $row = Database::query(
            'SELECT COUNT(*) AS c FROM login_attempts
             WHERE email = ? AND attempted_at > (NOW() - INTERVAL ? MINUTE)',
            [$email, $minutes]
        )->fetch();
        return (int)($row['c'] ?? 0);
    }

    public static function clearAttempts(string $email): void
    {
        Database::query('DELETE FROM login_attempts WHERE email = ?', [$email]);
    }

    public static function activeAdminCount(): int
    {
        return (int)Database::query(
            "SELECT COUNT(*) c FROM users WHERE role = 'admin' AND is_active = 1"
        )->fetch()['c'];
    }
}