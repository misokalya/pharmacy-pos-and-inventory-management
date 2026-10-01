<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<form method="post" action="<?= base_url('settings') ?>" class="space-y-6">
  <?= csrf_field() ?>

  <!-- General -->
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
      <i class="fa-solid fa-store text-emerald-500"></i> General
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">App Name</label>
        <input name="app_name" value="<?= e($settings['app_name'] ?? '') ?>" required
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Currency Code</label>
        <input name="currency" value="<?= e($settings['currency'] ?? '') ?>" placeholder="TZS" maxlength="8"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Currency Symbol</label>
        <input name="currency_symbol" value="<?= e($settings['currency_symbol'] ?? '') ?>" placeholder="Tsh" maxlength="4"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Tax Rate (%)</label>
        <input name="tax_rate" type="number" min="0" max="100" step="0.01"
               value="<?= e($settings['tax_rate'] ?? '0') ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
    </div>
  </div>

  <!-- Expiry & Alerts -->
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
      <i class="fa-solid fa-clock text-amber-500"></i> Expiry & Alerts
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Expiry Alert Window (days)</label>
        <input name="expiry_alert_days" type="number" min="1" max="730"
               value="<?= e($settings['expiry_alert_days'] ?? '90') ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <p class="text-xs text-slate-500 mt-1">Batches this close to expiry show as <strong>Near Expiry</strong>.</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Critical Window (days)</label>
        <input name="critical_expiry_days" type="number" min="1" max="365"
               value="<?= e($settings['critical_expiry_days'] ?? '30') ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <p class="text-xs text-slate-500 mt-1">Must be less than the alert window.</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Digest Window (days)</label>
        <input name="expiry_digest_window" type="number" min="7" max="365"
               value="<?= e($settings['expiry_digest_window'] ?? '90') ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <p class="text-xs text-slate-500 mt-1">How far ahead the daily email looks.</p>
      </div>
    </div>
    <div class="mt-4">
      <label class="block text-sm font-medium mb-1.5">Alert Email</label>
      <input name="alert_email" type="email" value="<?= e($settings['alert_email'] ?? '') ?>"
             class="w-full sm:w-80 rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      <p class="text-xs text-slate-500 mt-1">Where the daily expiry digest is sent.</p>
    </div>
  </div>

  <div class="flex justify-end">
    <button class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
      <i class="fa-solid fa-floppy-disk"></i> Save Settings
    </button>
  </div>
</form>