<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <?php if (empty($rows)): ?>
    <?php
      $icon = 'fa-circle-check';
      $title = 'Stock is healthy';
      $message = 'No products are at or below their reorder level.';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Product</th>
          <th class="text-left font-medium px-4 py-3">Category</th>
          <th class="text-right font-medium px-4 py-3">Current Stock</th>
          <th class="text-right font-medium px-4 py-3">Reorder Level</th>
          <th class="text-right font-medium px-4 py-3">Deficit</th>
          <th class="text-right font-medium px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($rows as $r):
          $deficit = max(0, (int)$r['reorder_level'] - (int)$r['current_stock']);
        ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3">
              <div class="font-medium"><?= e($r['name']) ?></div>
              <div class="text-xs text-slate-500 font-mono"><?= e($r['sku']) ?></div>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($r['category_name'] ?: '—') ?></td>
            <td class="px-4 py-3 text-right font-medium <?= (int)$r['current_stock'] === 0 ? 'text-red-600' : '' ?>">
              <?= (int)$r['current_stock'] ?> <?= e($r['unit']) ?>
            </td>
            <td class="px-4 py-3 text-right"><?= (int)$r['reorder_level'] ?></td>
            <td class="px-4 py-3 text-right text-amber-600 font-medium"><?= $deficit ?></td>
            <td class="px-4 py-3 text-right">
              <a href="<?= base_url('inventory/receive') ?>" class="text-xs text-emerald-600 hover:underline">
                <i class="fa-solid fa-boxes-packing"></i> Receive
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>