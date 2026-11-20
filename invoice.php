<?php
require __DIR__ . '/includes/bootstrap.php';
$o = order_load((string)($_GET['no'] ?? '')); $email = $_GET['email'] ?? null;
if (!$o || !order_can_view($o, $email)) { http_response_code(404); $page = ''; $title = 'Bill not found | RT Fashion Hub'; require __DIR__ . '/includes/header.php'; echo '<main class="section"><div class="container"><div class="empty"><h3>Bill not found</h3><p>Please log in or use the tracking page with your order number and email.</p><a class="btn btn--dark" href="' . url('track.php') . '">Track Order</a></div></div></main>'; require __DIR__ . '/includes/footer.php'; exit; }
$page = 'account'; $title = 'Bill ' . $o['order_no'] . ' | RT Fashion Hub'; $desc = 'Your RT Fashion Hub bill.';
$order = ['id' => $o['order_no'], 'date' => $o['created_at'], 'customer' => ['name' => $o['customer_name'], 'email' => $o['customer_email'], 'mobile' => $o['customer_mobile']], 'address' => $o['shipping_address'],
  'items' => array_map(fn($i) => ['name' => $i['product_name'], 'qty' => (int)$i['qty'], 'price' => (float)$i['unit_price']], $o['items']),
  'subtotal' => (float)$o['subtotal'], 'shipping' => (float)$o['shipping'], 'gst' => (float)$o['gst'], 'total' => (float)$o['total'], 'payment' => $o['payment_method'], 'paymentStatus' => $o['payment_status'], 'status' => $o['status'], 'tracking' => $o['tracking']];
$bill = ['email' => cfg('email'), 'phone' => cfg('phone'), 'location' => cfg('location'), 'address' => cfg('address'), 'gstin' => cfg('gstin'), 'billTerms' => cfg('bill_terms'), 'gstRate' => cfg('gst_rate'), 'logo' => url('images/brand/logo.png')];
$extraScripts = ['js/vendor/jspdf.umd.min.js', 'js/invoice-pdf.js'];
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero no-print" style="padding:46px 24px"><div class="crumbs">Home / Bill</div><h1>Your Bill</h1></section>
<main class="section--tight"><div class="container" style="max-width:900px">
  <div class="no-print" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:22px"><button class="btn btn--dark" id="dlPdf">Download PDF</button><button class="btn btn--outline" onclick="window.print()">Print</button><a class="btn btn--outline" href="<?= url('track.php?no=' . $o['order_no']) ?>">Track Order</a></div>
  <div class="bill-sheet">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;border-bottom:2px solid var(--accent);padding-bottom:18px"><img src="<?= url('images/brand/logo.png') ?>" alt="RT Fashion Hub" style="height:64px"><div style="text-align:right"><h2 style="font-size:1.9rem">INVOICE</h2><small style="color:var(--muted)">Invoice No: INV-<?= e(str_replace('RT-ORD-', '', $o['order_no'])) ?><br>Date: <?= date('d M Y', strtotime($o['created_at'])) ?><br>Order No: <?= e($o['order_no']) ?></small></div></div>
    <div class="kv" style="margin:20px 0"><div><small>Sold by</small><b>RT Fashion Hub</b><?= e(cfg('address')) ?></div><div><small>Bill to / Ship to</small><b><?= e($o['customer_name']) ?></b><?= e($o['customer_email']) ?><br><?= e($o['customer_mobile']) ?><br><?= e($o['shipping_address']) ?></div></div>
    <div class="tbl-wrap"><table class="tbl"><thead><tr><th>#</th><th>Item</th><th>Qty</th><th>Rate</th><th>Amount</th></tr></thead><tbody>
      <?php foreach ($o['items'] as $k => $i): ?><tr><td><?= $k + 1 ?></td><td><?= e($i['product_name']) ?></td><td><?= $i['qty'] ?></td><td><?= money($i['unit_price']) ?></td><td><?= money($i['qty'] * $i['unit_price']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <div class="totals" style="max-width:320px;margin:20px 0 0 auto"><div class="row"><span>Subtotal</span><span><?= money($o['subtotal']) ?></span></div><div class="row"><span>Delivery</span><span><?= $o['shipping'] > 0 ? money($o['shipping']) : 'Free' ?></span></div><div class="row"><span>GST (<?= cfg('gst_rate') * 100 ?>%)</span><span><?= money($o['gst']) ?></span></div><div class="row grand"><span>Grand Total</span><span><?= money($o['total']) ?></span></div></div>
    <p class="note">Payment: <?= e($o['payment_method']) ?> · <?= e($o['payment_status']) ?> · Order status: <?= e($o['status']) ?></p></div>
</div></main>
<script>window.RT_ORDER = <?= json_encode($order, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>; window.RT_BILL = <?= json_encode($bill, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;
document.addEventListener('DOMContentLoaded', () => document.getElementById('dlPdf').addEventListener('click', () => window.RTInvoice.download(window.RT_ORDER)));</script>
<?php require __DIR__ . '/includes/footer.php';
