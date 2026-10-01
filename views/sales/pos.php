<?php
$u = auth();
$csrf = csrf_token();
// $taxRate and $currency come from SaleController::pos() via Setting model
$taxRate  = $taxRate  ?? 0.0;
$currency = $currency ?? 'Tsh';
?>
<div class="h-full flex flex-col">

  <!-- Topbar -->
  <header class="h-16 shrink-0 flex items-center justify-between px-5 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
    <div class="flex items-center gap-3">
      <a href="<?= base_url('dashboard') ?>" class="h-9 w-9 grid place-items-center rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500">
        <i class="fa-solid fa-arrow-left"></i>
      </a>
      <div class="h-9 w-9 rounded-xl bg-emerald-500 text-white grid place-items-center">
        <i class="fa-solid fa-cash-register"></i>
      </div>
      <div>
        <div class="text-sm font-semibold"><?= e(config('name')) ?> POS</div>
        <div class="text-xs text-slate-500">Cashier: <?= e($u['name'] ?? '') ?></div>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <a href="<?= base_url('sales') ?>" class="text-sm text-slate-500 hover:text-slate-900 dark:hover:text-white">
        <i class="fa-solid fa-receipt mr-1"></i> History
      </a>
      <form method="post" action="<?= base_url('logout') ?>">
        <?= csrf_field() ?>
        <button class="text-sm text-slate-500 hover:text-red-600">
          <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Logout
        </button>
      </form>
    </div>
  </header>

  <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-[1fr_420px]">
    <!-- Left: Search & products -->
    <section class="min-h-0 flex flex-col p-5 gap-4">
      <div class="relative">
        <i class="fa-solid fa-barcode absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
        <input id="posSearch" type="text" autofocus autocomplete="off"
               placeholder="Scan barcode or search products… (F2 to focus, F9 to checkout)"
               class="w-full rounded-xl border border-slate-300 dark:border-slate-700 dark:bg-slate-900 pl-12 pr-4 py-3.5 text-base focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 shadow-sm">
        <div id="searchStatus" class="hidden absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></div>
      </div>

      <div id="searchResults" class="flex-1 min-h-0 overflow-y-auto">
        <div class="text-center text-sm text-slate-400 py-16">
          <i class="fa-solid fa-magnifying-glass text-2xl mb-3 opacity-50"></i>
          <p>Start typing to search products…</p>
        </div>
      </div>
    </section>

    <!-- Right: Cart -->
    <aside class="min-h-0 flex flex-col bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800">
      <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold">Current Sale</h2>
          <button type="button" onclick="clearCart()" class="text-xs text-slate-500 hover:text-red-600">
            <i class="fa-solid fa-trash-can mr-1"></i> Clear
          </button>
        </div>
      </div>

      <div id="cartItems" class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
        <div class="text-center text-sm text-slate-400 py-16 px-6">
          <i class="fa-solid fa-cart-shopping text-2xl mb-3 opacity-50"></i>
          <p>Cart is empty</p>
        </div>
      </div>

      <!-- Totals -->
      <div class="border-t border-slate-200 dark:border-slate-800 px-5 py-4 space-y-2 text-sm bg-slate-50 dark:bg-slate-900/60">
        <div class="flex justify-between">
          <span class="text-slate-500">Subtotal</span>
          <span id="subtotal" class="font-medium">Tsh 0</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-slate-500">Discount</span>
          <input id="discount" type="number" min="0" step="1" value="0"
                 class="w-28 text-right rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        </div>
        <div class="flex justify-between">
          <span class="text-slate-500">VAT (<span id="taxRateLabel">0</span>%)</span>
          <span id="tax" class="font-medium">Tsh 0</span>
        </div>
        <div class="flex justify-between text-base font-semibold pt-2 border-t border-slate-200 dark:border-slate-800">
          <span>Total</span>
          <span id="total">Tsh 0</span>
        </div>
      </div>

      <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
        <button type="button" onclick="openCheckout()" id="checkoutBtn" disabled
                class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed text-white font-medium py-3.5 transition">
          <i class="fa-solid fa-money-bill-wave mr-2"></i> Checkout <span class="text-xs opacity-70 ml-1">(F9)</span>
        </button>
      </div>
    </aside>
  </div>
</div>

