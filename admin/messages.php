<?php
$title = 'Messages | Admin'; $heading = 'Contact messages'; require_once __DIR__ . '/../includes/bootstrap.php'; require_admin();
if (is_post()) { require_csrf(); $id = (int)($_POST['id'] ?? 0);
    match ($_POST['action'] ?? '') { 'read' => q('UPDATE contact_messages SET is_read=1 WHERE id=?', [$id]), 'delete' => q('DELETE FROM contact_messages WHERE id=?', [$id]), default => null }; redirect('admin/messages.php'); }
$msgs = q('SELECT * FROM contact_messages ORDER BY id DESC LIMIT 200')->fetchAll(); require __DIR__ . '/_head.php';
?>
<?php if (!$msgs): ?><div class="empty"><h3>No messages yet</h3><p>Messages sent from the Contact page appear here.</p></div><?php endif; foreach ($msgs as $m): ?>
<div class="order"><div class="order-head"><b><?= e($m['subject']) ?></b><span><?= e($m['name']) ?> · <a class="link" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['subject']) ?>"><?= e($m['email']) ?></a></span><span><?= date('j M Y, g:i a', strtotime($m['created_at'])) ?></span><span class="pill <?= $m['is_read'] ? '' : 'pill--off' ?>"><?= $m['is_read'] ? 'Read' : 'New' ?></span></div>
  <div class="order-body"><p><?= nl2br(e($m['message'])) ?></p><div style="display:flex;gap:10px;margin-top:12px"><?php if (!$m['is_read']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn--dark btn--sm">Mark as read</button></form><?php endif; ?>
    <form method="post" data-confirm="Delete this message?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="btn btn--outline btn--sm">Delete</button></form></div></div></div>
<?php endforeach; require __DIR__ . '/_foot.php';
