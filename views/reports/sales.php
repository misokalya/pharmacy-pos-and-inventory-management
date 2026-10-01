<?php require VIEW_PATH . '/partials/page-header.php'; ?>

<form method="get" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 mb-6 flex flex-wrap items-end gap-3">
  <div>
    <label class="block text-xs text-slate-500 mb-1">From</label>
    <input type="date" name="from" value="<?= e($from) ?>" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm">
  </div>
  <div>
    <label class="block text-xs text-slate-500 mb-1">To</label>
    <input type="date" name="to" value="<?= e($to) ?>" class="rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm">
  </div>
  <button class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900">
    <i class="fa-solid fa-rotate"></i> Apply
  </button>
  <div class="ml-auto flex flex-wrap gap-2">
    <?php foreach ([
      ['Today', date('Y-m-d'), date('Y-m-d')],
      ['This Week', date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
      ['This Month', date('Y-m-01'), date('Y-m-d')],
      ['Last 30', date('Y-m-d', strtotime('-30 days')), date('Y-m-d')],
    ] as [$lbl,$f,$t]): ?>
      <a href="?from=<?= $f ?>&to=<?= $t ?>" class="rounded-lg px-3 py-1.5 text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700">
        <?= $lbl ?>
      </a>
    <?php endforeach; ?>
  </div>
</form>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <?php
    $kpis = [
      ['Sales',    (int)($summary['sales_count'] ?? 0), 'emerald'],
      ['Revenue',  money($summary['total'] ?? 0), 'indigo'],
      ['Discount', money($summary['discount'] ?? 0), 'amber'],
      ['Avg Sale', money($summary['avg_sale'] ?? 0), 'slate'],
    ];
  ?>
  <?php foreach ($kpis as [$lbl,$val,$color]): ?>
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4">
      <div class="text-xs text-slate-500"><?= e($lbl) ?></div>
      <div class="mt-1 text-2xl font-semibold tracking-tight text-<?= $color ?>-600 dark:text-<?= $color ?>-400"><?= e((string)$val) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- Daily trend -->
  <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
    <div class="flex items-center justify-between mb-4">
      <h3 class="font-semibold">Revenue by Day</h3>
      <div class="text-xs text-slate-500"><?= e($from) ?> → <?= e($to) ?></div>
    </div>
    <?php if (empty($daily)): ?>
      <div class="text-center text-sm text-slate-400 py-12">No sales in this range.</div>
    <?php else: ?>
      <canvas id="revenueChart" height="120"></canvas>
    <?php endif; ?>
  </div>

  <!-- Top products -->
  <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5">
    <h3 class="font-semibold mb-4">Top Products</h3>
    <?php if (empty($top)): ?>
      <div class="text-center text-sm text-slate-400 py-8">No data.</div>
    <?php else: ?>
      <ul class="space-y-3">
        <?php foreach ($top as $i => $t): ?>
          <li class="flex items-center gap-3">
            <div class="h-7 w-7 rounded-lg bg-slate-100 dark:bg-slate-800 grid place-items-center text-xs font-semibold text-slate-500"><?= $i+1 ?></div>
            <div class="min-w-0 flex-1">
              <div class="text-sm font-medium truncate"><?= e($t['product_name']) ?></div>
              <div class="text-xs text-slate-500"><?= (int)$t['qty_sold'] ?> sold</div>
            </div>
            <div class="text-sm font-semibold"><?= e(money($t['revenue'])) ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($daily)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const daily = <?= json_encode(array_map(fn($r) => [
  'day' => $r['day'],
  'total' => (float)$r['total'],
], $daily)) ?>;

const isDark = document.documentElement.classList.contains('dark');
const textColor = isDark ? '#cbd5e1' : '#475569';
const gridColor = isDark ? 'rgba(148,163,184,0.15)' : 'rgba(100,116,139,0.15)';

new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: daily.map(d => d.day),
    datasets: [{
      label: 'Revenue',
      data: daily.map(d => d.total),
      borderColor: '#10b981',
      backgroundColor: 'rgba(16,185,129,0.12)',
      fill: true,
      tension: 0.35,
      pointRadius: 3,
      pointBackgroundColor: '#10b981',
    }],
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { color: textColor }, grid: { color: gridColor } },
      y: { ticks: { color: textColor }, grid: { color: gridColor }, beginAtZero: true },
    },
  },
});
</script>
<?php endif; ?>