<?php
require __DIR__ . '/includes/bootstrap.php';
$u = require_login(); $page = 'cart'; $title = 'Checkout | RT Fashion Hub'; $desc = 'Review your order and place it with RT Fashion Hub.';
$L = cart_lines(); if (!$L) { flash('Your cart is empty.', 'err'); redirect('cart.php'); }
$saved = q('SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC, id', [$u['id']])->fetchAll(); $err = [];
if (is_post()) {
    require_csrf(); $name = trim((string)($_POST['name'] ?? '')); $mobile = trim((string)($_POST['mobile'] ?? '')); $addr = trim((string)($_POST['address'] ?? '')); $pin = trim((string)($_POST['pin'] ?? '')); $pay = (string)($_POST['pay'] ?? '');
    if (mb_strlen($name) < 2) $err['name'] = 'Enter your name.'; if (!valid_phone($mobile)) $err['mobile'] = 'Enter a valid mobile number.';
    if (mb_strlen($addr) < 10) $err['address'] = 'Enter your full delivery address.'; if (!preg_match('/^[1-9]\d{5}$/', $pin)) $err['pin'] = 'Enter a valid 6-digit PIN code.';
    if ($pay !== 'Cash on Delivery') $err['pay'] = 'Please choose Cash on Delivery – online payment is not enabled yet.';
    if (!$err) {
        $t = cart_totals($L); $pdo = db();
        try {
            $pdo->beginTransaction();
            q('INSERT INTO orders (user_id,customer_name,customer_email,customer_mobile,shipping_address,subtotal,shipping,gst,total,payment_method,payment_status,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
              [$u['id'], $name, $u['email'], digits_phone($mobile), $addr . ' – ' . $pin, $t['subtotal'], $t['shipping'], $t['gst'], $t['total'], 'Cash on Delivery', 'Pay on delivery', 'Order Placed']);
            $oid = (int)$pdo->lastInsertId(); $no = sprintf('RT-ORD-%s-%05d', date('Y'), $oid); q('UPDATE orders SET order_no=? WHERE id=?', [$no, $oid]);
            foreach ($L as $l) q('INSERT INTO order_items (order_id,product_id,product_name,unit_price,qty) VALUES (?,?,?,?,?)', [$oid, $l['p']['id'], $l['p']['name'], $l['unit'], $l['qty']]);
            q('INSERT INTO order_history (order_id,status,note) VALUES (?,?,?)', [$oid, 'Order Placed', 'Order received.']);
            if (!$saved || !empty($_POST['save_addr'])) { $dup = q('SELECT 1 FROM addresses WHERE user_id=? AND address_text=?', [$u['id'], $addr])->fetch(); if (!$dup) q('INSERT INTO addresses (user_id,address_text,pincode,is_default) VALUES (?,?,?,?)', [$u['id'], $addr, $pin, $saved ? 0 : 1]); }
            $pdo->commit(); cart_persist([]); redirect('order-success.php?no=' . $no);
        } catch (Throwable $ex) { if ($pdo->inTransaction()) $pdo->rollBack(); if ($ex instanceof PDOException) { $err['db'] = 'Sorry, we could not place your order. Please try again.'; if ($GLOBALS['CFG']['debug']) $err['db'] .= ' (' . $ex->getMessage() . ')'; } else throw $ex; }
    }
}
$t = cart_totals($L); $fe = fn($k) => '<span class="err">' . e($err[$k] ?? '') . '</span>'; $inv = fn($k) => isset($err[$k]) ? ' invalid' : '';
$opt = fn($v, $t2, $sub, $off) => '<label class="pay-opt ' . ($off ? 'disabled' : '') . '"><input type="radio" name="pay" value="' . $v . '" ' . ($off ? 'disabled' : 'checked') . '><span><b>' . $t2 . '</b><small>' . $sub . '</small></span></label>';
$why = 'Not available yet – needs a payment gateway (e.g. Razorpay) with your API keys.';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="padding:46px 24px"><div class="crumbs">Home / Cart / Checkout</div><h1>Checkout</h1></section>
<main class="section--tight"><div class="container">
<?php if (!empty($err['db'])): ?><div class="alert alert--err"><?= e($err['db']) ?></div><?php endif; ?>
<form class="cart-layout" method="post" novalidate><?= csrf_field() ?><div style="display:grid;gap:26px">
  <div class="panel"><h2>Customer Information</h2><div class="form-grid">
    <div class="field<?= $inv('name') ?>"><label for="coName">Full Name *</label><input id="coName" name="name" value="<?= e($_POST['name'] ?? $u['name']) ?>" required><?= $fe('name') ?></div>
    <div class="field<?= $inv('mobile') ?>"><label for="coMobile">Mobile *</label><input id="coMobile" name="mobile" type="tel" value="<?= e($_POST['mobile'] ?? $u['mobile']) ?>" required><?= $fe('mobile') ?></div>
    <div class="field full"><label for="coEmail">Email</label><input id="coEmail" value="<?= e($u['email']) ?>" readonly></div></div></div>
  <div class="panel"><h2>Shipping Address</h2>
    <?php if ($saved): ?><div class="field" style="margin-bottom:14px"><label for="coSaved">Saved addresses</label><select id="coSaved" onchange="document.getElementById('coAddr').value=this.value"><option value="">Enter a new address</option><?php foreach ($saved as $i => $a): ?><option value="<?= e($a['address_text']) ?>" <?= $i === 0 && !is_post() ? 'selected' : '' ?>><?= e(mb_substr($a['address_text'], 0, 70)) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field<?= $inv('address') ?>"><label for="coAddr">Delivery address *</label><textarea id="coAddr" name="address" rows="4" required><?= e($_POST['address'] ?? ($saved[0]['address_text'] ?? '')) ?></textarea><?= $fe('address') ?></div>
    <div class="field<?= $inv('pin') ?>" style="margin-top:14px;max-width:220px"><label for="coPin">PIN code *</label><input id="coPin" name="pin" inputmode="numeric" maxlength="6" value="<?= e($_POST['pin'] ?? ($saved[0]['pincode'] ?? '')) ?>" required><?= $fe('pin') ?></div>
    <?php if ($saved): ?><label class="check" style="margin-top:14px"><input type="checkbox" name="save_addr" value="1"> Save this address to my address book</label><?php endif; ?></div>
  <div class="panel"><h2>Payment Method</h2><?= $opt('UPI', 'UPI', $why, 1) . $opt('Card', 'Debit / Credit Card', $why, 1) . $opt('NetBanking', 'NetBanking', $why, 1) . $opt('Cash on Delivery', 'Cash on Delivery', 'Pay when your order arrives.', 0) ?><?= $fe('pay') ?></div></div>
  <aside class="summary"><h3>Order Summary</h3>
    <?php foreach ($L as $l): $p = $l['p']; ?><div class="order-line"><a href="<?= url('product.php?id=' . $p['id']) ?>"><img src="<?= e(p_img($p)) ?>" alt="" data-n="<?= e($p['name']) ?>" onerror="rtImgFail(this)"></a><div style="font-size:.84rem"><?= e($p['name']) ?><br><span style="color:var(--muted)">Qty <?= $l['qty'] ?> × <?= money($l['unit']) ?></span></div><b><?= money($l['line']) ?></b></div><?php endforeach; ?>
    <div class="totals" style="margin-top:14px"><div class="row"><span>Subtotal</span><span><?= money($t['subtotal']) ?></span></div><div class="row"><span>Delivery</span><span><?= $t['shipping'] ? money($t['shipping']) : 'Free' ?></span></div><div class="row"><span>GST (<?= cfg('gst_rate') * 100 ?>%)</span><span><?= money($t['gst']) ?></span></div><div class="row grand"><span>Final Amount</span><span><?= money($t['total']) ?></span></div></div>
    <button class="btn btn--dark btn--block" style="margin-top:20px" type="submit">Place Order (Cash on Delivery)</button></aside></form>
</div></main>
<?php require __DIR__ . '/includes/footer.php';
