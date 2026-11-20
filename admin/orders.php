<?php
$title = 'Orders | Admin'; $heading = 'Orders'; require_once __DIR__ . '/../includes/bootstrap.php'; require_admin();
$STAT = [...ORDER_STEPS, 'Cancelled'];
if (is_post()) { require_csrf(); $st = (string)($_POST['status'] ?? ''); $id = (int)($_POST['id'] ?? 0);
    if ($id && in_array($st, $STAT, true)) { order_set_status($id, $st, mb_substr(trim((string)($_POST['tracking'] ?? '')), 0, 250), trim((string)($_POST['note'] ?? ''))); flash('Order updated – customers see the change live.', 'ok'); }
    redirect('admin/orders.php' . (!empty($_POST['back']) ? '?' . $_POST['back'] : '')); }
$f = (string)($_GET['status'] ?? ''); $s = trim((string)($_GET['q'] ?? '')); $w = ['1=1']; $p = [];
if (in_array($f, $STAT, true)) { $w[] = 'status=?'; $p[] = $f; } if ($s !== '') { $w[] = '(order_no LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?)'; array_push($p, "%$s%", "%$s%", "%$s%"); }
$orders = q('SELECT * FROM orders WHERE ' . implode(' AND ', $w) . ' ORDER BY id DESC LIMIT 200', $p)->fetchAll(); require __DIR__ . '/_head.php'; $back = http_build_query(array_filter(['status' => $f, 'q' => $s]));
?>
<form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px"><input name="q" value="<?= e($s) ?>" placeholder="Search order no, name or email" style="flex:1;min-width:220px;padding:11px 16px;border:1px solid var(--line);border-radius:999px">
  <select name="status" class="sort"><option value="">All statuses</option><?php foreach ($STAT as $x): ?><option <?= $f === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select><button class="btn btn--dark btn--sm">Filter</button></form>
<?php if (!$orders): ?><div class="empty"><h3>No orders found</h3></div><?php endif; foreach ($orders as $o): $it = q('SELECT product_name,qty FROM order_items WHERE order_id=?', [$o['id']])->fetchAll(); ?>
<div class="order"><div class="order-head"><b><?= e($o['order_no']) ?></b><span><?= e($o['customer_name']) ?> · <?= e($o['customer_email']) ?> · <?= e($o['customer_mobile']) ?></span><span><?= date('j M Y, g:i a', strtotime($o['created_at'])) ?></span><b><?= money($o['total']) ?></b></div>
  <div class="order-body"><p style="font-size:.86rem;color:var(--muted)"><?= e(implode(' • ', array_map(fn($i) => $i['qty'] . '× ' . $i['product_name'], $it))) ?></p><p style="font-size:.86rem;margin:6px 0 12px"><b>Ship to:</b> <?= e($o['shipping_address']) ?> · <b>Payment:</b> <?= e($o['payment_method']) ?> (<?= e($o['payment_status']) ?>)</p>
    <form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $o['id'] ?>"><input type="hidden" name="back" value="<?= e($back) ?>">
      <div class="field"><label>Status</label><select name="status"><?php foreach ($STAT as $x): ?><option <?= $o['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
      <div class="field"><label>Tracking / courier no.</label><input name="tracking" value="<?= e($o['tracking']) ?>" placeholder="e.g. Courier name + AWB"></div>
      <div class="field full"><label>Note shown to customer (optional)</label><input name="note"></div>
      <div class="full" style="display:flex;gap:10px;flex-wrap:wrap"><button class="btn btn--dark btn--sm">Save update</button><a class="btn btn--outline btn--sm" href="<?= url('invoice.php?no=' . $o['order_no']) ?>">Bill</a><a class="btn btn--outline btn--sm" href="<?= url('track.php?no=' . $o['order_no']) ?>">Customer view</a></div></form></div></div>
<?php endforeach; require __DIR__ . '/_foot.php';
