<?php $err = flash('error'); $ok = flash('success'); ?>
<?php if ($err || $ok): ?>
<div id="toast" class="fixed bottom-6 right-6 z-50 max-w-sm">
  <div class="rounded-xl shadow-lg border px-4 py-3 text-sm flex items-start gap-3
              <?= $err
                  ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300'
                  : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300' ?>">
    <i class="fa-solid <?= $err ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mt-0.5"></i>
    <div><?= e($err ?: $ok) ?></div>
    <button class="ml-auto text-slate-400 hover:text-slate-600" onclick="this.parentElement.parentElement.remove()">
      <i class="fa-solid fa-xmark"></i>
    </button>
  </div>
</div>
<script>setTimeout(() => document.getElementById('toast')?.remove(), 5000);</script>
<?php endif; ?>