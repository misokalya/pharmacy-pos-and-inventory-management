<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!auth()) {
            $_SESSION['_intended'] = $_SERVER['REQUEST_URI'] ?? '/';
            redirect('login');
        }
    }
}