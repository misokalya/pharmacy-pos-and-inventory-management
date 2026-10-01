<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
    <div class="text-xs text-slate-500">Total Cost Value</div>
    <div class="text-2xl font-semibold tracking-tight mt-1"><?= e(money($costTotal)) ?></div>
  </div>
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
    <div class="text-xs text-slate-500">Total Retail Value</div>
    <div class="text-2xl font-semibold tracking-tight mt-1 text-emerald-600 dark:text-emerald-400"><?= e(money($retailTotal)) ?></div>
  </div>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <?php if (empty($rows)): ?>
    <?php
      $icon = 'fa-coins';
      $title = 'No stock to value';
      $message = 'Receive stock to see valuation figures.';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Product</th>
          <th class="text-left font-medium px-4 py-3">Category</th>
          <th class="text-right font-medium px-4 py-3">Units</th>
          <th class="text-right font-medium px-4 py-3">Cost Value</th>
          <th class="text-right font-medium px-4 py-3">Retail Value</th>
          <th class="text-right font-medium px-4 py-3">Margin</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($rows as $r):
          $margin = (float)$r['retail_value'] - (float)$r['cost_value'];
        ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3">
              <div class="font-medium"><?= e($r['name']) ?></div>
              <div class="text-xs text-slate-500 font-mono"><?= e($r['sku']) ?></div>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($r['category_name'] ?: '—') ?></td>
            <td class="px-4 py-3 text-right"><?= (int)$r['total_qty'] ?> <?= e($r['unit']) ?></td>
            <td class="px-4 py-3 text-right"><?= e(money($r['cost_value'])) ?></td>
            <td class="px-4 py-3 text-right"><?= e(money($r['retail_value'])) ?></td>
            <td class="px-4 py-3 text-right font-medium text-emerald-600 dark:text-emerald-400"><?= e(money($margin)) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>