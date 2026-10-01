<div class="space-y-6">
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
    <h2 class="text-xl font-semibold tracking-tight">
      Welcome back, <?= e($user['name'] ?? 'User') ?> 👋
    </h2>
    <p class="text-sm text-slate-500 mt-1">
      Signed in as <span class="capitalize font-medium"><?= e($user['role'] ?? '') ?></span>.
    </p>
  </div>

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <?php
      $cards = [
        ['fa-pills',               'Products',        $productCount,                     'emerald'],
        ['fa-cash-register',       'Sales Today',     $today['count'],                   'indigo'],
        ['fa-sack-dollar', 'Revenue Today', money($today['revenue']), 'amber'],
        ['fa-triangle-exclamation','Critical/Expired', ($summary['critical'] ?? 0) + ($summary['expired'] ?? 0), 'red'],
      ];
    ?>
    <?php foreach ($cards as [$icon,$label,$value,$color]): ?>
      <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
        <div class="flex items-center justify-between">
          <div class="text-sm text-slate-500"><?= e($label) ?></div>
          <div class="h-9 w-9 rounded-lg bg-<?= $color ?>-50 dark:bg-<?= $color ?>-500/10 text-<?= $color ?>-600 dark:text-<?= $color ?>-400 grid place-items-center">
            <i class="fa-solid <?= $icon ?> text-sm"></i>
          </div>
        </div>
        <div class="mt-3 text-2xl font-semibold tracking-tight"><?= e((string)$value) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Expiring soon -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-clock text-amber-500"></i>
          <h3 class="font-semibold">Expiring in 60 days</h3>
        </div>
        <a href="<?= base_url('reports/expiry') ?>" class="text-xs text-emerald-600 hover:underline">View all →</a>
      </div>
      <?php if (empty($expiringSoon)): ?>
        <div class="px-5 py-10 text-center text-sm text-slate-400">
          <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-2"></i>
          <p>Nothing expiring soon. Good job!</p>
        </div>
      <?php else: ?>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800 max-h-80 overflow-y-auto">
          <?php foreach (array_slice($expiringSoon, 0, 8) as $b): ?>
            <li class="px-5 py-3 flex items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="text-sm font-medium truncate"><?= e($b['product_name']) ?></div>
                <div class="text-xs text-slate-500">
                  Batch <?= e($b['batch_no']) ?> · <?= (int)$b['quantity_remaining'] ?> left
                </div>
              </div>
              <div class="text-right shrink-0">
                <?php
                  $days = (int)$b['days_left'];
                  $cls = $days < 0 ? 'text-red-600' : ($days <= 30 ? 'text-red-500' : 'text-amber-500');
                ?>
                <div class="text-xs font-medium <?= $cls ?>">
                  <?= $days < 0 ? abs($days).' days ago' : ($days === 0 ? 'Today' : 'in '.$days.' days') ?>
                </div>
                <div class="text-xs text-slate-400"><?= e(date('d M Y', strtotime($b['expiry_date']))) ?></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Low stock -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <i class="fa-solid fa-cart-arrow-down text-amber-500"></i>
          <h3 class="font-semibold">Low Stock</h3>
        </div>
        <a href="<?= base_url('reports/low-stock') ?>" class="text-xs text-emerald-600 hover:underline">View all →</a>
      </div>
      <?php if (empty($lowStock)): ?>
        <div class="px-5 py-10 text-center text-sm text-slate-400">
          <i class="fa-solid fa-circle-check text-2xl text-emerald-500 mb-2"></i>
          <p>All products are above reorder level.</p>
        </div>
      <?php else: ?>
        <ul class="divide-y divide-slate-100 dark:divide-slate-800">
          <?php foreach ($lowStock as $p): ?>
            <li class="px-5 py-3 flex items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="text-sm font-medium truncate"><?= e($p['name']) ?></div>
                <div class="text-xs text-slate-500 font-mono"><?= e($p['sku']) ?></div>
              </div>
              <div class="text-right shrink-0">
                <div class="text-sm font-medium <?= (int)$p['current_stock'] === 0 ? 'text-red-600' : 'text-amber-600' ?>">
                  <?= (int)$p['current_stock'] ?> / <?= (int)$p['reorder_level'] ?>
                </div>
                <div class="text-xs text-slate-400"><?= e($p['unit']) ?></div>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

  </div>
</div>