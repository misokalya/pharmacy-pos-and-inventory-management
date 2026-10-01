<?php
namespace App\Controllers;

use App\Models\User;

class UserController
{
    public function index(): void
    {
        if (!has_role('admin')) {
            flash('error', 'Only admins can manage users.');
            redirect('dashboard');
        }

        view('users.index', [
            'title' => 'User management',
            'users' => User::all(),
        ]);
    }

    public function store(): void
    {
        verify_csrf();
        if (!has_role('admin')) { flash('error', 'Permission denied.'); redirect('users'); }

        $d = [
            'name'     => trim($_POST['name'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role'     => $_POST['role'] ?? 'cashier',
            'is_active'=> isset($_POST['is_active']) ? 1 : 0,
            'created_by'=> auth_id(),
        ];

        $errors = $this->validate($d, isCreate: true);
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('users');
        }

        User::create($d);
        flash('success', 'User created successfully.');
        redirect('users');
    }

    public function update(int $id): void
    {
        verify_csrf();
        if (!has_role('admin')) { flash('error', 'Permission denied.'); redirect('users'); }

        $user = User::findById($id);
        if (!$user) { flash('error', 'User not found.'); redirect('users'); }

        $d = [
            'name'      => trim($_POST['name'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'role'      => $_POST['role'] ?? $user['role'],
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        $errors = $this->validate($d, isCreate: false, userId: $id);

        // Guard: don't let the last active admin lose admin or become inactive
        $wasAdmin    = $user['role'] === 'admin' && (int)$user['is_active'] === 1;
        $willBeAdmin = $d['role'] === 'admin' && $d['is_active'] === 1;
        if ($wasAdmin && !$willBeAdmin && User::activeAdminCount() <= 1) {
            $errors[] = 'Cannot remove the last active admin.';
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('users');
        }

        User::update($id, $d);
        flash('success', 'User updated.');
        redirect('users');
    }

    public function updatePassword(int $id): void
    {
        verify_csrf();
        if (!has_role('admin')) { flash('error', 'Permission denied.'); redirect('users'); }

        $pwd     = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';

        $errors = [];
        if (strlen($pwd) < 8)         $errors[] = 'Password must be at least 8 characters.';
        if ($pwd !== $confirm)        $errors[] = 'Passwords do not match.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('users');
        }

        User::updatePassword($id, $pwd);
        flash('success', 'Password updated.');
        redirect('users');
    }

    public function destroy(int $id): void
    {
        verify_csrf();
        if (!has_role('admin')) { flash('error', 'Permission denied.'); redirect('users'); }

        $user = User::findById($id);
        if (!$user) { flash('error', 'User not found.'); redirect('users'); }

        if ($id === auth_id()) {
            flash('error', 'You cannot deactivate your own account.');
            redirect('users');
        }

        if ($user['role'] === 'admin' && (int)$user['is_active'] === 1 && User::activeAdminCount() <= 1) {
            flash('error', 'Cannot deactivate the last active admin.');
            redirect('users');
        }

        User::deactivate($id);
        flash('success', 'User deactivated.');
        redirect('users');
    }

    /** ---------- Change own password (any authenticated user) ---------- */
    public function profile(): void
    {
        view('users.profile', [
            'title' => 'My Profile',
            'user'  => User::findById(auth_id()),
        ]);
    }

    public function updateOwnPassword(): void
    {
        verify_csrf();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['new_password_confirmation'] ?? '';

        $user = User::findById(auth_id());
        $errors = [];

        if (!$user || !password_verify($current, $user['password_hash'])) {
            $errors[] = 'Current password is incorrect.';
        }
        if (strlen($new) < 8)     $errors[] = 'New password must be at least 8 characters.';
        if ($new !== $confirm)    $errors[] = 'New passwords do not match.';
        if ($new === $current)    $errors[] = 'New password must be different from the current one.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('profile');
        }

        User::updatePassword((int)$user['id'], $new);
        flash('success', 'Password changed successfully.');
        redirect('profile');
    }

    /** ---------- Validation helper ---------- */
    private function validate(array $d, bool $isCreate, ?int $userId = null): array
    {
        $errors = [];

        if ($d['name'] === '')  $errors[] = 'Name is required.';
        if ($d['email'] === '') {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif (User::emailExists($d['email'], $userId)) {
            $errors[] = 'Email is already in use.';
        }

        if (!in_array($d['role'], ['admin', 'pharmacist', 'cashier'], true)) {
            $errors[] = 'Invalid role.';
        }

        if ($isCreate) {
            if (strlen($d['password'] ?? '') < 8) {
                $errors[] = 'Password must be at least 8 characters.';
            }
        }

        return $errors;
    }
}