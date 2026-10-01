/* ============================================================
   PharmaCare — Global JS
   ============================================================ */

// ---------- Theme ----------
function toggleTheme() {
  const root = document.documentElement;
  const isDark = root.classList.toggle('dark');
  localStorage.setItem('theme', isDark ? 'dark' : 'light');

  // Notify charts / other listeners that theme changed
  window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: isDark } }));
}

// ---------- Modals ----------
function openModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('hidden');
  document.body.classList.add('overflow-hidden');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('hidden');
  // Only unlock body scroll if no other modal is open
  if (!document.querySelector('[role="dialog"]:not(.hidden)')) {
    document.body.classList.remove('overflow-hidden');
  }
}

// Close on backdrop click / data-close button
document.addEventListener('click', (e) => {
  const closeBtn = e.target.closest('[data-close]');
  if (closeBtn) closeModal(closeBtn.getAttribute('data-close'));
});

// Close on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('[role="dialog"]:not(.hidden)').forEach(m => {
      m.classList.add('hidden');
    });
    if (!document.querySelector('[role="dialog"]:not(.hidden)')) {
      document.body.classList.remove('overflow-hidden');
    }
  }
});

// Auto-open modal via hash: #open=modalId
if (location.hash.startsWith('#open=')) {
  openModal(location.hash.replace('#open=', ''));
  history.replaceState(null, '', location.pathname + location.search);
}

// ---------- Confirm helper ----------
function confirmDelete(message = 'Are you sure? This cannot be undone.') {
  return confirm(message);
}

// ---------- Search auto-submit (debounced) ----------
document.querySelectorAll('input[name="q"]').forEach(input => {
  let t;
  input.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => input.form?.submit(), 400);
  });
});

// ---------- User menu dropdown ----------
document.addEventListener('click', (e) => {
  const menu = document.getElementById('userMenu');
  if (!menu || menu.classList.contains('hidden')) return;

  const clickedInsideMenu = e.target.closest('#userMenu');
  const clickedToggle     = e.target.closest('[data-user-menu-toggle]');
  if (!clickedInsideMenu && !clickedToggle) {
    menu.classList.add('hidden');
  }
});

function toggleUserMenu() {
  const menu = document.getElementById('userMenu');
  const btn  = document.querySelector('[data-user-menu-toggle]');
  if (!menu || !btn) return;

  const isOpening = menu.classList.contains('hidden');
  menu.classList.toggle('hidden');
  btn.setAttribute('aria-expanded', isOpening ? 'true' : 'false');
};