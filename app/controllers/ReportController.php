<?php
namespace App\Controllers;

use App\Models\Report;

class ReportController
{
    public function index(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        view('reports.index', [
            'title'   => 'Reports',
            'from'    => $from,
            'to'      => $to,
            'summary' => Report::salesSummary($from, $to),
        ]);
    }

    public function expiry(): void
    {
        $window = max(7, min(365, (int)($_GET['window'] ?? 90)));

        view('reports.expiry', [
            'title'    => 'Expiry Report',
            'window'   => $window,
            'forecast' => Report::expiryForecast($window),
            'batches'  => Report::expiryList($window),
        ]);
    }

    public function lowStock(): void
    {
        view('reports.low_stock', [
            'title' => 'Low Stock / Reorder List',
            'rows'  => Report::lowStock(),
        ]);
    }

    public function sales(): void
    {
        $from = $_GET['from'] ?? date('Y-m-01');
        $to   = $_GET['to']   ?? date('Y-m-d');

        view('reports.sales', [
            'title'      => 'Sales Report',
            'from'       => $from,
            'to'         => $to,
            'summary'    => Report::salesSummary($from, $to),
            'daily'      => Report::revenueByDay($from, $to),
            'top'        => Report::topProducts($from, $to, 10),
        ]);
    }

    public function valuation(): void
    {
        $rows = Report::stockValuation();
        $costTotal   = array_sum(array_column($rows, 'cost_value'));
        $retailTotal = array_sum(array_column($rows, 'retail_value'));

        view('reports.valuation', [
            'title'       => 'Stock Valuation',
            'rows'        => $rows,
            'costTotal'   => $costTotal,
            'retailTotal' => $retailTotal,
        ]);
    }

    /** CSV export — expiry list */
    public function exportExpiry(): void
    {
        $window = max(7, min(365, (int)($_GET['window'] ?? 90)));
        $rows = Report::expiryList($window);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="expiry-report-' . date('Ymd') . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Product', 'SKU', 'Batch', 'Supplier', 'Expiry', 'Days Left', 'State', 'Qty Remaining', 'Unit Cost', 'Value at Cost']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['product_name'],
                $r['sku'],
                $r['batch_no'],
                $r['supplier_name'] ?? '',
                $r['expiry_date'],
                $r['days_left'],
                $r['expiry_state'],
                $r['quantity_remaining'],
                number_format((float)$r['cost_price'], 2, '.', ''),
                number_format($r['value_at_cost'], 2, '.', ''),
            ]);
        }
        fclose($out);
        exit;
    }
}