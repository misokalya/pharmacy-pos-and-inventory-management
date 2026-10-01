<?php
/**
 * $color = 'emerald'|'amber'|'red'|'slate'|'indigo'|'sky'
 * $label
 */
$color = $color ?? 'slate';
$map = [
  'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
  'amber'   => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
  'red'     => 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
  'slate'   => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
  'indigo'  => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/30',
  'sky'     => 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/10 dark:text-sky-400 dark:border-sky-500/30',
];
?>
<span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-medium <?= $map[$color] ?? $map['slate'] ?>">
  <?php if (!empty($icon)): ?><i class="fa-solid <?= e($icon) ?> text-[10px]"></i><?php endif; ?>
  <?= e($label) ?>
</span>