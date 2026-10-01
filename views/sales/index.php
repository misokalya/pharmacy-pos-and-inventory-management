<?php
$actions = '<a href="' . base_url('sales/pos') . '" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
  <i class="fa-solid fa-cash-register"></i><span>Open POS</span></a>';
require VIEW_PATH . '/partials/page-header.php';
?>

<!-- Today summary -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 flex items-center gap-4">
    <div class="h-11 w-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 grid place-items-center">
      <i class="fa-solid fa-receipt"></i>
    </div>
    <div>
      <div class="text-xs text-slate-500">Sales today</div>
      <div class="text-xl font-semibold tracking-tight"><?= (int)$today['count'] ?></div>
    </div>
  </div>
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 flex items-center gap-4">
    <div class="h-11 w-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 grid place-items-center">
      <i class="fa-solid fa-sack-dollar"></i>
    </div>
    <div>
      <div class="text-xs text-slate-500">Revenue today</div>
      <div class="text-xl font-semibold tracking-tight"><?= e(money($today['revenue'])) ?></div>
    </div>
  </div>
</div>

<div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
  <form method="get" class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-3">
    <div class="relative flex-1 min-w-[220px]">
      <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input name="q" value="<?= e($filters['q']) ?>" placeholder="Invoice, customer name or phone…"
             class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <input type="date" name="from" value="<?= e($filters['from']) ?>" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    <input type="date" name="to"   value="<?= e($filters['to']) ?>"   class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    <button class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900">
      <i class="fa-solid fa-filter"></i> Filter
    </button>
    <?php if ($filters['q'] || $filters['from'] || $filters['to']): ?>
      <a href="<?= base_url('sales') ?>" class="text-sm text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">Clear</a>
    <?php endif; ?>
  </form>

  <?php if (empty($sales)): ?>
    <?php
      $icon = 'fa-receipt';
      $title = 'No sales yet';
      $message = 'Sales made in the POS will appear here.';
      $actionHtml = '<a href="' . base_url('sales/pos') . '" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white"><i class="fa-solid fa-cash-register"></i> Open POS</a>';
      require VIEW_PATH . '/partials/empty-state.php';
    ?>
  <?php else: ?>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400">
        <tr>
          <th class="text-left font-medium px-4 py-3">Invoice</th>
          <th class="text-left font-medium px-4 py-3">Date</th>
          <th class="text-left font-medium px-4 py-3">Customer</th>
          <th class="text-left font-medium px-4 py-3">Cashier</th>
          <th class="text-right font-medium px-4 py-3">Items</th>
          <th class="text-right font-medium px-4 py-3">Total</th>
          <th class="text-right font-medium px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
        <?php foreach ($sales as $s): ?>
          <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
            <td class="px-4 py-3 font-mono text-xs"><?= e($s['invoice_no']) ?></td>
            <td class="px-4 py-3">
              <div><?= e(date('d M Y', strtotime($s['created_at']))) ?></div>
              <div class="text-xs text-slate-500"><?= e(date('H:i', strtotime($s['created_at']))) ?></div>
            </td>
            <td class="px-4 py-3">
              <div><?= e($s['customer_name'] ?: 'Walk-in') ?></div>
              <?php if ($s['customer_phone']): ?><div class="text-xs text-slate-500"><?= e($s['customer_phone']) ?></div><?php endif; ?>
            </td>
            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?= e($s['cashier_name']) ?></td>
            <td class="px-4 py-3 text-right"><?= (int)$s['item_count'] ?></td>
            <td class="px-4 py-3 text-right font-medium"><?= e(money($s['total'])) ?></td>
            <td class="px-4 py-3 text-right">
              <a href="<?= base_url('sales/' . (int)$s['id'] . '/receipt') ?>" target="_blank"
                 class="inline-flex items-center gap-1 text-emerald-600 hover:underline text-xs">
                <i class="fa-solid fa-print"></i> Receipt
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pagination['pages'] > 1): ?>
    <div class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-800 text-sm">
      <div class="text-slate-500">Showing <?= count($sales) ?> of <?= (int)$pagination['total'] ?></div>
      <div class="flex items-center gap-1">
        <?php for ($i = 1; $i <= $pagination['pages']; $i++):
          $qs = http_build_query(array_filter(['q'=>$filters['q'],'from'=>$filters['from'],'to'=>$filters['to'],'page'=>$i]));
        ?>
          <a href="<?= base_url('sales?' . $qs) ?>" class="h-8 min-w-8 px-3 grid place-items-center rounded-lg text-sm
            <?= $i === (int)$pagination['page'] ? 'bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900' : 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300' ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
    </div>
  <?php endif; ?>
  <?php endif; ?>
</div>