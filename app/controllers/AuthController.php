<?php
namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function showLogin(): void
    {
        view('auth.login', ['title' => 'Sign In'], 'auth');
    }

    public function login(): void
    {
        verify_csrf();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['_old'] = ['email' => $email];

        if ($email === '' || $password === '') {
            flash('error', 'Email and password are required.');
            redirect('login');
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Rate limit: max 5 attempts in 15 minutes
        if (User::recentAttempts($email, 15) >= 5) {
            flash('error', 'Too many attempts. Please try again in 15 minutes.');
            redirect('login');
        }

        $user = User::findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            User::logAttempt($email, $ip);
            flash('error', 'Invalid credentials.');
            redirect('login');
        }

        if (!(int)$user['is_active']) {
            flash('error', 'Your account has been deactivated.');
            redirect('login');
        }

        // Success
        User::clearAttempts($email);
        User::updateLastLogin((int)$user['id']);

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ];
        unset($_SESSION['_old']);

        $intended = $_SESSION['_intended'] ?? null;
        unset($_SESSION['_intended']);

        if ($intended) {
            header('Location: ' . $intended);
            exit;
        }

        redirect('dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        redirect('login');
    }
}