<?php
namespace App\Controllers;

use App\Models\Batch;
use App\Models\Report;
use App\Models\Sale;
use App\Core\Database;

class DashboardController
{
    public function index(): void
    {
        $summary      = Batch::summaryCounts();
        $today        = Sale::todaySummary();
        $productCount = (int)Database::query('SELECT COUNT(*) c FROM products WHERE is_active = 1')->fetch()['c'];

        view('dashboard.index', [
            'title'        => 'Dashboard',
            'user'         => auth(),
            'productCount' => $productCount,
            'summary'      => $summary,
            'today'        => $today,
            'expiringSoon' => Report::expiryList(60),
            'lowStock'     => array_slice(Report::lowStock(), 0, 5),
        ]);
    }
}