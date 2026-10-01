<div class="min-h-full flex items-center justify-center p-6">
  <div class="w-full max-w-md">
    <div class="flex flex-col items-center mb-8">
      <div class="h-14 w-14 rounded-2xl bg-emerald-500 text-white grid place-items-center text-2xl shadow-lg shadow-emerald-500/30">
        <i class="fa-solid fa-prescription-bottle-medical"></i>
      </div>
      <h1 class="mt-4 text-2xl font-semibold tracking-tight"><?= e(config('name')) ?></h1>
      <p class="text-sm text-slate-500 mt-1">Sign in to your account</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
      <form method="post" action="<?= base_url('login') ?>" class="space-y-5">
        <?= csrf_field() ?>

        <div>
          <label for="email" class="block text-sm font-medium mb-1.5">Email</label>
          <div class="relative">
            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input id="email" name="email" type="email" required autofocus
                   value="<?= e(old('email')) ?>"
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          </div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium mb-1.5">Password</label>
          <div class="relative">
            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input id="password" name="password" type="password" required
                   class="w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2.5 text-sm
                          focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          </div>
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 transition
                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
          <i class="fa-solid fa-right-to-bracket mr-2"></i> Sign In
        </button>
      </form>
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">
      &copy; <?= date('Y') ?> <?= e(config('name')) ?>. All rights reserved.
    </p>
  </div>
</div>