<?php
/**
 * Daily expiry digest — run via cron:
 *   Linux/macOS:  0 7 * * *  /usr/bin/php /path/to/pharmacy/scripts/send_expiry_digest.php
 *   Windows:      Task Scheduler -> C:\xampp\php\php.exe C:\xampp\htdocs\pharmacy\scripts\send_expiry_digest.php
 *
 * Reads config from the settings table (alert_email, expiry_digest_window, app_name).
 */
declare(strict_types=1);

// CLI only
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require __DIR__ . '/../app/core/bootstrap.php';
require __DIR__ . '/../app/helpers/functions.php';

use App\Models\Report;
use App\Models\Setting;

// ---------- Load settings ----------
$to        = Setting::get('alert_email', '');
$window    = Setting::getInt('expiry_digest_window', 90);
$appName   = Setting::get('app_name', 'Pharmacy');

if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "[".date('Y-m-d H:i:s')."] No valid alert_email configured. Aborting.\n");
    exit(1);
}

// ---------- Fetch batches in window ----------
$rows = Report::expiryList($window);

if (empty($rows)) {
    echo "[".date('Y-m-d H:i:s')."] No batches expiring within {$window} days. Nothing to send.\n";
    exit(0);
}

// ---------- Count categories ----------
$expiredCount  = 0;
$criticalCount = 0;
foreach ($rows as $b) {
    $days = (int)$b['days_left'];
    if ($days < 0)             $expiredCount++;
    elseif ($days <= 30)       $criticalCount++;
}

// ---------- Build email body ----------
$lines   = [];
$lines[] = "{$appName} — Expiry Digest (" . date('Y-m-d') . ")";
$lines[] = str_repeat('=', 66);
$lines[] = sprintf("Batches expiring within %d days: %d", $window, count($rows));
if ($expiredCount)  $lines[] = "  • Already expired: {$expiredCount}";
if ($criticalCount) $lines[] = "  • Critical (<=30 days): {$criticalCount}";
$lines[] = '';

foreach ($rows as $b) {
    $days = (int)$b['days_left'];
    if ($days < 0)         $tag = '[EXPIRED] ';
    elseif ($days <= 30)   $tag = '[CRITICAL]';
    elseif ($days <= 90)   $tag = '[NEAR]    ';
    else                   $tag = '[OK]      ';

    $lines[] = sprintf(
        "%s  %s | Batch %s | Qty %d | Exp %s (%s) | Supplier: %s",
        $tag,
        $b['product_name'],
        $b['batch_no'],
        (int)$b['quantity_remaining'],
        $b['expiry_date'],
        $days < 0 ? abs($days) . ' days ago' : $days . ' days left',
        $b['supplier_name'] ?? '—'
    );
}

$lines[] = '';
$lines[] = str_repeat('-', 66);
$lines[] = 'Open the app for full details:';
$lines[] = '  Reports -> Expiry Report';

$body    = implode("\n", $lines);
$subject = sprintf('[%s] Expiry Digest — %d batch(es) expiring', $appName, count($rows));

// ---------- Headers ----------
$fromAddress = 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? gethostname() ?: 'localhost');
$headers = implode("\r\n", [
    'From: ' . $appName . ' <' . $fromAddress . '>',
    'Reply-To: ' . $to,
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
]);

// ---------- Send ----------
$sent = @mail($to, $subject, $body, $headers);

// ---------- Log the result ----------
$logLine = sprintf(
    "[%s] digest to=%s window=%d total=%d expired=%d critical=%d result=%s\n",
    date('Y-m-d H:i:s'),
    $to,
    $window,
    count($rows),
    $expiredCount,
    $criticalCount,
    $sent ? 'sent' : 'failed'
);
$logDir = APP_PATH . '/storage/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0775, true);
@file_put_contents($logDir . '/digest.log', $logLine, FILE_APPEND);

if ($sent) {
    echo rtrim($logLine);
    exit(0);
}

fwrite(STDERR, "Failed to send email to {$to}. See storage/logs/digest.log for details.\n");
exit(1);