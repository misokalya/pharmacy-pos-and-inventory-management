<?php
namespace App\Models;

use App\Core\Database;

class Setting
{
    /** All settings as key => value (cached per request) */
    public static function all(): array
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [];
            foreach (Database::query('SELECT `key`, `value` FROM settings')->fetchAll() as $r) {
                $cache[$r['key']] = $r['value'];
            }
        }
        return $cache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $v = self::get($key);
        return $v === null ? $default : (int)$v;
    }

    public static function getFloat(string $key, float $default = 0.0): float
    {
        $v = self::get($key);
        return $v === null ? $default : (float)$v;
    }

    /** Bulk upsert (one transaction) */
    public static function updateMany(array $values): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
            );
            foreach ($values as $k => $v) {
                $stmt->execute([$k, (string)$v]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** Whitelist of keys the UI may write */
    public static function editableKeys(): array
    {
        return [
            'app_name', 'currency', 'currency_symbol', 'tax_rate',
            'expiry_alert_days', 'critical_expiry_days',
            'alert_email', 'expiry_digest_window',
        ];
    }
}