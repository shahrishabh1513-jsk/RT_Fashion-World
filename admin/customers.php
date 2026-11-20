<?php
$title = 'Customers | Admin'; $heading = 'Customers'; require __DIR__ . '/_head.php';
$cu = q("SELECT u.*, (SELECT COUNT(*) FROM orders o WHERE o.user_id=u.id) oc FROM users u ORDER BY u.id DESC")->fetchAll(); $ns = q('SELECT * FROM newsletter_subscribers ORDER BY id DESC')->fetchAll();
?>
<div class="tbl-wrap"><table class="tbl"><thead><tr><th>Customer ID</th><th>Name</th><th>Email</th><th>Mobile</th><th>Role</th><th>Orders</th><th>Joined</th></tr></thead><tbody>
<?php foreach ($cu as $c): ?><tr><td><?= e($c['customer_id']) ?></td><td><?= e($c['name']) ?></td><td><?= e($c['email']) ?></td><td><?= e($c['mobile']) ?></td><td><span class="pill <?= $c['role'] === 'admin' ? 'pill--ok' : '' ?>"><?= e($c['role']) ?></span></td><td><?= (int)$c['oc'] ?></td><td><?= date('j M Y', strtotime($c['created_at'])) ?></td></tr><?php endforeach; ?></tbody></table></div>
<h2 style="font-size:1.4rem;margin:30px 0 12px">Newsletter subscribers (<?= count($ns) ?>)</h2>
<div class="tbl-wrap"><table class="tbl" style="min-width:0"><thead><tr><th>Email</th><th>Subscribed</th></tr></thead><tbody><?php foreach ($ns as $s): ?><tr><td><?= e($s['email']) ?></td><td><?= date('j M Y', strtotime($s['created_at'])) ?></td></tr><?php endforeach; if (!$ns): ?><tr><td colspan="2" style="color:var(--muted);text-align:center">No subscribers yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require __DIR__ . '/_foot.php';
