<?php
require __DIR__ . '/includes/bootstrap.php';
$u = require_login(); $page = 'account'; $title = 'My Account | RT Fashion Hub'; $desc = 'Manage your RT Fashion Hub profile, orders, wishlist and addresses.';
$TABS = ['dashboard' => 'Dashboard', 'profile' => 'My Profile', 'orders' => 'My Orders', 'cart' => 'Shopping Cart', 'wishlist' => 'Wishlist', 'addresses' => 'Address Book', 'settings' => 'Account Settings'];
$tab = $_GET['tab'] ?? 'dashboard'; if (!isset($TABS[$tab])) $tab = 'dashboard'; if ($tab === 'cart') redirect('cart.php');
$err = []; $ok = '';
if (is_post()) {
    require_csrf(); $a = $_POST['action'] ?? '';
    if ($a === 'profile') {
        $name = trim((string)($_POST['name'] ?? '')); $mobile = trim((string)($_POST['mobile'] ?? '')); $email = strtolower(trim((string)($_POST['email'] ?? ''))); $addr = trim((string)($_POST['address'] ?? ''));
        if (mb_strlen($name) < 2) $err['name'] = 'Enter your name.'; if (!valid_phone($mobile)) $err['mobile'] = 'Enter a valid mobile number.'; if (!valid_email($email)) $err['email'] = 'Enter a valid email.';
        elseif (q('SELECT 1 FROM users WHERE email=? AND id<>?', [$email, $u['id']])->fetch()) $err['email'] = 'That email is already used by another account.';
        if (!$err) {
            q('UPDATE users SET name=?, mobile=?, email=? WHERE id=?', [$name, digits_phone($mobile), $email, $u['id']]);
            $d = q('SELECT id FROM addresses WHERE user_id=? AND is_default=1', [$u['id']])->fetch();
            if ($addr === '') { if ($d) q('DELETE FROM addresses WHERE id=?', [$d['id']]); } elseif ($d) q('UPDATE addresses SET address_text=? WHERE id=?', [$addr, $d['id']]); else q('INSERT INTO addresses (user_id,address_text,is_default) VALUES (?,?,1)', [$u['id'], $addr]);
            flash('Profile updated.', 'ok'); redirect('account.php?tab=profile');
        }
    } elseif ($a === 'add_address') {
        $t = trim((string)($_POST['address'] ?? '')); if (mb_strlen($t) < 10) $err['address'] = 'Enter the full address.';
        else { $first = !q('SELECT 1 FROM addresses WHERE user_id=?', [$u['id']])->fetch(); q('INSERT INTO addresses (user_id,address_text,is_default) VALUES (?,?,?)', [$u['id'], $t, $first ? 1 : 0]); flash('Address saved.', 'ok'); redirect('account.php?tab=addresses'); }
    } elseif ($a === 'del_address') { q('DELETE FROM addresses WHERE id=? AND user_id=?', [(int)$_POST['id'], $u['id']]); if (!q('SELECT 1 FROM addresses WHERE user_id=? AND is_default=1', [$u['id']])->fetch()) q('UPDATE addresses SET is_default=1 WHERE user_id=? ORDER BY id LIMIT 1', [$u['id']]); flash('Address deleted.'); redirect('account.php?tab=addresses');
    } elseif ($a === 'default_address') { q('UPDATE addresses SET is_default=(id=?) WHERE user_id=?', [(int)$_POST['id'], $u['id']]); redirect('account.php?tab=addresses');
    } elseif ($a === 'password') {
        $row = q('SELECT password_hash FROM users WHERE id=?', [$u['id']])->fetch(); $n1 = (string)($_POST['new'] ?? '');
        if (!password_verify((string)($_POST['old'] ?? ''), $row['password_hash'])) $err['old'] = 'Current password is incorrect.';
        if ($m = pw_problem($n1)) $err['new'] = $m; if ($n1 !== (string)($_POST['new2'] ?? '')) $err['new2'] = 'Passwords do not match.';
        if (!$err) { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($n1, PASSWORD_DEFAULT), $u['id']]); flash('Password updated.', 'ok'); redirect('account.php?tab=settings'); }
    }
}
$u = q('SELECT * FROM users WHERE id=?', [$u['id']])->fetch(); $addrs = q('SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC, id', [$u['id']])->fetchAll();
$kv = fn($k, $v) => '<div><small>' . $k . '</small><b>' . ($v !== '' ? $v : '—') . '</b></div>';
$fe = fn($k) => '<span class="err">' . e($err[$k] ?? '') . '</span>'; $inv = fn($k) => isset($err[$k]) ? ' invalid' : '';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero" style="padding:46px 24px"><div class="crumbs">Home / My Account</div><h1>My Account</h1></section>
<main class="section--tight"><div class="container"><div class="acc-layout">
  <nav class="acc-nav" aria-label="Account"><?php foreach ($TABS as $k => $l): ?><a class="<?= $k === $tab ? 'on' : '' ?>" href="<?= url('account.php?tab=' . $k) ?>"><?= $l ?></a><?php endforeach; ?>
    <form method="post" action="<?= url('logout.php') ?>"><?= csrf_field() ?><button>Logout</button></form></nav>
  <div>
