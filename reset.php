<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'login'; $title = 'Reset Password | RT Fashion Hub'; $desc = 'Choose a new RT Fashion Hub password.'; $tok = (string)($_GET['token'] ?? $_POST['token'] ?? ''); $err = '';
$row = $tok !== '' ? q('SELECT * FROM password_resets WHERE token_hash=? AND used=0 AND expires_at>NOW()', [hash('sha256', $tok)])->fetch() : false;
if ($row && is_post()) { require_csrf(); $p1 = (string)($_POST['password'] ?? ''); $p2 = (string)($_POST['password2'] ?? '');
    if ($m = pw_problem($p1)) $err = $m; elseif ($p1 !== $p2) $err = 'Passwords do not match.';
    else { q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($p1, PASSWORD_DEFAULT), $row['user_id']]); q('UPDATE password_resets SET used=1 WHERE id=?', [$row['id']]); flash('Password updated. You can log in now.', 'ok'); redirect('login.php'); } }
require __DIR__ . '/includes/header.php';
?>
<main class="section--tight" style="padding:60px 0"><div class="container"><div class="auth-wrap">
  <div style="text-align:center;margin-bottom:26px"><h1 style="font-size:2.2rem">Reset password</h1></div>
  <?php if (!$row): ?><div class="alert alert--err">This reset link is invalid or has expired.</div><p style="text-align:center"><a class="link" href="<?= url('forgot.php') ?>">Request a new link</a></p>
  <?php else: ?><form class="form-card" method="post" novalidate><?= csrf_field() ?><input type="hidden" name="token" value="<?= e($tok) ?>"><?php if ($err): ?><div class="alert alert--err"><?= e($err) ?></div><?php endif; ?>
    <div style="display:grid;gap:16px">
      <div class="field"><label for="p1">New password *</label><div class="pw"><input id="p1" name="password" type="password" autocomplete="new-password" required><button type="button" data-toggle="p1" aria-label="Show password"></button></div></div>
      <div class="field"><label for="p2">Confirm new password *</label><div class="pw"><input id="p2" name="password2" type="password" autocomplete="new-password" required><button type="button" data-toggle="p2" aria-label="Show password"></button></div></div>
      <button class="btn btn--dark btn--block">Update password</button></div></form><?php endif; ?>
</div></div></main>
<?php require __DIR__ . '/includes/footer.php';