<!-- Checkout Modal -->
<?php ob_start(); ?>
<form id="checkoutForm" class="space-y-4">
  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Customer Name</label>
      <input name="customer_name" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Phone</label>
      <input name="customer_phone" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
      <label class="block text-sm font-medium mb-1.5">Payment Method</label>
      <select name="payment_method" class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
        <option value="cash">Cash</option>
        <option value="card">Card</option>
        <option value="mobile">Mobile Money</option>
        <option value="insurance">Insurance</option>
      </select>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1.5">Amount Paid (Tsh)</label>
      <input type="number" step="1" min="0" name="amount_paid" required
             class="w-full rounded-lg border border-slate-300 dark:border-slate-700 dark:bg-slate-950 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
    </div>
  </div>

  <div class="rounded-xl bg-slate-50 dark:bg-slate-900/60 p-4 space-y-2 text-sm">
    <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span id="coSubtotal" class="font-medium">Tsh 0</span></div>
    <div class="flex justify-between"><span class="text-slate-500">Discount</span><span id="coDiscount" class="font-medium">Tsh 0</span></div>
    <div class="flex justify-between"><span class="text-slate-500">VAT</span><span id="coTax" class="font-medium">Tsh 0</span></div>
    <div class="flex justify-between text-base font-semibold pt-2 border-t border-slate-200 dark:border-slate-800">
      <span>Total Due</span><span id="coTotal">Tsh 0</span>
    </div>
    <div class="flex justify-between text-emerald-600 pt-1">
      <span>Change</span><span id="coChange" class="font-semibold">Tsh 0</span>
    </div>
  </div>

  <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
    <button type="button" data-close="checkoutModal" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700">Cancel</button>
    <button type="submit" id="confirmSaleBtn" class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium bg-emerald-600 hover:bg-emerald-700 text-white">
      <i class="fa-solid fa-check"></i> Complete Sale
    </button>
  </div>
</form>
<?php $content = ob_get_clean(); $id = 'checkoutModal'; $title = 'Complete Sale'; require VIEW_PATH . '/partials/modal.php'; ?>

<script>
const CSRF     = <?= json_encode($csrf) ?>;
const BASE     = <?= json_encode(rtrim(base_url(), '/')) ?>;
const TAX_RATE = <?= json_encode((float)$taxRate) ?>;
const CURRENCY = <?= json_encode((string)$currency) ?>;

let cart = [];

// ---------- Formatting ----------
// TZS is an integer currency — no decimals, thousands separator
function fmt(n) {
  const value = Math.round(Number(n) || 0);
  return CURRENCY + ' ' + value.toLocaleString('en-US');
}
function parseMoney(str) {
  if (!str) return 0;
  return parseFloat(String(str).replace(CURRENCY, '').replace(/[^0-9.\-]/g, '')) || 0;
}
function escapeHtml(s) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ---------- Search & lookup ----------
const searchInput = document.getElementById('posSearch');
const searchResults = document.getElementById('searchResults');
const searchStatus = document.getElementById('searchStatus');
let searchTimer;

searchInput.addEventListener('input', () => {
  clearTimeout(searchTimer);
  const q = searchInput.value.trim();
  if (q.length < 2) {
    searchResults.innerHTML = '<div class="text-center text-sm text-slate-400 py-16"><i class="fa-solid fa-magnifying-glass text-2xl mb-3 opacity-50"></i><p>Start typing to search products…</p></div>';
    return;
  }
  searchTimer = setTimeout(() => doSearch(q), 220);
});

searchInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    const q = searchInput.value.trim();
    if (!q) return;
    // Try barcode/SKU lookup first (scanner-friendly)
    fetch(`${BASE}/sales/lookup?code=${encodeURIComponent(q)}`)
      .then(r => r.json())
      .then(({ result }) => {
        if (result) {
          addToCart(result);
          searchInput.value = '';
          searchResults.innerHTML = '';
        } else {
          doSearch(q);
        }
      });
  }
});

function doSearch(q) {
  searchStatus.textContent = 'Searching…';
  searchStatus.classList.remove('hidden');
  fetch(`${BASE}/sales/search?q=${encodeURIComponent(q)}`)
    .then(r => r.json())
    .then(({ results }) => {
      searchStatus.classList.add('hidden');
      renderResults(results);
    })
    .catch(() => { searchStatus.classList.add('hidden'); });
}

