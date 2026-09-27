<?php
namespace App\Controllers;

use App\Models\Supplier;

class SupplierController
{
    public function index(): void
    {
        view('suppliers.index', [
            'title'     => 'Suppliers',
            'suppliers' => Supplier::all(),
        ]);
    }

    public function store(): void
    {
        verify_csrf();
        $data = $this->validated();
        if (!empty($data['errors'])) {
            flash('error', implode(' ', $data['errors']));
            redirect('suppliers');
        }
        Supplier::create($data);
        flash('success', 'Supplier created.');
        redirect('suppliers');
    }

    public function update(int $id): void
    {
        verify_csrf();
        if (!Supplier::find($id)) {
            flash('error', 'Supplier not found.');
            redirect('suppliers');
        }
        $data = $this->validated();
        if (!empty($data['errors'])) {
            flash('error', implode(' ', $data['errors']));
            redirect('suppliers');
        }
        Supplier::update($id, $data);
        flash('success', 'Supplier updated.');
        redirect('suppliers');
    }

    public function destroy(int $id): void
    {
        verify_csrf();
        if (!has_role('admin')) {
            flash('error', 'Only admins can delete suppliers.');
            redirect('suppliers');
        }
        Supplier::delete($id);
        flash('success', 'Supplier deleted.');
        redirect('suppliers');
    }

    private function validated(): array
    {
        $errors = [];
        $d = [
            'name'           => trim($_POST['name'] ?? ''),
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
        ];
        if ($d['name'] === '') $errors[] = 'Supplier name is required.';
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }
        $d['errors'] = $errors;
        return $d;
    }
}