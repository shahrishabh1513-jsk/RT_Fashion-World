<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'track'; $title = 'Track Your Order | RT Fashion Hub'; $desc = 'Track your RT Fashion Hub order status live and download your bill.';
$no = trim((string)($_GET['no'] ?? '')); $email = trim((string)($_GET['email'] ?? '')); $o = null; $msg = '';
if ($no !== '') { $o = order_load($no); if (!$o || !order_can_view($o, $email !== '' ? $email : null)) { $o = null; $msg = 'No order found for that order number and email.'; } }
$u = current_user(); $S = ORDER_STEPS;
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="padding:46px 24px"><div class="crumbs">Home / Order Tracking</div><h1>Track Your Order</h1><p>Enter your order number and email to see live progress.</p></section>
<main class="section--tight"><div class="container" style="max-width:900px">
  <form class="form-card" method="get" action="<?= url('track.php') ?>"><div class="form-grid">
    <div class="field"><label for="tId">Order number</label><input id="tId" name="no" placeholder="RT-ORD-2026-00001" value="<?= e($no) ?>" required></div>
    <div class="field"><label for="tEmail">Email address</label><input id="tEmail" name="email" type="email" value="<?= e($email ?: ($u['email'] ?? '')) ?>"></div>
    <div class="full"><button class="btn btn--dark" type="submit">Track Order</button><?php if ($msg): ?><div class="form-msg err" role="status"><?= e($msg) ?></div><?php endif; ?></div></div></form>
<?php if ($o): $i = order_step($o['status']); $canc = $o['status'] === 'Cancelled'; $hist = $o['history'];
      $when = function ($s) use ($hist) { foreach (array_reverse($hist) as $h) if ($h['status'] === $s) return date('j M, g:i a', strtotime($h['created_at'])); return ''; };
      $sig = implode('|', [$o['status'], $o['tracking'], count($hist)]); $kv = fn($k, $v) => '<div><small>' . $k . '</small><b>' . ($v !== '' ? $v : '—') . '</b></div>'; ?>
  <div class="panel" style="margin-top:28px" id="trackRoot" data-no="<?= e($o['order_no']) ?>" data-email="<?= e($email) ?>" data-sig="<?= e($sig) ?>">
    <div class="order-head" style="border-radius:10px;margin-bottom:22px"><span><b><?= e($o['order_no']) ?></b></span><span>Placed <?= date('d/m/Y', strtotime($o['created_at'])) ?></span><span class="status <?= $canc ? '' : 'status--pending' ?>"><?= e($o['status']) ?></span><b><?= money($o['total']) ?></b></div>
    <?php if ($canc): ?><div class="alert alert--err">This order was cancelled.</div><?php endif; ?>
    <ol class="track <?= $canc ? 'cancelled' : '' ?>" aria-label="Order progress"><?php foreach ($S as $k => $s): ?><li class="<?= $canc ? '' : ($k < $i ? 'done' : ($k === $i ? 'now' : '')) ?>"><span class="dot"><?= !$canc && $k <= $i ? icon('check') : $k + 1 ?></span><b><?= $s ?></b><small><?= $canc ? '' : e($when($s)) ?></small></li><?php endforeach; ?></ol>
    <div class="kv" style="margin:24px 0"><?= $kv('Tracking / courier details', e($o['tracking'])) . $kv('Shipping to', e($o['shipping_address'])) . $kv('Payment', e($o['payment_method']) . ' · ' . e($o['payment_status'])) . $kv('Last updated', date('j M, g:i a', strtotime($o['updated_at']))) ?></div>
    <h3 style="font-size:1.1rem;font-family:var(--font);margin-bottom:10px">Activity</h3><ul class="log"><?php foreach (array_reverse($hist) as $h): ?><li><b><?= e($h['status']) ?></b> <small><?= date('j M, g:i a', strtotime($h['created_at'])) ?></small><?= $h['note'] ? '<br><span style="color:var(--muted)">' . e($h['note']) . '</span>' : '' ?></li><?php endforeach; ?></ul>
    <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px"><a class="btn btn--dark" href="<?= url('invoice.php?no=' . $o['order_no'] . ($email !== '' ? '&email=' . urlencode($email) : '')) ?>">View / Download Bill (PDF)</a><a class="btn btn--outline" href="<?= url('contact.php') ?>">Need help?</a></div>
    <p class="note"><span class="live-dot"></span> Live: this page checks the server every few seconds and refreshes when the store updates your order.</p></div>
<?php endif; ?>
</div></main>
<?php require __DIR__ . '/includes/footer.php';
