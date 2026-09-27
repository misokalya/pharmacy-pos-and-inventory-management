<?php
namespace App\Models;

use App\Core\Database;

class Report
{
    /**
     * Expiry forecast: how many units/batches expire in the next N days.
     * $window in days (30, 60, 90, ...)
     */
    public static function expiryForecast(int $window = 90): array
    {
        // Buckets: [0-30], [31-60], [61-90], [expired]
        return Database::query(
            "SELECT
                SUM(CASE WHEN expiry_date < CURDATE() AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS expired_batches,
                SUM(CASE WHEN expiry_date < CURDATE() AND quantity_remaining > 0 THEN quantity_remaining ELSE 0 END) AS expired_units,
                SUM(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS d30_batches,
                SUM(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND quantity_remaining > 0 THEN quantity_remaining ELSE 0 END) AS d30_units,
                SUM(CASE WHEN expiry_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 31 DAY) AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS d60_batches,
                SUM(CASE WHEN expiry_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 31 DAY) AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) AND quantity_remaining > 0 THEN quantity_remaining ELSE 0 END) AS d60_units,
                SUM(CASE WHEN expiry_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 61 DAY) AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND quantity_remaining > 0 THEN 1 ELSE 0 END) AS d90_batches,
                SUM(CASE WHEN expiry_date BETWEEN DATE_ADD(CURDATE(), INTERVAL 61 DAY) AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) AND quantity_remaining > 0 THEN quantity_remaining ELSE 0 END) AS d90_units
             FROM batches
             WHERE quantity_remaining > 0"
        )->fetch() ?: [];
    }

    /** Full list of batches expiring within $window days (incl. already expired) */
    public static function expiryList(int $window = 90): array
    {
        $rows = Database::query(
            "SELECT b.*, p.name AS product_name, p.sku, p.unit,
                    s.name AS supplier_name,
                    DATEDIFF(b.expiry_date, CURDATE()) AS days_left
             FROM batches b
             JOIN products p ON p.id = b.product_id
             LEFT JOIN suppliers s ON s.id = b.supplier_id
             WHERE b.quantity_remaining > 0
               AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
             ORDER BY b.expiry_date ASC",
            [$window]
        )->fetchAll();

        foreach ($rows as &$r) {
            $r['expiry_state'] = Batch::expiryState($r['expiry_date']);
            $r['value_at_cost'] = (float)$r['cost_price'] * (int)$r['quantity_remaining'];
        }
        return $rows;
    }

    /** Products whose total remaining stock is at or below reorder_level */
public static function lowStock(): array
{
    return Database::query(
        "SELECT * FROM (
            SELECT p.id, p.name, p.sku, p.unit, p.reorder_level,
                   c.name AS category_name,
                   COALESCE(SUM(b.quantity_remaining), 0) AS current_stock
            FROM products p
            LEFT JOIN categories c ON c.id = p.category_id
            LEFT JOIN batches b ON b.product_id = p.id
                                AND b.quantity_remaining > 0
                                AND b.expiry_date >= CURDATE()
            WHERE p.is_active = 1
            GROUP BY p.id, p.name, p.sku, p.unit, p.reorder_level, c.name
        ) AS stock_summary
        WHERE current_stock <= reorder_level
        ORDER BY (current_stock - reorder_level) ASC, name ASC"
    )->fetchAll();
}

    /** Top-selling products in a date range */
    public static function topProducts(string $from, string $to, int $limit = 10): array
    {
        return Database::query(
            "SELECT si.product_id, si.product_name,
                    SUM(si.quantity) AS qty_sold,
                    SUM(si.line_total) AS revenue
             FROM sale_items si
             JOIN sales s ON s.id = si.sale_id
             WHERE DATE(s.created_at) BETWEEN ? AND ?
             GROUP BY si.product_id, si.product_name
             ORDER BY qty_sold DESC, revenue DESC
             LIMIT ?",
            [$from, $to, $limit]
        )->fetchAll();
    }

    /** Revenue summary per day (or grouped by range) */
    public static function revenueByDay(string $from, string $to): array
    {
        return Database::query(
            "SELECT DATE(created_at) AS day,
                    COUNT(*) AS sales_count,
                    COALESCE(SUM(subtotal),0) AS subtotal,
                    COALESCE(SUM(discount),0) AS discount,
                    COALESCE(SUM(tax),0) AS tax,
                    COALESCE(SUM(total),0) AS total
             FROM sales
             WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY DATE(created_at)
             ORDER BY day ASC",
            [$from, $to]
        )->fetchAll();
    }

    /** Range summary: totals for a whole period */
    public static function salesSummary(string $from, string $to): array
    {
        $row = Database::query(
            "SELECT COUNT(*) AS sales_count,
                    COALESCE(SUM(subtotal),0) AS subtotal,
                    COALESCE(SUM(discount),0) AS discount,
                    COALESCE(SUM(tax),0) AS tax,
                    COALESCE(SUM(total),0) AS total
             FROM sales
             WHERE DATE(created_at) BETWEEN ? AND ?",
            [$from, $to]
        )->fetch() ?: [];

        $row['avg_sale'] = ((int)($row['sales_count'] ?? 0)) > 0
            ? (float)$row['total'] / (int)$row['sales_count']
            : 0.0;

        return $row;
    }

    /** Total stock valuation (all batches with stock, grouped by product) */
    public static function stockValuation(): array
    {
        return Database::query(
            "SELECT p.id, p.name, p.sku, p.unit, c.name AS category_name,
                    SUM(b.quantity_remaining) AS total_qty,
                    SUM(b.quantity_remaining * b.cost_price) AS cost_value,
                    SUM(b.quantity_remaining * b.selling_price) AS retail_value
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             JOIN batches b ON b.product_id = p.id AND b.quantity_remaining > 0
             GROUP BY p.id
             ORDER BY cost_value DESC"
        )->fetchAll();
    }
}