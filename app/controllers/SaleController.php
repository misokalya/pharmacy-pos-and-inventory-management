<?php
namespace App\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Batch;
use App\Models\Setting;

class SaleController
{
    /** POS screen */
    public function pos(): void
    {
        view('sales.pos', [
            'title'    => 'Point of Sale',
            'taxRate'  => Setting::getFloat('tax_rate', 0.0),
            'currency' => Setting::get('currency_symbol', '$'),
        ], 'pos');
    }

    /** AJAX: search products (returns stock + nearest expiry info) */
    public function searchProducts(): void
    {
        $q = trim($_GET['q'] ?? '');
        if ($q === '') { json_response(['results' => []]); }

        $rows = \App\Core\Database::query(
            "SELECT p.id, p.name, p.sku, p.barcode, p.unit, p.selling_price, p.requires_prescription,
                    COALESCE((SELECT SUM(quantity_remaining) FROM batches b
                              WHERE b.product_id = p.id AND b.quantity_remaining > 0
                                AND b.expiry_date >= CURDATE()),0) AS stock,
                    (SELECT MIN(expiry_date) FROM batches b
                     WHERE b.product_id = p.id AND b.quantity_remaining > 0
                       AND b.expiry_date >= CURDATE()) AS nearest_expiry
             FROM products p
             WHERE p.is_active = 1
               AND (p.name LIKE ? OR p.generic_name LIKE ? OR p.sku LIKE ? OR p.barcode = ?)
             ORDER BY p.name ASC
             LIMIT 20",
            ["%$q%", "%$q%", "%$q%", $q]
        )->fetchAll();

        // Enrich
        foreach ($rows as &$r) {
            $r['selling_price']  = (float)$r['selling_price'];
            $r['stock']          = (int)$r['stock'];
            $r['expiry_state']   = $r['nearest_expiry'] ? Batch::expiryState($r['nearest_expiry']) : null;
            $r['image_url']      = null;
        }
        json_response(['results' => $rows]);
    }

    /** AJAX: by barcode (for scanners) */
    public function lookupBarcode(): void
    {
        $code = trim($_GET['code'] ?? '');
        if ($code === '') json_response(['result' => null]);

        $row = \App\Core\Database::query(
            "SELECT p.id, p.name, p.sku, p.barcode, p.unit, p.selling_price, p.requires_prescription,
                    COALESCE((SELECT SUM(quantity_remaining) FROM batches b
                              WHERE b.product_id = p.id AND b.quantity_remaining > 0
                                AND b.expiry_date >= CURDATE()),0) AS stock
             FROM products p
             WHERE p.is_active = 1 AND (p.barcode = ? OR p.sku = ?)
             LIMIT 1",
            [$code, $code]
        )->fetch();

        if ($row) {
            $row['selling_price'] = (float)$row['selling_price'];
            $row['stock']         = (int)$row['stock'];
        }
        json_response(['result' => $row ?: null]);
    }

    /** POST: checkout */
    public function checkout(): void
    {
        verify_csrf();
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        try {
            $result = Sale::checkout(
                [
                    'customer_name'  => trim($payload['customer_name'] ?? ''),
                    'customer_phone' => trim($payload['customer_phone'] ?? ''),
                    'discount'       => (float)($payload['discount'] ?? 0),
                    'amount_paid'    => (float)($payload['amount_paid'] ?? 0),
                    'payment_method' => $payload['payment_method'] ?? 'cash',
                    'notes'          => trim($payload['notes'] ?? ''),
                ],
                $payload['cart'] ?? [],
                auth_id()
            );

            json_response([
                'ok'         => true,
                'sale_id'    => $result['sale_id'],
                'invoice_no' => $result['invoice_no'],
                'receipt_url'=> base_url('sales/' . $result['sale_id'] . '/receipt'),
            ]);
        } catch (\Throwable $e) {
            json_response(['ok' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /** Sales history */
    public function index(): void
    {
        $filters = [
            'q'       => trim($_GET['q'] ?? ''),
            'from'    => $_GET['from'] ?? '',
            'to'      => $_GET['to'] ?? '',
            'user_id' => isset($_GET['user']) ? (int)$_GET['user'] : 0,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Sale::paginate($filters, $page, 15);

        view('sales.index', [
            'title'      => 'Sales History',
            'sales'      => $result['rows'],
            'pagination' => $result,
            'filters'    => $filters,
            'today'      => Sale::todaySummary(),
        ]);
    }

    /** View receipt */
    public function receipt(int $id): void
    {
        $sale = Sale::find($id);
        if (!$sale) { flash('error', 'Sale not found.'); redirect('sales'); }

        view('sales.receipt', [
            'title' => 'Receipt ' . $sale['invoice_no'],
            'sale'  => $sale,
        ], 'print');
    }
}