<?php if ($tab === 'dashboard'): $oc = (int)q('SELECT COUNT(*) c FROM orders WHERE user_id=?', [$u['id']])->fetch()['c']; $wc = (int)q('SELECT COUNT(*) c FROM wishlist WHERE user_id=?', [$u['id']])->fetch()['c']; ?>
    <div class="welcome"><h2>Welcome, <?= e(explode(' ', $u['name'])[0]) ?>!</h2><p>Manage your orders, wishlist and details from here.</p></div>
    <div class="panel"><h2>Account Information</h2><div class="kv"><?= $kv('Customer Name', e($u['name'])) . $kv('Customer ID', e($u['customer_id'])) . $kv('Registered Email', e($u['email'])) . $kv('Mobile Number', e($u['mobile'])) . $kv('Saved Address', e($addrs[0]['address_text'] ?? 'No address saved yet')) . $kv('Member Since', date('j M Y', strtotime($u['created_at']))) ?></div>
      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px"><a class="btn btn--dark btn--sm" href="<?= url('account.php?tab=orders') ?>"><?= $oc ?> order<?= $oc === 1 ? '' : 's' ?></a><a class="btn btn--outline btn--sm" href="<?= url('account.php?tab=wishlist') ?>"><?= $wc ?> wishlist item<?= $wc === 1 ? '' : 's' ?></a><?php if ($u['role'] === 'admin'): ?><a class="btn btn--gold btn--sm" href="<?= url('admin/index.php') ?>">Admin Panel</a><?php endif; ?></div></div>

<?php elseif ($tab === 'profile'): ?>
    <div class="panel"><h2>My Profile</h2><form method="post" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="profile"><div class="form-grid">
      <div class="field<?= $inv('name') ?>"><label for="pName">Full Name</label><input id="pName" name="name" value="<?= e($_POST['name'] ?? $u['name']) ?>"><?= $fe('name') ?></div>
      <div class="field<?= $inv('mobile') ?>"><label for="pMobile">Mobile Number</label><input id="pMobile" name="mobile" type="tel" value="<?= e($_POST['mobile'] ?? $u['mobile']) ?>"><?= $fe('mobile') ?></div>
      <div class="field<?= $inv('email') ?>"><label for="pEmail">Email</label><input id="pEmail" name="email" type="email" value="<?= e($_POST['email'] ?? $u['email']) ?>"><?= $fe('email') ?></div>
      <div class="field"><label>Customer ID</label><input value="<?= e($u['customer_id']) ?>" readonly></div>
      <div class="field full"><label for="pAddr">Primary Address</label><textarea id="pAddr" name="address" rows="3"><?= e($_POST['address'] ?? ($addrs[0]['address_text'] ?? '')) ?></textarea></div>
      <div class="full"><button class="btn btn--dark" type="submit">Save Changes</button></div></div></form></div>

