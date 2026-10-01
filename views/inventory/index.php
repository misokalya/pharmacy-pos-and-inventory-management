<?php
$actions = '<a href="' . base_url('inventory/receive') . '" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
  <i class="fa-solid fa-boxes-packing"></i><span>Receive Stock</span></a>';
require VIEW_PATH . '/partials/page-header.php';

$stateBadge = function (array $b) {
  $map = [
    'valid'    => ['emerald', 'fa-circle-check',     'Valid'],
    'near'     => ['amber',   'fa-clock',            'Near Expiry'],
    'critical' => ['red',     'fa-triangle-exclamation','Critical'],
    'expired'  => ['red',     'fa-ban',              'Expired'],
  ];
  [$color, $icon, $label] = $map[$b['expiry_state']] ?? ['slate', 'fa-circle', 'Unknown'];
  return '<span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium '
    . ['emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
       'amber'   => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
       'red'     => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
       'slate'   => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700'][$color]
    . '"><i class="fa-solid ' . $icon . ' text-[10px]"></i>' . $label . '</span>';
};
?>

<!-- Summary chips -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <?php
    $cards = [
      ['fa-boxes-stacked', 'With Stock',      $summary['total_with_stock'] ?? 0, 'slate'],
      ['fa-circle-check',  'Valid (>alert)',  null, 'emerald'],
      ['fa-clock',         'Near Expiry',     $summary['near'] ?? 0, 'amber'],
      ['fa-triangle-exclamation', 'Critical', $summary['critical'] ?? 0, 'red'],
    ];
    $cards[1][2] = max(0, ($summary['total_with_stock'] ?? 0) - ($summary['near'] ?? 0) - ($summary['critical'] ?? 0) - ($summary['expired'] ?? 0));
  ?>
  <?php foreach ($cards as [$icon,$label,$value,$color]): ?>
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
      <div class="flex items-center justify-between">
        <div class="text-xs text-slate-500"><?= e($label) ?></div>
        <div class="h-8 w-8 rounded-lg bg-<?= $color ?>-50 dark:bg-<?= $color ?>-500/10 text-<?= $color ?>-600 dark:text-<?= $color ?>-400 grid place-items-center">
          <i class="fa-solid <?= $icon ?> text-xs"></i>
        </div>
      </div>
      <div class="mt-2 text-2xl font-semibold tracking-tight"><?= (int)$value ?></div>
    </div>
  <?php endforeach; ?>
</div>

<?php if (($summary['expired'] ?? 0) > 0 || ($summary['critical'] ?? 0) > 0): ?>
  <div class="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 text-red-800 dark:text-red-300 px-4 py-3 mb-6 text-sm flex items-start gap-3">
    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
    <div>
      <?php if ($summary['expired'] > 0): ?><strong><?= (int)$summary['expired'] ?></strong> batch(es) already expired.<?php endif; ?>
      <?php if ($summary['critical'] > 0): ?> <strong><?= (int)$summary['critical'] ?></strong> expiring within 30 days.<?php endif; ?>
      <a href="?state=critical" class="underline ml-1">View them</a>
    </div>
  </div>
<?php endif; ?>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <!-- Filters -->
  <form method="get" class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[220px]">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input name="q" value="<?= e($filters['q']) ?>" placeholder="Search product, SKU or batch no…"
             class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <select name="state" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      <option value="">All states</option>
      <?php foreach (['with_stock'=>'With stock','valid'=>'Valid','near'=>'Near expiry','critical'=>'Critical','expired'=>'Expired','depleted'=>'Depleted'] as $val=>$lbl): ?>
        <option value="<?= $val ?>" <?= $filters['state'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
      <?php endforeach; ?>
    </select>
    <button class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900">
      <i class="fa-solid fa-filter"></i> Filter
    </button>
  </form>

  <?php if (empty($batches)): ?>
    <?php
      $icon = 'fa-boxes-stacked';
      $title = 'No batches found';
      $message = 'Receive stock to create your first batch.';
      $actionHtml = '<a href="' . base_url('inventory/receive') . '" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-boxes-packing"></i> Receive Stock</a>';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Product</th>
          <th class="text-left font-medium px-4 py-3">Batch</th>
          <th class="text-left font-medium px-4 py-3">Expiry</th>
          <th class="text-right font-medium px-4 py-3">Remaining</th>
          <th class="text-right font-medium px-4 py-3">Cost</th>
          <th class="text-right font-medium px-4 py-3">Price</th>
          <th class="text-center font-medium px-4 py-3">State</th>
          <th class="text-right font-medium px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($batches as $b): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3">
              <div class="font-medium"><?= e($b['product_name']) ?></div>
              <div class="text-xs text-slate-500 font-mono"><?= e($b['sku']) ?></div>
            </td>
            <td class="px-4 py-3">
              <div class="font-mono text-xs"><?= e($b['batch_no']) ?></div>
              <?php if ($b['supplier_name']): ?><div class="text-xs text-slate-500"><?= e($b['supplier_name']) ?></div><?php endif; ?>
            </td>
            <td class="px-4 py-3">
              <div><?= e(date('d M Y', strtotime($b['expiry_date']))) ?></div>
              <div class="text-xs text-slate-500">
                <?php if ($b['days_to_expiry'] < 0): ?>
                  <?= abs($b['days_to_expiry']) ?> days ago
                <?php elseif ($b['days_to_expiry'] === 0): ?>
                  Today
                <?php else: ?>
                  in <?= $b['days_to_expiry'] ?> days
                <?php endif; ?>
              </div>
            </td>
            <td class="px-4 py-3 text-right font-medium">
              <?= (int)$b['quantity_remaining'] ?> <span class="text-xs text-slate-500">/ <?= (int)$b['quantity_received'] ?></span>
            </td>
            <td class="px-4 py-3 text-right"><?= e(money($b['cost_price'])) ?></td>
            <td class="px-4 py-3 text-right"><?= e(money($b['selling_price'])) ?></td>
            <td class="px-4 py-3 text-center"><?= $stateBadge($b) ?></td>
            <td class="px-4 py-3 text-right">
              <?php if (has_role('admin','pharmacist')): ?>
                <button type="button"
                  onclick='openAdjustModal(<?= json_encode($b, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'
                  class="h-8 w-8 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500" title="Adjust">
                  <i class="fa-solid fa-sliders"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pagination['pages'] > 1): ?>
    <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-800 text-sm">
      <div class="text-slate-500">Showing <?= count($batches) ?> of <?= (int)$pagination['total'] ?></div>
      <div class="flex items-center gap-1">
        <?php for ($i = 1; $i <= $pagination['pages']; $i++):
          $qs = http_build_query(array_filter(['q'=>$filters['q'],'state'=>$filters['state'],'page'=>$i]));
        ?>
          <a href="<?= base_url('inventory?' . $qs) ?>" class="h-8 min-w-8 px-3 grid place-items-center rounded-lg text-sm
            <?= $i === (int)$pagination['page'] ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Adjust Modal -->
<?php ob_start(); ?>
<form id="adjustForm" method="post" action="" class="space-y-4">
  <?= csrf_field() ?>
  <div class="text-sm">
    <div class="text-slate-500">Product</div>
    <div id="adjustProduct" class="font-medium"></div>
    <div class="text-slate-500 mt-2">Batch</div>
    <div id="adjustBatch" class="font-mono text-xs"></div>
  </div>
  <div>
    <label class="block text-sm font-medium mb-1.5">New Quantity Remaining <span class="text-red-500">*</span></label>
    <input type="number" min="0" name="quantity_remaining" required
           class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div>
    <label class="block text-sm font-medium mb-1.5">Reason</label>
    <input name="reason" placeholder="e.g. damaged, counted, expired removal"
           class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
  </div>
  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="adjustModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">Cancel</button>
    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
      <i class="fa-solid fa-check"></i> Apply
    </button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'adjustModal'; $title = 'Adjust Batch Quantity'; require VIEW_PATH . '/partials/modal.php'; ?>

<script>
function openAdjustModal(batch) {
  const form = document.getElementById('adjustForm');
  form.action = '<?= base_url('inventory') ?>/' + batch.id + '/adjust';
  document.getElementById('adjustProduct').textContent = batch.product_name;
  document.getElementById('adjustBatch').textContent = batch.batch_no;
  form.querySelector('[name="quantity_remaining"]').value = batch.quantity_remaining;
  form.querySelector('[name="reason"]').value = '';
  openModal('adjustModal');
}
</script>