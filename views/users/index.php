<?php
$actions = '<button onclick="openUserModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
  <i class="fa-solid fa-user-plus"></i><span>New User</span></button>';
require VIEW_PATH . '/partials/page-header.php';

$roleBadge = function (string $role) {
  $map = [
    'admin'      => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
    'pharmacist' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30',
    'cashier'    => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30',
  ];
  return '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-medium capitalize '
    . ($map[$role] ?? '') . '">' . e($role) . '</span>';
};
?>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Name</th>
          <th class="text-left font-medium px-4 py-3">Email</th>
          <th class="text-left font-medium px-4 py-3">Role</th>
          <th class="text-center font-medium px-4 py-3">Status</th>
          <th class="text-left font-medium px-4 py-3">Last Login</th>
          <th class="text-right font-medium px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($users as $u): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3 font-medium">
              <?= e($u['name']) ?>
              <?php if ((int)$u['id'] === auth_id()): ?>
                <span class="text-xs text-slate-400 ml-1">(you)</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($u['email']) ?></td>
            <td class="px-4 py-3"><?= $roleBadge($u['role']) ?></td>
            <td class="px-4 py-3 text-center">
              <?php if ((int)$u['is_active']): ?>
                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">Active</span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-slate-500 text-xs">
              <?= $u['last_login_at'] ? e(date('d M Y H:i', strtotime($u['last_login_at']))) : '—' ?>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex items-center gap-1">
                <button type="button" onclick='openUserModal(<?= json_encode($u, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'
                        class="h-8 w-8 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" title="Edit">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button type="button" onclick="openPasswordModal(<?= (int)$u['id'] ?>, <?= json_encode($u['name']) ?>)"
                        class="h-8 w-8 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" title="Change password">
                  <i class="fa-solid fa-key"></i>
                </button>
                <?php if ((int)$u['is_active'] && (int)$u['id'] !== auth_id()): ?>
                  <form method="post" action="<?= base_url('users/' . (int)$u['id'] . '/delete') ?>"
                        onsubmit="return confirmDelete('Deactivate this user?');">
                    <?= csrf_field() ?>
                    <button class="h-8 w-8 grid place-items-center rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500" title="Deactivate">
                      <i class="fa-solid fa-user-slash"></i>
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ---------- Create/Edit User Modal ---------- -->
<?php ob_start(); ?>
<form id="userForm" method="post" action="<?= base_url('users') ?>" class="space-y-4">
  <?= csrf_field() ?>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Name <span class="text-red-500">*</span></label>
      <input name="name" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Email <span class="text-red-500">*</span></label>
      <input type="email" name="email" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Role <span class="text-red-500">*</span></label>
      <select name="role" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <option value="cashier">Cashier</option>
        <option value="pharmacist">Pharmacist</option>
        <option value="admin">Admin</option>
      </select>
    </div>
    <div class="flex items-end pb-2">
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="is_active" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
        Active
      </label>
    </div>
  </div>

  <div id="passwordGroup">
    <label class="block text-sm font-medium mb-1.5">Password <span class="text-red-500">*</span></label>
    <input type="password" name="password" minlength="8" autocomplete="new-password"
           class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    <p class="text-xs text-slate-500 mt-1">Minimum 8 characters.</p>
  </div>

  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="userModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">Cancel</button>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-floppy-disk"></i> Save</button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'userModal'; $title = 'New User'; require VIEW_PATH . '/partials/modal.php'; ?>

<!-- ---------- Change Password Modal ---------- -->
<?php ob_start(); ?>
<form id="passwordForm" method="post" action="" class="space-y-4">
  <?= csrf_field() ?>
  <p class="text-sm text-slate-500">Set a new password for <strong id="passwordTarget"></strong>.</p>
  <div>
    <label class="block text-sm font-medium mb-1.5">New Password <span class="text-red-500">*</span></label>
    <input type="password" name="password" minlength="8" required autocomplete="new-password"
           class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div>
    <label class="block text-sm font-medium mb-1.5">Confirm Password <span class="text-red-500">*</span></label>
    <input type="password" name="password_confirmation" minlength="8" required autocomplete="new-password"
           class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="passwordModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">Cancel</button>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-key"></i> Update Password</button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'passwordModal'; $title = 'Change Password'; require VIEW_PATH . '/partials/modal.php'; ?>

<script>
const userForm = document.getElementById('userForm');
const userModalTitle = document.querySelector('#userModal h3');
const passwordGroup = document.getElementById('passwordGroup');

function openUserModal(u = null) {
  userForm.reset();
  userForm.action = '<?= base_url('users') ?>';
  passwordGroup.style.display = 'block';
  userForm.querySelector('[name="password"]').required = true;
  userForm.querySelector('[name="is_active"]').checked = true;

  if (u) {
    userModalTitle.textContent = 'Edit User';
    userForm.action = '<?= base_url('users') ?>/' + u.id;
    userForm.querySelector('[name="name"]').value  = u.name ?? '';
    userForm.querySelector('[name="email"]').value = u.email ?? '';
    userForm.querySelector('[name="role"]').value  = u.role ?? 'cashier';
    userForm.querySelector('[name="is_active"]').checked = u.is_active == 1;
    passwordGroup.style.display = 'none';
    userForm.querySelector('[name="password"]').required = false;
    userForm.querySelector('[name="password"]').value = '';
  } else {
    userModalTitle.textContent = 'New User';
  }
  openModal('userModal');
}

function openPasswordModal(id, name) {
  const form = document.getElementById('passwordForm');
  form.action = '<?= base_url('users') ?>/' + id + '/password';
  form.reset();
  document.getElementById('passwordTarget').textContent = name;
  openModal('passwordModal');
}
</script>