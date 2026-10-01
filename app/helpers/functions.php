<?php
use App\Core\Database;

function base_url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function view(string $view, array $data = [], ?string $layout = 'app'): void
{
    extract($data, EXTR_SKIP);
    $viewFile = VIEW_PATH . '/' . str_replace('.', '/', $view) . '.php';
    if (!file_exists($viewFile)) {
        die("View not found: {$view}");
    }

    if ($layout) {
        $layoutFile = VIEW_PATH . '/layouts/' . $layout . '.php';
        ob_start();
        require $viewFile;
        $content = ob_get_clean();
        require $layoutFile;
    } else {
        require $viewFile;
    }
}

function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['_old'][$key] ?? $default;
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $msg = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf']
          ?? $_SERVER['HTTP_X_CSRF_TOKEN']
          ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'CSRF token mismatch.']);
        exit;
    }
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function auth(): ?array
{
    return $_SESSION['user'] ?? null;
}

function auth_id(): ?int
{
    return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
}

function has_role(string ...$roles): bool
{
    $user = auth();
    return $user && in_array($user['role'], $roles, true);
}

function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require APP_PATH . '/config/app.php';
    }
    return $cfg[$key] ?? $default;
}

function money(float|int|string|null $amount): string
{
    static $symbol = null;
    if ($symbol === null) {
        $symbol = \App\Models\Setting::get('currency_symbol', 'Tsh');
    }
    return $symbol . ' ' . number_format((float)$amount, 2);
}