function renderResults(results) {
  if (!results.length) {
    searchResults.innerHTML = '<div class="text-center text-sm text-slate-400 py-16"><i class="fa-solid fa-circle-question text-2xl mb-3 opacity-50"></i><p>No products match your search.</p></div>';
    return;
  }
  searchResults.innerHTML = results.map(p => {
    const disabled = p.stock <= 0;
    const stateColors = {
      valid:    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30',
      near:     'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30',
      critical: 'bg-red-50 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30',
    };
    const stateLabels = { valid: 'OK', near: 'Near expiry', critical: 'Critical' };
    const stateBadge = p.expiry_state && stateColors[p.expiry_state]
      ? `<span class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-medium ${stateColors[p.expiry_state]}"><i class="fa-solid fa-clock"></i>${stateLabels[p.expiry_state]}</span>`
      : '';
    return `
      <button type="button" ${disabled ? 'disabled' : ''}
        onclick='addToCart(${JSON.stringify(p).replace(/'/g, "&#39;")})'
        class="w-full text-left p-4 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-emerald-400 hover:shadow-sm transition flex items-center justify-between gap-4 ${disabled ? 'opacity-50 cursor-not-allowed' : ''} mb-2">
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-medium truncate">${escapeHtml(p.name)}</span>
            ${p.requires_prescription ? '<span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">Rx</span>' : ''}
            ${stateBadge}
          </div>
          <div class="text-xs text-slate-500 mt-1 font-mono">${escapeHtml(p.sku)}${p.barcode ? ' · ' + escapeHtml(p.barcode) : ''}</div>
        </div>
        <div class="text-right shrink-0">
          <div class="font-semibold">${fmt(p.selling_price)}</div>
          <div class="text-xs ${p.stock <= 0 ? 'text-red-500' : 'text-slate-500'}">
            ${p.stock <= 0 ? 'Out of stock' : p.stock + ' ' + escapeHtml(p.unit || 'pcs')}
          </div>
        </div>
      </button>
    `;
  }).join('');
}

// ---------- Cart ----------
function addToCart(product) {
  if (product.stock <= 0) return;
  const existing = cart.find(l => l.product_id === product.id);
  if (existing) {
    if (existing.quantity + 1 > product.stock) return;
    existing.quantity += 1;
  } else {
    cart.push({
      product_id: product.id,
      name: product.name,
      sku: product.sku,
      unit: product.unit || 'pcs',
      unit_price: Number(product.selling_price),
      quantity: 1,
      stock: product.stock,
      requires_prescription: product.requires_prescription,
    });
  }
  renderCart();
  searchInput.focus();
}

function changeQty(productId, delta) {
  const line = cart.find(l => l.product_id === productId);
  if (!line) return;
  line.quantity += delta;
  if (line.quantity <= 0) {
    cart = cart.filter(l => l.product_id !== productId);
  } else if (line.quantity > line.stock) {
    line.quantity = line.stock;
  }
  renderCart();
}

function setQty(productId, qty) {
  const line = cart.find(l => l.product_id === productId);
  if (!line) return;
  const n = Math.max(1, Math.min(parseInt(qty) || 1, line.stock));
  line.quantity = n;
  renderCart();
}

function removeLine(productId) {
  cart = cart.filter(l => l.product_id !== productId);
  renderCart();
}

function clearCart() {
  if (!cart.length) return;
  if (!confirm('Clear the current sale?')) return;
  cart = [];
  document.getElementById('discount').value = 0;
  renderCart();
}

function renderCart() {
  const el = document.getElementById('cartItems');
  if (!cart.length) {
    el.innerHTML = '<div class="text-center text-sm text-slate-400 py-16 px-6"><i class="fa-solid fa-cart-shopping text-2xl mb-3 opacity-50"></i><p>Cart is empty</p></div>';
  } else {
    el.innerHTML = cart.map(l => `
      <div class="p-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <div class="font-medium text-sm truncate">${escapeHtml(l.name)}</div>
            <div class="text-xs text-slate-500 font-mono">${escapeHtml(l.sku)}</div>
            <div class="text-xs text-slate-500 mt-1">${fmt(l.unit_price)} × ${l.quantity}</div>
          </div>
          <div class="text-right">
            <div class="font-semibold text-sm">${fmt(l.unit_price * l.quantity)}</div>
            <div class="flex items-center gap-1 mt-2 justify-end">
              <button type="button" onclick="changeQty(${l.product_id}, -1)" class="h-7 w-7 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"><i class="fa-solid fa-minus text-xs"></i></button>
              <input type="number" min="1" max="${l.stock}" value="${l.quantity}"
                     onchange="setQty(${l.product_id}, this.value)"
                     class="w-14 text-center text-sm rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-950 py-1">
              <button type="button" onclick="changeQty(${l.product_id}, 1)" class="h-7 w-7 grid place-items-center rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800"><i class="fa-solid fa-plus text-xs"></i></button>
              <button type="button" onclick="removeLine(${l.product_id})" class="ml-1 h-7 w-7 grid place-items-center rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10"><i class="fa-solid fa-xmark text-xs"></i></button>
            </div>
          </div>
        </div>
      </div>
    `).join('');
  }
  recalc();
}

