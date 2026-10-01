<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <?php
    $cards = [
      ['fa-calendar-xmark', 'Expiry Report',     'Batches expiring in 30/60/90 days',   'reports/expiry',      'red'],
      ['fa-cart-arrow-down','Low Stock',         'Products at or below reorder level',  'reports/low-stock',   'amber'],
      ['fa-chart-line',     'Sales Report',      'Revenue, top products, daily trend',  'reports/sales',       'emerald'],
      ['fa-coins',          'Stock Valuation',   'Cost & retail value of current stock','reports/valuation',   'indigo'],
    ];
  ?>
  <?php foreach ($cards as [$icon,$title,$desc,$url,$color]): ?>
    <a href="<?= base_url($url) ?>" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 hover:border-<?= $color ?>-400 hover:shadow-sm transition group">
      <div class="h-11 w-11 rounded-xl bg-<?= $color ?>-50 dark:bg-<?= $color ?>-500/10 text-<?= $color ?>-600 dark:text-<?= $color ?>-400 grid place-items-center">
        <i class="fa-solid <?= $icon ?>"></i>
      </div>
      <div class="mt-4 font-semibold"><?= e($title) ?></div>
      <div class="text-sm text-slate-500 mt-1"><?= e($desc) ?></div>
      <div class="mt-3 text-xs text-<?= $color ?>-600 dark:text-<?= $color ?>-400 font-medium opacity-0 group-hover:opacity-100 transition">
        Open <i class="fa-solid fa-arrow-right ml-1"></i>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
  <form method="get" class="flex flex-wrap items-end gap-3">
    <div>
      <label class="block text-xs text-slate-500 mb-1">From</label>
      <input type="date" name="from" value="<?= e($from) ?>" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1">To</label>
      <input type="date" name="to" value="<?= e($to) ?>" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm">
    </div>
    <button class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900">
      <i class="fa-solid fa-filter"></i> Preview Range
    </button>
  </form>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-5">
    <?php
      $stats = [
        ['Sales',    (int)($summary['sales_count'] ?? 0)],
        ['Revenue',  money($summary['total'] ?? 0)],
        ['Discount', money($summary['discount'] ?? 0)],
        ['Avg Sale', money($summary['avg_sale'] ?? 0)],
      ];
    ?>
    <?php foreach ($stats as [$lbl, $val]): ?>
      <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3">
        <div class="text-xs text-slate-500"><?= e($lbl) ?></div>
        <div class="text-lg font-semibold mt-0.5"><?= e((string)$val) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>