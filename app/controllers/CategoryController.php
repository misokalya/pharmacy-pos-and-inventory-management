<?php
namespace App\Controllers;

use App\Models\Category;

class CategoryController
{
    public function index(): void
    {
        view('categories.index', [
            'title'      => 'Categories',
            'categories' => Category::all(),
        ]);
    }

    public function store(): void
    {
        verify_csrf();
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            flash('error', 'Category name is required.');
            redirect('categories');
        }
        if (Category::nameExists($name)) {
            flash('error', 'That category already exists.');
            redirect('categories');
        }

        Category::create(['name' => $name, 'description' => $description ?: null]);
        flash('success', 'Category created.');
        redirect('categories');
    }

    public function update(int $id): void
    {
        verify_csrf();
        if (!Category::find($id)) {
            flash('error', 'Category not found.');
            redirect('categories');
        }
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '') {
            flash('error', 'Category name is required.');
            redirect('categories');
        }
        if (Category::nameExists($name, $id)) {
            flash('error', 'That category already exists.');
            redirect('categories');
        }

        Category::update($id, ['name' => $name, 'description' => $description ?: null]);
        flash('success', 'Category updated.');
        redirect('categories');
    }

    public function destroy(int $id): void
    {
        verify_csrf();
        if (!has_role('admin')) {
            flash('error', 'Only admins can delete categories.');
            redirect('categories');
        }
        Category::delete($id);
        flash('success', 'Category deleted.');
        redirect('categories');
    }
}