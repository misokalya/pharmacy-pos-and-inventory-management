<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<div class="max-w-3xl">
  <form method="post" action="<?= base_url('inventory/receive') ?>" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 space-y-5">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Product <span class="text-red-500">*</span></label>
        <select name="product_id" required class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <option value="">— Select product —</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= (int)$p['id'] ?>" data-cost="<?= e($p['selling_price']) ?>" <?= (int)old('product_id') === (int)$p['id'] ? 'selected' : '' ?>>
              <?= e($p['name']) ?> (<?= e($p['sku']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Supplier</label>
        <select name="supplier_id" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
          <option value="">— None —</option>
          <?php foreach ($suppliers as $s): ?>
            <option value="<?= (int)$s['id'] ?>" <?= (int)old('supplier_id') === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Batch Number <span class="text-red-500">*</span></label>
        <input name="batch_no" required value="<?= e(old('batch_no')) ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Quantity <span class="text-red-500">*</span></label>
        <input type="number" min="1" name="quantity" required value="<?= e(old('quantity', '1')) ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Cost Price (per unit)</label>
        <input type="number" step="0.01" min="0" name="cost_price" value="<?= e(old('cost_price', '0.00')) ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1.5">Selling Price (per unit)</label>
        <input type="number" step="0.01" min="0" name="selling_price" value="<?= e(old('selling_price', '0.00')) ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1.5">Manufacture Date</label>
        <input type="date" name="manufacture_date" value="<?= e(old('manufacture_date')) ?>"
               class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring