<?php
namespace App\Middleware;

class GuestMiddleware
{
    public function handle(): void
    {
        if (auth()) {
            redirect('dashboard');
        }
    }
}