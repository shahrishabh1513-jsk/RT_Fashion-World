<?php
require __DIR__ . '/includes/bootstrap.php';
if (is_post()) { require_csrf(); $id = (string)($_POST['id'] ?? '');
    match ($_POST['action'] ?? '') { 'set' => cart_set($id, (int)($_POST['qty'] ?? 0)), 'remove' => (function () use ($id) { cart_set($id, 0); flash('Item removed.'); })(), 'clear' => cart_persist([]), default => null };
    redirect('cart.php'); }
$page = 'cart'; $title = 'Shopping Cart | RT Fashion Hub'; $desc = 'Review the items in your RT Fashion Hub shopping cart.';
$L = cart_lines(); $t = cart_totals($L);
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="padding:46px 24px"><div class="crumbs">Home / Cart</div><h1>Shopping Cart</h1></section>
<main class="section--tight"><div class="container">
<?php if (!$L): ?><div class="empty"><?= icon('bag') ?><h3>Your cart is empty</h3><p>Looks like you haven't added anything yet.</p><a class="btn btn--dark" href="<?= url('shop.php') ?>">Continue Shopping</a></div>
<?php else: ?><div class="cart-layout"><div>
  <?php foreach ($L as $l): $p = $l['p']; ?>
  <div class="cart-row"><a href="<?= url('product.php?id=' . $p['id']) ?>"><img src="<?= e(p_img($p)) ?>" alt="<?= e($p['name']) ?>" data-n="<?= e($p['name']) ?>" data-s="<?= e($p['subcategory']) ?>" onerror="rtImgFail(this)"></a>
    <div><span class="pcat"><?= e($p['category']) ?></span><h3><a href="<?= url('product.php?id=' . $p['id']) ?>"><?= e($p['name']) ?></a></h3><div style="color:var(--muted);font-size:.88rem;margin-top:4px">Unit price: <?= money($l['unit']) ?></div>
      <div class="controls"><div class="qty">
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="set"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><input type="hidden" name="qty" value="<?= $l['qty'] - 1 ?>"><button aria-label="Decrease quantity">−</button></form>
        <span><?= $l['qty'] ?></span>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="set"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><input type="hidden" name="qty" value="<?= $l['qty'] + 1 ?>"><button aria-label="Increase quantity">+</button></form></div>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="remove-link">Remove</button></form></div></div>
    <div class="line-total price"><?= money($l['line']) ?></div></div>
  <?php endforeach; ?>
  <div style="margin-top:22px"><a class="btn btn--outline" href="<?= url('shop.php') ?>">← Continue Shopping</a></div></div>
  <aside class="summary"><h3>Order Summary</h3><div class="totals"><div class="row"><span>Subtotal</span><span><?= money($t['subtotal']) ?></span></div><div class="row"><span>Delivery</span><span><?= $t['shipping'] ? money($t['shipping']) : 'Free' ?></span></div><div class="row"><span>GST (<?= cfg('gst_rate') * 100 ?>%)</span><span><?= money($t['gst']) ?></span></div><div class="row grand"><span>Total</span><span><?= money($t['total']) ?></span></div></div>
    <a class="btn btn--dark btn--block" style="margin-top:20px" href="<?= url('checkout.php') ?>">Proceed to Checkout</a></aside></div><?php endif; ?>
</div></main>
<?php require __DIR__ . '/includes/footer.php';
