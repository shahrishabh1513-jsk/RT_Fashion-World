<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'login'; $title = 'Forgot Password | RT Fashion Hub'; $desc = 'Reset your RT Fashion Hub password.'; $link = ''; $done = false; $err = '';
if (is_post()) { require_csrf(); $email = strtolower(trim((string)($_POST['email'] ?? '')));
    if (!valid_email($email)) $err = 'Enter a valid email address.';
    else { $done = true; $u = q('SELECT id FROM users WHERE email=?', [$email])->fetch();
        if ($u) { $tok = bin2hex(random_bytes(32)); q('UPDATE password_resets SET used=1 WHERE user_id=?', [$u['id']]);
            q('INSERT INTO password_resets (user_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(NOW(), INTERVAL 30 MINUTE))', [$u['id'], hash('sha256', $tok)]);
            $link = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . url('reset.php?token=' . $tok);
            /* TODO (production): email $link with mail()/PHPMailer instead of showing it. */ } } }
require __DIR__ . '/includes/header.php';
?>
<main class="section--tight" style="padding:60px 0"><div class="container"><div class="auth-wrap">
  <div style="text-align:center;margin-bottom:26px"><h1 style="font-size:2.2rem">Forgot password</h1><p style="color:var(--muted)">Enter your account email to get a reset link.</p></div>
  <?php if ($done): ?><div class="alert alert--ok">If an account exists for that email, a reset link has been created. It is valid for 30 minutes.</div>
    <?php if ($link && $CFG['debug']): ?><div class="alert alert--warn"><b>Local/XAMPP mode:</b> no mail server is configured, so here is your link:<br><a class="link" style="word-break:break-all" href="<?= e($link) ?>"><?= e($link) ?></a></div><?php endif; ?>
  <?php else: ?><form class="form-card" method="post" novalidate><?= csrf_field() ?>
    <div class="field<?= $err ? ' invalid' : '' ?>"><label for="fe">Email address</label><input id="fe" name="email" type="email" value="<?= old('email') ?>" required><span class="err"><?= e($err) ?></span></div>
    <button class="btn btn--dark btn--block" style="margin-top:16px">Send reset link</button></form><?php endif; ?>
  <p style="text-align:center;margin-top:18px"><a class="link" href="<?= url('login.php') ?>">← Back to login</a></p>
</div></div></main>
<?php require __DIR__ . '/includes/footer.php';
