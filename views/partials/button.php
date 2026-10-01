<?php
/**
 * $variant = 'primary'|'secondary'|'danger'|'ghost'
 * $label, $icon, $href, $type, $attrs (raw string)
 */
$variant = $variant ?? 'primary';
$type    = $type ?? 'button';
$classes = [
  'primary'   => 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500',
  'secondary' => 'bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 focus:ring-slate-400',
  'danger'    => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500',
  'ghost'     => 'hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 focus:ring-slate-400',
][$variant] ?? '';
$base = "inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-slate-900 $classes";
$inner = (!empty($icon) ? '<i class="fa-solid ' . e($icon) . '"></i>' : '') . '<span>' . e($label ?? '') . '</span>';
?>
<?php if (!empty($href)): ?>
  <a href="<?= e($href) ?>" class="<?= $base ?>" <?= $attrs ?? '' ?>><?= $inner ?></a>
<?php else: ?>
  <button type="<?= e($type) ?>" class="<?= $base ?>" <?= $attrs ?? '' ?>><?= $inner ?></button>
<?php endif; ?>