<?php
$title = 'Admin Dashboard | RT Fashion Hub'; $heading = 'Dashboard'; require __DIR__ . '/_head.php';
$n = fn($sql) => (int)q($sql)->fetch()['c'];
$rev = (float)q("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status<>'Cancelled'")->fetch()['s'];
$recent = q('SELECT * FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<div class="stat-cards">
  <div class="stat-card"><b><?= $n('SELECT COUNT(*) c FROM orders') ?></b><span>Orders</span></div>
  <div class="stat-card"><b><?= $n("SELECT COUNT(*) c FROM orders WHERE status NOT IN ('Delivered','Cancelled')") ?></b><span>Open orders</span></div>
  <div class="stat-card"><b><?= money($rev) ?></b><span>Order value</span></div>
  <div class="stat-card"><b><?= $n('SELECT COUNT(*) c FROM products WHERE is_active=1') ?></b><span>Live products</span></div>
  <div class="stat-card"><b><?= $n("SELECT COUNT(*) c FROM users WHERE role='customer'") ?></b><span>Customers</span></div>
  <div class="stat-card"><b><?= $n('SELECT COUNT(*) c FROM contact_messages WHERE is_read=0') ?></b><span>Unread messages</span></div></div>
<h2 style="font-size:1.5rem;margin-bottom:14px">Recent orders</h2>
<div class="tbl-wrap"><table class="tbl"><thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recent as $o): ?><tr><td><a class="link" href="<?= url('admin/orders.php?q=' . $o['order_no']) ?>"><?= e($o['order_no']) ?></a></td><td><?= e($o['customer_name']) ?></td><td><?= date('j M Y', strtotime($o['created_at'])) ?></td><td><?= money($o['total']) ?></td><td><span class="status status--pending"><?= e($o['status']) ?></span></td></tr>
<?php endforeach; if (!$recent): ?><tr><td colspan="5" style="text-align:center;color:var(--muted)">No orders yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require __DIR__ . '/_foot.php';
