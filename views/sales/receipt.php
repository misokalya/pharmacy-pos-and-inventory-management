<?php
$currency = \App\Core\Database::query("SELECT `value` FROM settings WHERE `key`='currency'")->fetch()['value'] ?? '';
?>
<div class="max-w-sm mx-auto bg-white shadow-lg my-8 p-6 font-mono text-xs leading-relaxed">
  <div class="text-center">
    <div class="text-lg font-bold"><?= e(config('name')) ?></div>
    <div class="text-slate-500">Sales Receipt</div>
  </div>

  <div class="mt-4 border-t border-dashed border-slate-300 pt-4 space-y-1">
    <div class="flex justify-between"><span>Invoice:</span><span class="font-bold"><?= e($sale['invoice_no']) ?></span></div>
    <div class="flex justify-between"><span>Date:</span><span><?= e(date('d M Y H:i', strtotime($sale['created_at']))) ?></span></div>
    <div class="flex justify-between"><span>Cashier:</span><span><?= e($sale['cashier_name']) ?></span></div>
    <?php if ($sale['customer_name']): ?>
      <div class="flex justify-between"><span>Customer:</span><span><?= e($sale['customer_name']) ?></span></div>
    <?php endif; ?>
    <?php if ($sale['customer_phone']): ?>
      <div class="flex justify-between"><span>Phone:</span><span><?= e($sale['customer_phone']) ?></span></div>
    <?php endif; ?>
  </div>

  <div class="mt-4 border-t border-dashed border-slate-300 pt-4">
    <?php foreach ($sale['items'] as $item): ?>
      <div class="mb-2">
        <div class="font-bold"><?= e($item['product_name']) ?></div>
        <div class="flex justify-between">
          <span><?= (int)$item['quantity'] ?> × <?= number_format((float)$item['unit_price'], 2) ?></span>
          <span><span><?= e(money($item['line_total'])) ?></span></span>
        </div>
        <div class="text-[10px] text-slate-500">
          Batch <?= e($item['batch_no']) ?> · Exp <?= e(date('m/Y', strtotime($item['expiry_date']))) ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="mt-4 border-t border-dashed border-slate-300 pt-4 space-y-1">
    <div class="flex justify-between"><span>Subtotal</span><span><?= e(money($sale['subtotal'])) ?></span></div>
<?php if ((float)$sale['discount'] > 0): ?>
  <div class="flex justify-between"><span>Discount</span><span>-<?= e(money($sale['discount'])) ?></span></div>
<?php endif; ?>
<?php if ((float)$sale['tax'] > 0): ?>
  <div class="flex justify-between"><span>Tax</span><span><?= e(money($sale['tax'])) ?></span></div>
<?php endif; ?>
<div class="flex justify-between font-bold text-sm border-t border-slate-300 pt-1 mt-1">
  <span>TOTAL</span><span><?= e(money($sale['total'])) ?></span>
</div>
<div class="flex justify-between"><span>Paid (<?= e(ucfirst($sale['payment_method'])) ?>)</span><span><?= e(money($sale['amount_paid'])) ?></span></div>
<div class="flex justify-between"><span>Change</span><span><?= e(money($sale['change_due'])) ?></span></div>
  </div>

  <div class="text-center mt-6 pt-4 border-t border-dashed border-slate-300 text-slate-500">
    <div>Thank you for your business!</div>
    <div class="mt-1 text-[10px]">Keep this receipt for returns &amp; warranty.</div>
  </div>
</div>