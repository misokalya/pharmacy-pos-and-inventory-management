<?php $u = auth(); ?>
<header class="h-16 flex items-center justify-between px-6 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
  <div>
    <h1 class="text-lg font-semibold tracking-tight"><?= e($title ?? '') ?></h1>
  </div>

  <div class="flex items-center gap-3">
    <!-- Dark mode toggle -->
    <button type="button"
            onclick="toggleTheme()"
            aria-label="Toggle dark mode"
            class="h-9 w-9 rounded-lg border border-slate-200 dark:border-slate-700 grid place-items-center text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
      <i class="fa-solid fa-moon dark:hidden"></i>
      <i class="fa-solid fa-sun hidden dark:inline"></i>
    </button>

    <!-- User menu -->
    <div class="relative shrink-0 w-auto">
      <button type="button"
              data-user-menu-toggle
              onclick="toggleUserMenu()"
              aria-haspopup="true"
              aria-expanded="false"
              class="flex items-center gap-2 pl-3 border-l border-slate-200 dark:border-slate-700">
        <div class="h-9 w-9 rounded-full bg-slate-200 dark:bg-slate-700 grid place-items-center text-slate-600 dark:text-slate-200">
          <i class="fa-solid fa-user"></i>
        </div>
        <div class="hidden sm:block leading-tight text-left">
          <div class="text-sm font-medium"><?= e($u['name'] ?? '') ?></div>
          <div class="text-xs text-slate-500 capitalize"><?= e($u['role'] ?? '') ?></div>
        </div>
        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
      </button>

      <div id="userMenu"
           class="hidden absolute right-0 top-full mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-lg overflow-hidden z-50">
        <a href="<?= base_url('profile') ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
          <i class="fa-solid fa-user w-4 text-center text-slate-400"></i> My Profile
        </a>
        <?php if (has_role('admin')): ?>
          <a href="<?= base_url('settings') ?>" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
            <i class="fa-solid fa-gear w-4 text-center text-slate-400"></i> Settings
          </a>
        <?php endif; ?>
        <div class="border-t border-slate-200 dark:border-slate-800">
          <form method="post" action="<?= base_url('logout') ?>">
            <?= csrf_field() ?>
            <button class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
              <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center"></i> Logout
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>