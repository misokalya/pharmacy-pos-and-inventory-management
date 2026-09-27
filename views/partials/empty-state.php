<?php
/**
 * $icon, $title, $message, $actionHtml (optional)
 */
?>
<div class="text-center py-16 px-6">
  <div class="mx-auto h-16 w-16 rounded-2xl bg-slate-100 dark:bg-slate-800 grid place-items-center text-slate-400 dark:text-slate-500 text-2xl">
    <i class="fa-solid <?= e($icon ?? 'fa-inbox') ?>"></i>
  </div>
  <h3 class="mt-4 text-base font-semibold"><?= e($title ?? 'Nothing here yet') ?></h3>
  <?php if (!empty($message)): ?>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 max-w-sm mx-auto"><?= e($message) ?></p>
  <?php endif; ?>
  <?php if (!empty($actionHtml)): ?>
    <div class="mt-5"><?= $actionHtml ?></div>
  <?php endif; ?>
</div>