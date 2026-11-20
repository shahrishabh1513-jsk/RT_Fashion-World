<?php
require __DIR__ . '/includes/bootstrap.php';
$u = require_login(); $o = order_load((string)($_GET['no'] ?? '')); if (!$o || !order_can_view($o)) { http_response_code(404); exit('Order not found.'); }
$page = 'cart'; $title = 'Order placed | RT Fashion Hub'; $desc = 'Your RT Fashion Hub order has been placed.';
require __DIR__ . '/includes/header.php';
?>
<main class="section"><div class="container"><div class="empty" style="max-width:560px;margin:0 auto"><?= icon('check') ?><h3>Thank you – your order is placed!</h3>
  <p>Order number <b><?= e($o['order_no']) ?></b> · Total <?= money($o['total']) ?></p><p class="note">Payment: <?= e($o['payment_method']) ?> (<?= e($o['payment_status']) ?>). You can follow your order live on the tracking page.</p>
  <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:18px"><a class="btn btn--dark" href="<?= url('invoice.php?no=' . $o['order_no']) ?>">View / Download Bill</a><a class="btn btn--outline" href="<?= url('track.php?no=' . $o['order_no']) ?>">Track Order</a><a class="btn btn--outline" href="<?= url('account.php?tab=orders') ?>">My Orders</a></div></div></div></main>
<?php require __DIR__ . '/includes/footer.php';
