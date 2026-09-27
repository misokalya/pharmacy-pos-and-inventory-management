<?php
$actions = '<a href="' . base_url('reports/expiry/export?window=' . (int)$window) . '" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700"><i class="fa-solid fa-file-csv"></i> Export CSV</a>';
require VIEW_PATH . '/partials/page-header.php';

$stateBadge = function (string $state) {
  $map = [
    'valid'    => ['emerald', 'fa-circle-check',       'Valid'],
    'near'     => ['amber',   'fa-clock',              'Near Expiry'],
    'critical' => ['red',     'fa-triangle-exclamation','Critical'],
    'expired'  => ['red',     'fa-ban',                'Expired'],
  ];
  [$c,$i,$l] = $map[$state] ?? ['slate','fa-circle','—'];
  return '<span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium '
    . ['emerald'=>'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
       'amber'  =>'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
       'red'    =>'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
       'slate'  =>'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'][$c]
    . '"><i class="fa-solid '.$i.' text-[10px]"></i>'.$l.'</span>';
};
?>

<!-- Window selector -->
<div class="flex flex-wrap items-center gap-2 mb-4">
  <?php foreach ([30,60,90,180,365] as $w): ?>
    <a href="<?= base_url('reports/expiry?window=' . $w) ?>"
       class="rounded-lg px-3 py-1.5 text-sm font-medium <?= $w === $window ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700' ?>">
      Next <?= $w ?> days
    </a>
  <?php endforeach; ?>
</div>

<!-- Forecast buckets -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <?php
    $buckets = [
      ['Expired',   $forecast['expired_batches'] ?? 0, $forecast['expired_units'] ?? 0, 'red'],
      ['0–30 days', $forecast['d30_batches'] ?? 0,     $forecast['d30_units'] ?? 0,     'red'],
      ['31–60 days',$forecast['d60_batches'] ?? 0,     $forecast['d60_units'] ?? 0,     'amber'],
      ['61–90 days',$forecast['d90_batches'] ?? 0,     $forecast['d90_units'] ?? 0,     'emerald'],
    ];
  ?>
  <?php foreach ($buckets as [$lbl,$batches,$units,$color]): ?>
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
      <div class="text-xs text-slate-500"><?= e($lbl) ?></div>
      <div class="mt-2 text-2xl font-semibold tracking-tight text-<?= $color ?>-600 dark:text-<?= $color ?>-400"><?= (int)$batches ?></div>
      <div class="text-xs text-slate-500 mt-1"><?= (int)$units ?> units</div>
    </div>
  <?php endforeach; ?>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <?php if (empty($batches)): ?>
    <?php
      $icon = 'fa-circle-check';
      $title = 'Nothing expiring';
      $message = 'No batches expire within the selected window.';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Product</th>
          <th class="text-left font-medium px-4 py-3">Batch</th>
          <th class="text-left font-medium px-4 py-3">Supplier</th>
          <th class="text-left font-medium px-4 py-3">Expiry</th>
          <th class="text-right font-medium px-4 py-3">Days Left</th>
          <th class="text-right font-medium px-4 py-3">Remaining</th>
          <th class="text-right font-medium px-4 py-3">Value at Cost</th>
          <th class="text-center font-medium px-4 py-3">State</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($batches as $b): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3">
              <div class="font-medium"><?= e($b['product_name']) ?></div>
              <div class="text-xs text-slate-500 font-mono"><?= e($b['sku']) ?></div>
            </td>
            <td class="px-4 py-3 font-mono text-xs"><?= e($b['batch_no']) ?></td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($b['supplier_name'] ?: '—') ?></td>
            <td class="px-4 py-3"><?= e(date('d M Y', strtotime($b['expiry_date']))) ?></td>
            <td class="px-4 py-3 text-right <?= (int)$b['days_left'] < 0 ? 'text-red-600 font-medium' : '' ?>">
              <?= (int)$b['days_left'] ?>
            </td>
            <td class="px-4 py-3 text-right"><?= (int)$b['quantity_remaining'] ?> <?= e($b['unit']) ?></td>
            <td class="px-4 py-3 text-right"><?= e(money($b['value_at_cost'])) ?></td>
            <td class="px-4 py-3 text-center"><?= $stateBadge($b['expiry_state']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>