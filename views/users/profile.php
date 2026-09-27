<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Info -->
  <div class="lg:col-span-1 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 text-center">
    <div class="mx-auto h-20 w-20 rounded-full bg-slate-200 dark:bg-slate-700 grid place-items-center text-slate-500 dark:text-slate-300 text-3xl">
      <i class="fa-solid fa-user"></i>
    </div>
    <div class="mt-4 text-lg font-semibold"><?= e($user['name']) ?></div>
    <div class="text-sm text-slate-500"><?= e($user['email']) ?></div>
    <div class="mt-2">
      <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30">
        <?= e($user['role']) ?>
      </span>
    </div>
    <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 text-xs text-slate-500 text-left space-y-1">
      <div><span class="font-medium">Last login:</span> <?= $user['last_login_at'] ? e(date('d M Y H:i', strtotime($user['last_login_at']))) : '—' ?></div>
      <div><span class="font-medium">Member since:</span> <?= e(date('d M Y', strtotime($user['created_at']))) ?></div>
      <div><span class="font-medium">Password changed:</span> <?= $user['last_password_change'] ? e(date('d M Y', strtotime($user['last_password_change']))) : '—' ?></div>
    </div>
  </div>

  <!-- Change password -->
  <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
    <h3 class="font-semibold mb-4 flex items-center gap-2">
      <i class="fa-solid fa-key text-amber-500"></i> Change Password
    </h3>

    <form method="post" action="<?= base_url('profile/password') ?>" class="space-y-4 max-w-md">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-medium mb-1.5">Current Password <span class="text-red-500">*</span></label>
        <input type="password" name="current_password" required autocomplete="current-password"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">New Password <span class="text-red-500">*</span></label>
        <input type="password" name="new_password" minlength="8" required autocomplete="new-password"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <p class="text-xs text-slate-500 mt-1">Minimum 8 characters. Must differ from current password.</p>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
        <input type="password" name="new_password_confirmation" minlength="8" required autocomplete="new-password"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <button class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
        <i class="fa-solid fa-check"></i> Update Password
      </button>
    </form>
  </div>
</div>