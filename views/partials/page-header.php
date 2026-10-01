<?php
/**
 * Usage:
 * $title = 'Products';
 * $subtitle = 'Manage your drug catalog';
 * $actions = '<a href="..." class="...">...</a>';  // optional HTML
 * require VIEW_PATH . '/partials/page-header.php';
 */
?>
<div class="flex flex-wrap items-start justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-semibold tracking-tight"><?= e($title ?? '') ?></h1>
    <?php if (!empty($subtitle)): ?>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1"><?= e($subtitle) ?></p>
    <?php endif; ?>
  </div>
  <?php if (!empty($actions)): ?>
    <div class="flex items-center gap-2"><?= $actions ?></div>
  <?php endif; ?>
</div>