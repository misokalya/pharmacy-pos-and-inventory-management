<?php
declare(strict_types=1);

require __DIR__ . '/../app/core/bootstrap.php';
require __DIR__ . '/../app/helpers/functions.php';
require __DIR__ . '/../app/helpers/upload.php';

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\ProductController;
use App\Controllers\CategoryController;
use App\Controllers\SupplierController;
use App\Controllers\InventoryController;
use App\Controllers\SaleController;
use App\Controllers\ReportController;
use App\Controllers\SettingController;
use App\Controllers\UserController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;

$router = new Router();

// Guest
$router->get('login',  [AuthController::class, 'showLogin'], [GuestMiddleware::class]);
$router->post('login', [AuthController::class, 'login'],     [GuestMiddleware::class]);

// Auth
$router->post('logout',   [AuthController::class, 'logout'],     [AuthMiddleware::class]);
$router->get('dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class]);
$router->get('',          [DashboardController::class, 'index'], [AuthMiddleware::class]);

// Products
$router->get('products',              [ProductController::class, 'index'],   [AuthMiddleware::class]);
$router->post('products',             [ProductController::class, 'store'],   [AuthMiddleware::class]);
$router->post('products/{id}',        [ProductController::class, 'update'],  [AuthMiddleware::class]);
$router->post('products/{id}/delete', [ProductController::class, 'destroy'], [AuthMiddleware::class]);

// Categories
$router->get('categories',              [CategoryController::class, 'index'],   [AuthMiddleware::class]);
$router->post('categories',             [CategoryController::class, 'store'],   [AuthMiddleware::class]);
$router->post('categories/{id}',        [CategoryController::class, 'update'],  [AuthMiddleware::class]);
$router->post('categories/{id}/delete', [CategoryController::class, 'destroy'], [AuthMiddleware::class]);

// Suppliers
$router->get('suppliers',              [SupplierController::class, 'index'],   [AuthMiddleware::class]);
$router->post('suppliers',             [SupplierController::class, 'store'],   [AuthMiddleware::class]);
$router->post('suppliers/{id}',        [SupplierController::class, 'update'],  [AuthMiddleware::class]);
$router->post('suppliers/{id}/delete', [SupplierController::class, 'destroy'], [AuthMiddleware::class]);

// Inventory
$router->get('inventory',              [InventoryController::class, 'index'],        [AuthMiddleware::class]);
$router->get('inventory/receive',      [InventoryController::class, 'receive'],      [AuthMiddleware::class]);
$router->post('inventory/receive',     [InventoryController::class, 'storeReceive'], [AuthMiddleware::class]);
$router->post('inventory/{id}/adjust', [InventoryController::class, 'adjust'],       [AuthMiddleware::class]);

// POS / Sales — literal paths first
$router->get('sales/pos',      [SaleController::class, 'pos'],            [AuthMiddleware::class]);
$router->get('sales/search',   [SaleController::class, 'searchProducts'], [AuthMiddleware::class]);
$router->get('sales/lookup',   [SaleController::class, 'lookupBarcode'],  [AuthMiddleware::class]);
$router->post('sales/checkout',[SaleController::class, 'checkout'],       [AuthMiddleware::class]);
$router->get('sales',              [SaleController::class, 'index'],   [AuthMiddleware::class]);
$router->get('sales/{id}/receipt', [SaleController::class, 'receipt'], [AuthMiddleware::class]);

// Reports — literal paths first
$router->get('reports',               [ReportController::class, 'index'],        [AuthMiddleware::class]);
$router->get('reports/expiry',        [ReportController::class, 'expiry'],       [AuthMiddleware::class]);
$router->get('reports/expiry/export', [ReportController::class, 'exportExpiry'], [AuthMiddleware::class]);
$router->get('reports/low-stock',     [ReportController::class, 'lowStock'],     [AuthMiddleware::class]);
$router->get('reports/sales',         [ReportController::class, 'sales'],        [AuthMiddleware::class]);
$router->get('reports/valuation',     [ReportController::class, 'valuation'],    [AuthMiddleware::class]);

// Settings
$router->get('settings',  [SettingController::class, 'index'],  [AuthMiddleware::class]);
$router->post('settings', [SettingController::class, 'update'], [AuthMiddleware::class]);

// Users
$router->get('users',                [UserController::class, 'index'],           [AuthMiddleware::class]);
$router->post('users',               [UserController::class, 'store'],           [AuthMiddleware::class]);
$router->post('users/{id}',          [UserController::class, 'update'],          [AuthMiddleware::class]);
$router->post('users/{id}/password', [UserController::class, 'updatePassword'],  [AuthMiddleware::class]);
$router->post('users/{id}/delete',   [UserController::class, 'destroy'],         [AuthMiddleware::class]);

// Profile
$router->get('profile',           [UserController::class, 'profile'],           [AuthMiddleware::class]);
$router->post('profile/password', [UserController::class, 'updateOwnPassword'], [AuthMiddleware::class]);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);