<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= e($title ?? 'Receipt') ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    @media print { .no-print { display: none !important; } body { background: white !important; } }
  </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
  <div class="no-print fixed top-4 right-4 flex gap-2">
    <button onclick="window.print()" class="rounded-lg bg-slate-900 text-white px-4 py-2 text-sm font-medium"><i class="fa-solid fa-print mr-1"></i> Print</button>
    <button onclick="window.close()" class="rounded-lg bg-white border border-slate-300 px-4 py-2 text-sm font-medium">Close</button>
  </div>
  <?= $content ?>
</body>
</html>