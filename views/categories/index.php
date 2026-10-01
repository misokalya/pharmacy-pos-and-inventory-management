<?php
$actions = '<button onclick="openCategoryModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
  <i class="fa-solid fa-plus"></i><span>New Category</span></button>';
require VIEW_PATH . '/partials/page-header.php';
?>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <?php if (empty($categories)): ?>
    <?php
      $icon = 'fa-tags';
      $title = 'No categories yet';
      $message = 'Create categories to organize your products.';
      $actionHtml = '<button onclick="openCategoryModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-plus"></i> New Category</button>';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Name</th>
          <th class="text-left font-medium px-4 py-3">Description</th>
          <th class="text-right font-medium px-4 py-3">Products</th>
          <th class="text-right font-medium px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($categories as $c): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3 font-medium"><?= e($c['name']) ?></td>
            <td class="px-4 py-3 text-slate-500"><?= e($c['description'] ?: '—') ?></td>
            <td class="px-4 py-3 text-right"><?= (int)$c['product_count'] ?></td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex items-center gap-1">
                <button type="button" onclick='openCategoryModal(<?= json_encode($c, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'
                        class="h-8 w-8 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= base_url('categories/' . (int)$c['id'] . '/delete') ?>" onsubmit="return confirmDelete('Delete this category?');">
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

<!-- Category Modal -->
<?php ob_start(); ?>
<form id="categoryForm" method="post" action="<?= base_url('categories') ?>" class="space-y-4">
  <?= csrf_field() ?>
  <div>
    <label class="block text-sm font-medium mb-1.5">Name <span class="text-red-500">*</span></label>
    <input name="name" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div>
    <label class="block text-sm font-medium mb-1.5">Description</label>
    <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
  </div>
  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="categoryModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">Cancel</button>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
      <i class="fa-solid fa-floppy-disk"></i> Save
    </button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'categoryModal'; $title = 'New Category'; require VIEW_PATH . '/partials/modal.php'; ?>

<script>
const categoryForm = document.getElementById('categoryForm');
const categoryModalTitle = document.querySelector('#categoryModal h3');

function openCategoryModal(category = null) {
  categoryForm.reset();
  categoryForm.action = '<?= base_url('categories') ?>';
  if (category) {
    categoryModalTitle.textContent = 'Edit Category';
    categoryForm.action = '<?= base_url('categories') ?>/' + category.id;
    categoryForm.querySelector('[name="name"]').value = category.name ?? '';
    categoryForm.querySelector('[name="description"]').value = category.description ?? '';
  } else {
    categoryModalTitle.textContent = 'New Category';
  }
  openModal('categoryModal');
}
</script>