// ---------- Totals ----------
document.getElementById('discount').addEventListener('input', recalc);

function computeTotals() {
  const subtotal = cart.reduce((s, l) => s + l.unit_price * l.quantity, 0);
  const discount = Math.max(0, parseFloat(document.getElementById('discount').value) || 0);
  const taxBase  = Math.max(0, subtotal - discount);
  const tax      = Math.round(taxBase * (TAX_RATE / 100));
  const total    = Math.round(taxBase + tax);
  return { subtotal, discount, tax, total };
}

function recalc() {
  const { subtotal, tax, total } = computeTotals();
  document.getElementById('subtotal').textContent = fmt(subtotal);
  document.getElementById('tax').textContent      = fmt(tax);
  document.getElementById('total').textContent    = fmt(total);
  document.getElementById('taxRateLabel').textContent = TAX_RATE;
  document.getElementById('checkoutBtn').disabled = cart.length === 0;
}

// ---------- Checkout ----------
function openCheckout() {
  if (!cart.length) return;
  const { subtotal, discount, tax, total } = computeTotals();

  document.getElementById('coSubtotal').textContent = fmt(subtotal);
  document.getElementById('coDiscount').textContent = fmt(discount);
  document.getElementById('coTax').textContent      = fmt(tax);
  document.getElementById('coTotal').textContent    = fmt(total);

  const paidInput = document.querySelector('#checkoutForm [name="amount_paid"]');
  paidInput.value = total;
  updateChange();
  openModal('checkoutModal');
  setTimeout(() => paidInput.select(), 100);
}

function updateChange() {
  const total = parseMoney(document.getElementById('coTotal').textContent);
  const paid  = parseFloat(document.querySelector('#checkoutForm [name="amount_paid"]').value) || 0;
  document.getElementById('coChange').textContent = fmt(Math.max(0, paid - total));
}
document.querySelector('#checkoutForm [name="amount_paid"]').addEventListener('input', updateChange);

document.getElementById('checkoutForm').addEventListener('submit', (e) => {
  e.preventDefault();
  const form = e.target;
  const data = {
    customer_name:  form.customer_name.value,
    customer_phone: form.customer_phone.value,
    payment_method: form.payment_method.value,
    amount_paid:    parseFloat(form.amount_paid.value) || 0,
    discount:       parseFloat(document.getElementById('discount').value) || 0,
    cart:           cart.map(l => ({ product_id: l.product_id, quantity: l.quantity })),
    _csrf:          CSRF,
  };

  const btn = document.getElementById('confirmSaleBtn');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing…';

  fetch(`${BASE}/sales/checkout`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
    body: JSON.stringify(data),
  })
    .then(r => r.json())
    .then(res => {
      if (!res.ok) throw new Error(res.error || 'Checkout failed.');
      closeModal('checkoutModal');
      cart = [];
      document.getElementById('discount').value = 0;
      renderCart();
      window.open(res.receipt_url, '_blank');
      showToast('Sale completed: ' + res.invoice_no, 'success');
    })
    .catch(err => {
      showToast(err.message, 'error');
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa-solid fa-check"></i> Complete Sale';
    });
});

// ---------- Toast helper (POS-only) ----------
function showToast(message, type = 'success') {
  const id = 'toast-' + Date.now();
  const html = `
    <div id="${id}" class="fixed bottom-6 right-6 z-[100] max-w-sm">
      <div class="rounded-xl shadow-lg border px-4 py-3 text-sm flex items-start gap-3
                  ${type === 'error'
                    ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/30 dark:text-red-300'
                    : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/30 dark:text-emerald-300'}">
        <i class="fa-solid ${type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check'} mt-0.5"></i>
        <div>${escapeHtml(message)}</div>
      </div>
    </div>`;
  document.body.insertAdjacentHTML('beforeend', html);
  setTimeout(() => document.getElementById(id)?.remove(), 4000);
}

// ---------- Keyboard shortcuts ----------
document.addEventListener('keydown', (e) => {
  if (e.key === 'F2') { e.preventDefault(); searchInput.focus(); searchInput.select(); }
  if (e.key === 'F9') { e.preventDefault(); openCheckout(); }
  if (e.key === 'Escape') {
    if (document.activeElement === searchInput) searchInput.value = '';
  }
});
</script>