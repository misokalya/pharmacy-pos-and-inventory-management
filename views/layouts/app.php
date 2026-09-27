<!doctype html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Dashboard') ?> · <?= e(config('name')) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = { darkMode: 'class' };
    if (localStorage.getItem('theme') === 'dark' ||
        (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="h-full bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-100 antialiased">
  <div class="flex h-full">
    <?php require VIEW_PATH . '/partials/sidebar.php'; ?>
    <div class="flex-1 flex flex-col min-w-0">
      <?php require VIEW_PATH . '/partials/topbar.php'; ?>
      <main class="flex-1 overflow-y-auto p-6">
        <?= $content ?>
      </main>
    </div>
  </div>

  <?php require VIEW_PATH . '/partials/toast.php'; ?>
  <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>