<?php elseif ($tab === 'orders'):
    $orders = q('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC', [$u['id']])->fetchAll(); ?>
    <div class="panel"><h2>My Orders</h2>
    <?php if (!$orders): ?><div class="empty"><?= icon('bag') ?><h3>No orders yet</h3><p>Your orders will appear here.</p><a class="btn btn--dark" href="<?= url('shop.php') ?>">Start Shopping</a></div>
    <?php else: foreach ($orders as $o): $items = q('SELECT oi.*, p.image, p.subcategory FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE order_id=?', [$o['id']])->fetchAll(); ?>
      <div class="order"><div class="order-head"><b><?= e($o['order_no']) ?></b><span><?= date('j M Y', strtotime($o['created_at'])) ?></span><span><?= e($o['payment_method']) ?></span><span class="status status--pending"><?= e($o['status']) ?></span><b><?= money($o['total']) ?></b></div>
        <div class="order-body"><?php foreach ($items as $i): $pimg = ['image' => $i['image'] ?? '', 'name' => $i['product_name'], 'subcategory' => $i['subcategory'] ?? '']; ?>
          <div class="order-line"><img src="<?= e(p_img($pimg)) ?>" alt="" data-n="<?= e($i['product_name']) ?>" onerror="rtImgFail(this)"><div style="font-size:.9rem"><?= e($i['product_name']) ?><br><span style="color:var(--muted)">Qty <?= $i['qty'] ?> × <?= money($i['unit_price']) ?></span></div><b><?= money($i['qty'] * $i['unit_price']) ?></b></div><?php endforeach; ?>
          <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px"><a class="btn btn--dark btn--sm" href="<?= url('track.php?no=' . $o['order_no']) ?>">Track Order</a><a class="btn btn--outline btn--sm" href="<?= url('invoice.php?no=' . $o['order_no']) ?>">Download Bill (PDF)</a></div>
          <details style="margin-top:12px"><summary class="link" style="cursor:pointer">Order details</summary><div class="kv" style="margin:14px 0"><?= $kv('Customer', e($o['customer_name']) . '<br>' . e($o['customer_email']) . '<br>' . e($o['customer_mobile'])) . $kv('Shipping Address', e($o['shipping_address'])) . $kv('Payment Status', e($o['payment_status'])) . $kv('Tracking', e($o['tracking']) ?: 'Not available yet') ?></div>
            <div class="totals"><div class="row"><span>Subtotal</span><span><?= money($o['subtotal']) ?></span></div><div class="row"><span>Delivery</span><span><?= $o['shipping'] > 0 ? money($o['shipping']) : 'Free' ?></span></div><div class="row"><span>Tax (GST)</span><span><?= money($o['gst']) ?></span></div><div class="row grand"><span>Final Amount</span><span><?= money($o['total']) ?></span></div></div></details></div></div>
    <?php endforeach; endif; ?></div>

<?php elseif ($tab === 'wishlist'): $w = q('SELECT p.* FROM wishlist w JOIN products p ON p.id=w.product_id WHERE w.user_id=? AND p.is_active=1 ORDER BY w.created_at DESC', [$u['id']])->fetchAll(); ?>
    <div class="panel"><h2>Wishlist</h2><?php if ($w): ?><div class="product-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))"><?php foreach ($w as $p) echo product_card($p); ?></div>
    <?php else: ?><div class="empty"><?= icon('heart') ?><h3>Your wishlist is empty</h3><p>Tap the heart on any product to save it.</p><a class="btn btn--dark" href="<?= url('shop.php') ?>">Browse Products</a></div><?php endif; ?></div>

<?php elseif ($tab === 'addresses'): ?>
    <div class="panel"><h2>Address Book</h2>
    <?php if (!$addrs): ?><p style="color:var(--muted);margin-bottom:16px">No saved addresses yet.</p><?php endif; foreach ($addrs as $a): ?>
      <div class="addr-card"><div><?php if ($a['is_default']): ?><span class="tag tag--gold" style="display:inline-block;margin-bottom:8px">Default</span><br><?php endif; ?><?= nl2br(e($a['address_text'])) ?></div>
        <div style="display:grid;gap:6px;justify-items:end"><?php if (!$a['is_default']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="default_address"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="link">Make default</button></form><?php endif; ?>
          <form method="post" data-confirm="Delete this address?"><?= csrf_field() ?><input type="hidden" name="action" value="del_address"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="remove-link">Delete</button></form></div></div><?php endforeach; ?>
      <form method="post" style="margin-top:18px" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="add_address"><div class="field<?= $inv('address') ?>"><label for="aNew">Add a new address</label><textarea id="aNew" name="address" rows="3" placeholder="House no., street, city, state, PIN"></textarea><?= $fe('address') ?></div><button class="btn btn--dark btn--sm" style="margin-top:12px">Save Address</button></form></div>

<?php else: $pw = fn($id, $n, $l, $ac, $k) => '<div class="field' . $inv($k) . '"><label for="' . $id . '">' . $l . '</label><div class="pw"><input id="' . $id . '" name="' . $n . '" type="password" autocomplete="' . $ac . '"><button type="button" data-toggle="' . $id . '" aria-label="Show password"></button></div>' . $fe($k) . '</div>'; ?>
    <div class="panel"><h2>Account Settings</h2><h3 style="font-size:1.1rem;margin-bottom:14px;font-family:var(--font)">Change password</h3>
      <form method="post" style="display:grid;gap:14px;max-width:420px" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="password"><?= $pw('sOld', 'old', 'Current password', 'current-password', 'old') . $pw('sNew', 'new', 'New password', 'new-password', 'new') . $pw('sNew2', 'new2', 'Confirm new password', 'new-password', 'new2') ?><button class="btn btn--dark" type="submit">Update Password</button></form></div>
<?php endif; ?>
  </div></div></div></main>
<?php require __DIR__ . '/includes/footer.php';
