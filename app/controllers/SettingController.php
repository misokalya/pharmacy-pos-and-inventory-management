<?php
namespace App\Controllers;

use App\Models\Setting;

class SettingController
{
    public function index(): void
    {
        if (!has_role('admin')) {
            flash('error', 'Only admins can access settings.');
            redirect('dashboard');
        }

        view('settings.index', [
            'title'    => 'Settings',
            'settings' => Setting::all(),
        ]);
    }

    public function update(): void
    {
        verify_csrf();

        if (!has_role('admin')) {
            flash('error', 'Permission denied.');
            redirect('dashboard');
        }

        $errors = [];
        $values = [];

        foreach (Setting::editableKeys() as $key) {
            $value = trim((string)($_POST[$key] ?? ''));
            $values[$key] = $value;
        }

        // --- Validation ---
        if ($values['app_name'] === '') {
            $errors[] = 'App name is required.';
        }
        if ($values['currency'] === '') {
            $errors[] = 'Currency code is required.';
        }
        if ($values['alert_email'] !== '' && !filter_var($values['alert_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Alert email is invalid.';
        }

        $taxRate = (float)$values['tax_rate'];
        if ($taxRate < 0 || $taxRate > 100) {
            $errors[] = 'Tax rate must be between 0 and 100.';
        }

        $alertDays = (int)$values['expiry_alert_days'];
        $criticalDays = (int)$values['critical_expiry_days'];
        $digestWindow = (int)$values['expiry_digest_window'];

        if ($alertDays < 1 || $alertDays > 730)       $errors[] = 'Expiry alert days must be 1–730.';
        if ($criticalDays < 1 || $criticalDays > 365) $errors[] = 'Critical days must be 1–365.';
        if ($criticalDays >= $alertDays)              $errors[] = 'Critical days must be less than expiry alert days.';
        if ($digestWindow < 7 || $digestWindow > 365) $errors[] = 'Digest window must be 7–365.';

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('settings');
        }

        Setting::updateMany($values);
        flash('success', 'Settings saved.');
        redirect('settings');
    }
}