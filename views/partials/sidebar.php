<?php $current = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/'); ?>
<aside class="w-64 shrink-0 hidden md:flex flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
  <div class="h-16 flex items-center gap-3 px-5 border-b border-slate-200 dark:border-slate-800">
    <div class="h-9 w-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center">
      <i class="fa-solid fa-prescription-bottle-medical"></i>
    </div>
    <span class="font-semibold tracking-tight"><?= e(config('name')) ?></span>
  </div>

  <nav class="flex-1 p-3 space-y-1 overflow-y-auto text-sm">
    <?php
      $items = [
        ['dashboard', 'fa-gauge-high', 'Dashboard', ['admin','pharmacist','cashier']],
        ['products',  'fa-pills',      'Products',  ['admin','pharmacist']],
        ['categories', 'fa-tags', 'Categories', ['admin','pharmacist']],
        ['inventory', 'fa-boxes-stacked','Inventory',['admin','pharmacist']],
        ['sales/pos', 'fa-cash-register','POS',      ['admin','pharmacist','cashier']],
        ['sales',     'fa-receipt',    'Sales',     ['admin','pharmacist']],
        ['reports',   'fa-chart-line', 'Reports',   ['admin','pharmacist']],
        ['users',     'fa-users',      'Users',     ['admin']],
        ['suppliers', 'fa-truck-field', 'Suppliers', ['admin','pharmacist']],
        ['users', 'fa-users', 'Users', ['admin']],
      ];
      foreach ($items as [$slug, $icon, $label, $roles]):
        if (!has_role(...$roles)) continue;
        $active = str_starts_with($current, $slug);
    ?>
      <a href="<?= base_url($slug) ?>"
         class="flex items-center gap-3 px-3 py-2 rounded-lg transition
                <?= $active
                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 font-medium'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' ?>">
        <i class="fa-solid <?= $icon ?> w-4 text-center"></i>
        <span><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="p-3 border-t border-slate-200 dark:border-slate-800">
    <form method="post" action="<?= base_url('logout') ?>">
      <?= csrf_field() ?>
      <button class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800">
        <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i>
        <span>Logout</span>
      </button>
    </form>
  </div>
</aside>