<?php
/**
 * $id, $title, $content (HTML), $size = 'md'|'lg'
 */
$size = $size ?? 'md';
$sizeClass = $size === 'lg' ? 'max-w-3xl' : 'max-w-lg';
?>
<div id="<?= e($id) ?>" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
  <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" data-close="<?= e($id) ?>"></div>
  <div class="absolute inset-0 flex items-center justify-center p-4 overflow-y-auto">
    <div class="w-full <?= $sizeClass ?> bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800">
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800">
        <h3 class="text-base font-semibold"><?= e($title) ?></h3>
        <button type="button" data-close="<?= e($id) ?>" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      <div class="p-6"><?= $content ?></div>
    </div>
  </div>
</div>