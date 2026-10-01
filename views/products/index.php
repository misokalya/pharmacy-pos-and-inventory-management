<?php
$actions = '';
$actions .= '<a href="' . base_url('categories') . '" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700">
  <i class="fa-solid fa-tags"></i><span>Categories</span></a>';
$actions .= '<button onclick="openProductModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
  <i class="fa-solid fa-plus"></i><span>New Product</span></button>';
require VIEW_PATH . '/partials/page-header.php';
?>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <!-- Filters -->
  <form method="get" class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[220px]">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input name="q" value="<?= e($search) ?>" placeholder="Search name, SKU, barcode…"
             class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <select name="category" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      <option value="">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int)$c['id'] ?>" <?= $categoryId === (int)$c['id'] ? 'selected' : '' ?>>
          <?= e($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-white">
      <i class="fa-solid fa-filter"></i> Filter
    </button>
    <?php if ($search || $categoryId): ?>
      <a href="<?= base_url('products') ?>" class="text-sm text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">Clear</a>
    <?php endif; ?>
  </form>

  <!-- Table -->
  <?php if (empty($products)): ?>
    <?php
      $icon = 'fa-pills';
      $title = 'No products yet';
      $message = 'Add your first product to start building your catalog.';
      $actionHtml = '<button onclick="openProductModal()" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-plus"></i> New Product</button>';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3 w-16"></th>
          <th class="text-left font-medium px-4 py-3">Product</th>
          <th class="text-left font-medium px-4 py-3">SKU / Barcode</th>
          <th class="text-left font-medium px-4 py-3">Category</th>
          <th class="text-right font-medium px-4 py-3">Cost</th>
          <th class="text-right font-medium px-4 py-3">Price</th>
          <th class="text-center font-medium px-4 py-3">Status</th>
          <th class="text-right font-medium px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($products as $p): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3">
              <?php if (!empty($p['image_path'])): ?>
                <img src="<?= e(base_url($p['image_path'])) ?>" alt=""
                    class="h-12 w-12 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
              <?php else: ?>
                <div class="h-12 w-12 rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center text-slate-400">
                  <i class="fa-solid fa-pills"></i>
                </div>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3">
              <div class="font-medium"><?= e($p['name']) ?></div>
              <?php if ($p['generic_name']): ?>
                <div class="text-xs text-slate-500"><?= e($p['generic_name']) ?></div>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3">
              <div class="font-mono text-xs"><?= e($p['sku']) ?></div>
              <?php if ($p['barcode']): ?>
                <div class="font-mono text-xs text-slate-500"><?= e($p['barcode']) ?></div>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($p['category_name'] ?? '—') ?></td>
            <td class="px-4 py-3 text-right"><?= e(money($p['cost_price'])) ?></td>
            <td class="px-4 py-3 text-right font-medium"><?= e(money($p['selling_price'])) ?></td>
            <td class="px-4 py-3 text-center">
              <?php if ((int)$p['is_active']): ?>
                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30">
                  Active
                </span>
              <?php else: ?>
                <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium bg-slate-100 text-slate-600 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700">
                  Inactive
                </span>
              <?php endif; ?>
              <?php if ((int)$p['requires_prescription']): ?>
                <div class="text-[10px] text-amber-600 mt-1"><i class="fa-solid fa-file-prescription"></i> Rx</div>
              <?php endif; ?>
            </td>
            <td class="px-4 py-3 text-right">
              <div class="inline-flex items-center gap-1">
                <button type="button"
                        onclick='openProductModal(<?= json_encode($p, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'
                        class="h-8 w-8 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <form method="post" action="<?= base_url('products/' . (int)$p['id'] . '/delete') ?>" onsubmit="return confirmDelete('Delete this product?');">
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

  <!-- Pagination -->
  <?php if ($pagination['pages'] > 1): ?>
    <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-800 text-sm">
      <div class="text-slate-500">
        Showing <?= count($products) ?> of <?= (int)$pagination['total'] ?>
      </div>
      <div class="flex items-center gap-1">
        <?php for ($i = 1; $i <= $pagination['pages']; $i++): 
          $qs = http_build_query(array_filter(['q' => $search, 'category' => $categoryId, 'page' => $i]));
        ?>
          <a href="<?= base_url('products?' . $qs) ?>"
             class="h-8 min-w-8 px-3 grid place-items-center rounded-lg text-sm
                    <?= $i === (int)$pagination['page']
                        ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900'
                        : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Product Modal -->
<?php ob_start(); ?>
<form id="productForm" method="post" action="<?= base_url('products') ?>" enctype="multipart/form-data" class="space-y-4">
  <?= csrf_field() ?>
  <input type="hidden" name="_method" value="create">

  <!-- Image -->
<div class="flex items-start gap-4">
  <div class="shrink-0">
    <img id="productImagePreview"
         src="<?= asset('img/product-default.png') ?>"
         alt="Preview"
         class="h-24 w-24 rounded-xl object-cover border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800"
         onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%2394a3b8%22><rect width=%2224%22 height=%2224%22 fill=%22%23e2e8f0%22/><path d=%22M8 11h8v2H8zm0-4h8v2H8zm0 8h5v2H8z%22/></svg>'">
  </div>
  <div class="flex-1">
    <label class="block text-sm font-medium mb-1.5">Product Image</label>
    <input type="file" name="image" accept="image/png,image/jpeg,image/webp,image/gif"
           onchange="previewProductImage(this)"
           class="block w-full text-sm text-slate-600 dark:text-slate-300
                  file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                  file:text-sm file:font-medium file:bg-slate-100 dark:file:bg-slate-800
                  file:text-slate-700 dark:file:text-slate-200 hover:file:bg-slate-200 dark:hover:file:bg-slate-700">
    <p class="text-xs text-slate-500 mt-1.5">JPG, PNG, WEBP or GIF. Max 2 MB. Leave empty to keep current / default.</p>
    <label id="removeImageWrap" class="hidden items-center gap-2 mt-2 text-xs text-red-600">
      <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500">
      Remove current image
    </label>
  </div>
</div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">SKU <span class="text-red-500">*</span></label>
      <input name="sku" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Barcode</label>
      <input name="barcode" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Name <span class="text-red-500">*</span></label>
      <input name="name" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Generic Name</label>
      <input name="generic_name" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Category</label>
      <select name="category_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <option value="">— None —</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= (int)$c['id'] ?>"><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Unit <span class="text-red-500">*</span></label>
      <select name="unit" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <?php foreach (['pcs','box','bottle','strip','tube','sachet','vial','ml','mg','g'] as $u): ?>
          <option value="<?= $u ?>"><?= $u ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Reorder Level</label>
      <input type="number" name="reorder_level" min="0" value="10" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Cost Price</label>
      <input type="number" step="0.01" min="0" name="cost_price" value="0.00" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Selling Price</label>
      <input type="number" step="0.01" min="0" name="selling_price" value="0.00" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="flex items-center gap-6 pt-2">
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" name="requires_prescription" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
      Requires prescription
    </label>
    <label class="inline-flex items-center gap-2 text-sm">
      <input type="checkbox" name="is_active" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
      Active
    </label>
  </div>

  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="productModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">
      Cancel
    </button>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
      <i class="fa-solid fa-floppy-disk"></i> Save
    </button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'productModal'; $title = 'New Product'; require VIEW_PATH . '/partials/modal.php'; ?>

<script>
const productForm = document.getElementById('productForm');
const productModalTitle = document.querySelector('#productModal h3');

const defaultImage = "<?= asset('img/product-default.png') ?>";
const productImagePreview = document.getElementById('productImagePreview');
const removeImageWrap = document.getElementById('removeImageWrap');

function previewProductImage(input) {
  const file = input.files?.[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => { productImagePreview.src = e.target.result; };
  reader.readAsDataURL(file);
}

function openProductModal(product = null) {
  productForm.reset();
  productForm.action = '<?= base_url('products') ?>';
  productImagePreview.src = defaultImage;
  removeImageWrap.classList.add('hidden');
  removeImageWrap.classList.remove('flex');
  productForm.querySelector('[name="remove_image"]').checked = false;

  if (product) {
    productModalTitle.textContent = 'Edit Product';
    productForm.action = '<?= base_url('products') ?>/' + product.id;
    for (const key of ['sku','barcode','name','generic_name','category_id','unit','reorder_level','cost_price','selling_price']) {
      const el = productForm.querySelector(`[name="${key}"]`);
      if (el) el.value = product[key] ?? '';
    }
    productForm.querySelector('[name="requires_prescription"]').checked = product.requires_prescription == 1;
    productForm.querySelector('[name="is_active"]').checked = product.is_active == 1;

    if (product.image_path) {
      productImagePreview.src = '<?= rtrim(base_url(), '/') ?>/' + product.image_path;
      removeImageWrap.classList.remove('hidden');
      removeImageWrap.classList.add('flex');
    }
  } else {
    productModalTitle.textContent = 'New Product';
    productForm.querySelector('[name="is_active"]').checked = true;
  }
  openModal('productModal');
}
</script>