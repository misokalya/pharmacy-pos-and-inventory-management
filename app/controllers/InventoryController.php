<?php
namespace App\Controllers;

use App\Models\Batch;
use App\Models\Product;
use App\Models\Supplier;

class InventoryController
{
    public function index(): void
    {
        $filters = [
            'q'          => trim($_GET['q'] ?? ''),
            'state'      => $_GET['state'] ?? '',
            'product_id' => isset($_GET['product']) ? (int)$_GET['product'] : 0,
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Batch::paginate($filters, $page, 15);

        view('inventory.index', [
            'title'     => 'Inventory',
            'batches'   => $result['rows'],
            'pagination'=> $result,
            'filters'   => $filters,
            'summary'   => Batch::summaryCounts(),
        ]);
    }

    public function receive(): void
    {
        $products  = Product::allActive();
        $suppliers = Supplier::all();

        view('inventory.receive', [
            'title'     => 'Receive Stock',
            'products'  => $products,
            'suppliers' => $suppliers,
        ]);
    }

    public function storeReceive(): void
    {
        verify_csrf();

        $data = [
            'product_id'       => (int)($_POST['product_id'] ?? 0),
            'supplier_id'      => (int)($_POST['supplier_id'] ?? 0) ?: null,
            'batch_no'         => trim($_POST['batch_no'] ?? ''),
            'quantity'         => (int)($_POST['quantity'] ?? 0),
            'cost_price'       => (float)($_POST['cost_price'] ?? 0),
            'selling_price'    => (float)($_POST['selling_price'] ?? 0),
            'manufacture_date' => $_POST['manufacture_date'] ?: null,
            'expiry_date'      => $_POST['expiry_date'] ?: '',
            'notes'            => trim($_POST['notes'] ?? ''),
            'received_by'      => auth_id(),
        ];

        $errors = [];
        if (!$data['product_id']) $errors[] = 'Please select a product.';
        if ($data['batch_no'] === '') $errors[] = 'Batch number is required.';
        if ($data['quantity'] <= 0) $errors[] = 'Quantity must be at least 1.';
        if ($data['expiry_date'] === '') $errors[] = 'Expiry date is required.';
        elseif (strtotime($data['expiry_date']) === false) $errors[] = 'Invalid expiry date.';
        elseif ($data['expiry_date'] < date('Y-m-d')) $errors[] = 'Expiry date cannot be in the past.';
        if ($data['manufacture_date'] && $data['manufacture_date'] > $data['expiry_date']) {
            $errors[] = 'Manufacture date cannot be after expiry date.';
        }
        if ($data['cost_price'] < 0 || $data['selling_price'] < 0) $errors[] = 'Prices cannot be negative.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            $_SESSION['_old'] = $_POST;
            redirect('inventory/receive');
        }

        try {
            Batch::receive($data);
            flash('success', 'Stock received and batch created.');
            redirect('inventory');
        } catch (\Throwable $e) {
            flash('error', 'Failed to receive stock: ' . $e->getMessage());
            redirect('inventory/receive');
        }
    }

    public function adjust(int $id): void
    {
        verify_csrf();
        if (!has_role('admin', 'pharmacist')) {
            flash('error', 'Permission denied.');
            redirect('inventory');
        }

        $newQty = (int)($_POST['quantity_remaining'] ?? -1);
        $reason = trim($_POST['reason'] ?? '');

        if ($newQty < 0) {
            flash('error', 'Quantity must be zero or greater.');
            redirect('inventory');
        }

        try {
            Batch::adjust($id, $newQty, $reason, auth_id());
            flash('success', 'Batch adjusted.');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
        }
        redirect('inventory');
    }
}