<?php
$actions = '<button onclick="openSupplierModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
  <i class="fa-solid fa-plus"></i><span>New Supplier</span></button>';
require VIEW_PATH . '/partials/page-header.php';
?>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <?php if (empty($suppliers)): ?>
    <?php
      $icon = 'fa-truck-field';
      $title = 'No suppliers yet';
      $message = 'Add suppliers to link received batches to their source.';
      $actionHtml = '<button onclick="openSupplierModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-plus"></i> New Supplier</button>';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Name</th>
          <th class="text-left font-medium px-4 py-3">Contact</th>
          <th class="text-left font-medium px-4 py-3">Phone / Email</th>
          <th class="text-right font-medium px-4 py-3">Batches</th>
          <th class="text-right font-medium px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($suppliers as $s): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3 font-medium"><?= e($s['name']) ?></td>
            <td class="px-4 py-3 text-slate-500"><?= e($s['contact_person'] ?: '—') ?></td>
            <td class="px-4 py-3">
              <?php if ($s['phone']): ?><div class="text-xs"><i class="fa-solid fa-phone mr-1 text-slate-400"></i><?= e($s['phone']) ?></div><?php endif; ?>
              <?php if ($s['email']): ?><div class="text-xs"><i class="fa-solid fa-envelope mr-1 text-slate-400"></i><?= e($s['email']) ?></div><?php endif; ?>
            </td>
            <td class="px-4 py-3 text-right"><?= (int)$s['batch_count'] ?></td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex items-center gap-1">
                <button type="button" onclick='openSupplierModal(<?= json_encode($s, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'
                        class="h-8 w-8 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= base_url('suppliers/' . (int)$s['id'] . '/delete') ?>" onsubmit="return confirmDelete('Delete supplier?');">
                  <?= csrf_field() ?>
                  <button class="h-8 w-8 grid place-items-center rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php ob_start(); ?>
<form id="supplierForm" method="post" action="<?= base_url('suppliers') ?>" class="space-y-4">
  <?= csrf_field() ?>
  <div>
    <label class="block text-sm font-medium mb-1.5">Name <span class="text-red-500">*</span></label>
    <input name="name" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Contact Person</label>
      <input name="contact_person" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Phone</label>
      <input name="phone" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>
  <div>
    <label class="block text-sm font-medium mb-1.5">Email</label>
    <input type="email" name="email" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div>
    <label class="block text-sm font-medium mb-1.5">Address</label>
    <textarea name="address" rows="2" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
  </div>
  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="supplierModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">Cancel</button>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-floppy-disk"></i> Save</button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'supplierModal'; $title = 'New Supplier'; require VIEW_PATH . '/partials/modal.php'; ?>

<script>
const supplierForm = document.getElementById('supplierForm');
const supplierModalTitle = document.querySelector('#supplierModal h3');
function openSupplierModal(s = null) {
  supplierForm.reset();
  supplierForm.action = '<?= base_url('suppliers') ?>';
  if (s) {
    supplierModalTitle.textContent = 'Edit Supplier';
    supplierForm.action = '<?= base_url('suppliers') ?>/' + s.id;
    ['name','contact_person','phone','email','address'].forEach(k => {
      const el = supplierForm.querySelector(`[name="${k}"]`);
      if (el) el.value = s[k] ?? '';
    });
  } else supplierModalTitle.textContent = 'New Supplier';
  openModal('supplierModal');
}
</script>