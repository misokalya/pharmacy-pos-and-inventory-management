<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;

class ProductController
{
    public function index(): void
    {
        $search     = trim($_GET['q'] ?? '');
        $categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
        $page       = max(1, (int)($_GET['page'] ?? 1));

        $result     = Product::paginate($search, $categoryId, $page, 10);
        $categories = Category::all();

        view('products.index', [
            'title'      => 'Products',
            'products'   => $result['rows'],
            'pagination' => $result,
            'search'     => $search,
            'categoryId' => $categoryId,
            'categories' => $categories,
        ]);
    }

    public function store(): void
    {
        verify_csrf();

        $data = $this->validated();
        if (!empty($data['errors'])) {
            flash('error', implode(' ', $data['errors']));
            $_SESSION['_old'] = $_POST;
            redirect('products');
        }

        // Handle image
        $upload = handle_image_upload($_FILES['image'] ?? null, 'products');
        if ($upload['error']) {
            flash('error', $upload['error']);
            $_SESSION['_old'] = $_POST;
            redirect('products');
        }
        $data['image_path'] = $upload['path'];

        Product::create($data);
        flash('success', 'Product created successfully.');
        redirect('products');
    }

    public function update(int $id): void
    {
        verify_csrf();
        $product = Product::find($id);
        if (!$product) { flash('error', 'Product not found.'); redirect('products'); }

        $data = $this->validated($id);
        if (!empty($data['errors'])) {
            flash('error', implode(' ', $data['errors']));
            redirect('products');
        }

        // New upload replaces old; remove-image flag wipes it
        $removeImage = !empty($_POST['remove_image']);

        $upload = handle_image_upload($_FILES['image'] ?? null, 'products');
        if ($upload['error']) {
            flash('error', $upload['error']);
            redirect('products');
        }

        if ($upload['path']) {
            // Replace: delete old, store new
            if (!empty($product['image_path'])) {
                delete_upload($product['image_path']);
            }
            $data['image_path'] = $upload['path'];
        } elseif ($removeImage) {
            if (!empty($product['image_path'])) {
                delete_upload($product['image_path']);
            }
            $data['image_path'] = null;
        } else {
            // No change
            $data['image_path'] = $product['image_path'];
        }

        Product::update($id, $data);
        flash('success', 'Product updated.');
        redirect('products');
    }

    public function destroy(int $id): void
    {
        verify_csrf();
        if (!has_role('admin', 'pharmacist')) {
            flash('error', 'You do not have permission.');
            redirect('products');
        }
        Product::delete($id);
        flash('success', 'Product deleted.');
        redirect('products');
    }

    private function validated(?int $exceptId = null): array
    {
        $errors = [];
        $d = [
            'sku'                    => trim($_POST['sku'] ?? ''),
            'barcode'                => trim($_POST['barcode'] ?? ''),
            'name'                   => trim($_POST['name'] ?? ''),
            'generic_name'           => trim($_POST['generic_name'] ?? ''),
            'category_id'            => (int)($_POST['category_id'] ?? 0),
            'unit'                   => trim($_POST['unit'] ?? 'pcs'),
            'reorder_level'          => (int)($_POST['reorder_level'] ?? 0),
            'cost_price'             => (float)($_POST['cost_price'] ?? 0),
            'selling_price'          => (float)($_POST['selling_price'] ?? 0),
            'requires_prescription'  => isset($_POST['requires_prescription']) ? 1 : 0,
            'is_active'              => isset($_POST['is_active']) ? 1 : 0,
            'image_path' => null, // filled later by the upload handler
        ];

        if ($d['sku'] === '')  $errors[] = 'SKU is required.';
        if ($d['name'] === '') $errors[] = 'Product name is required.';
        if ($d['unit'] === '') $errors[] = 'Unit is required.';
        if ($d['selling_price'] < 0 || $d['cost_price'] < 0) $errors[] = 'Prices cannot be negative.';

        if ($d['sku'] && Product::skuExists($d['sku'], $exceptId)) {
            $errors[] = 'SKU already in use.';
        }
        if ($d['barcode'] && Product::barcodeExists($d['barcode'], $exceptId)) {
            $errors[] = 'Barcode already in use.';
        }

        $d['errors'] = $errors;
        return $d;
    }
}