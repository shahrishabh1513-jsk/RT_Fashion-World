<?php
require __DIR__ . '/includes/bootstrap.php';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
if (current_user()) redirect($next);
$page = 'login'; $title = 'Login or Register | RT Fashion Hub'; $desc = 'Log in or create your RT Fashion Hub account to track orders, save your wishlist and check out faster.';
$tab = ($_GET['tab'] ?? $_POST['action'] ?? '') === 'register' ? 'register' : 'login'; $err = []; $msg = '';

if (is_post()) {
    require_csrf(); $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        $email = strtolower(trim((string)($_POST['email'] ?? ''))); $pw = (string)($_POST['password'] ?? '');
        if (($_SESSION['lock_until'] ?? 0) > time()) $msg = 'Too many failed attempts. Please wait a minute and try again.';
        elseif (!valid_email($email)) $err['l_email'] = 'Enter a valid email.';
        elseif ($pw === '') $err['l_pass'] = 'Enter your password.';
        else {
            $u = q('SELECT * FROM users WHERE email=?', [$email])->fetch();
            if ($u && password_verify($pw, $u['password_hash'])) {
                unset($_SESSION['fails'], $_SESSION['lock_until']);
                if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) q('UPDATE users SET password_hash=? WHERE id=?', [password_hash($pw, PASSWORD_DEFAULT), $u['id']]);
                login_user($u); if (!empty($_POST['remember'])) setcookie(session_name(), session_id(), ['expires' => time() + 2592000, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
                flash('Welcome back, ' . explode(' ', $u['name'])[0] . '!', 'ok');
                redirect($u['role'] === 'admin' && $next === 'account.php' ? 'admin/index.php' : $next);
            }
            $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1; if ($_SESSION['fails'] >= 5) { $_SESSION['lock_until'] = time() + 60; $_SESSION['fails'] = 0; }
            $msg = 'Incorrect email or password.';
        }
        $tab = 'login';
    } elseif ($action === 'register') {
        $name = trim((string)($_POST['name'] ?? '')); $email = strtolower(trim((string)($_POST['reg_email'] ?? ''))); $mobile = trim((string)($_POST['mobile'] ?? ''));
        $p1 = (string)($_POST['password'] ?? ''); $p2 = (string)($_POST['password2'] ?? ''); $addr = trim((string)($_POST['address'] ?? ''));
        if (mb_strlen($name) < 2) $err['r_name'] = 'Enter your full name.';
        if (!valid_email($email)) $err['r_email'] = 'Enter a valid email.';
        if (!valid_phone($mobile)) $err['r_mobile'] = 'Enter a valid 10-digit Indian mobile number.';
        if ($pw = pw_problem($p1)) $err['r_pass'] = $pw;
        if ($p1 !== $p2) $err['r_pass2'] = 'Passwords do not match.';
        if (empty($_POST['terms'])) $err['r_terms'] = 'Please accept the Terms & Conditions.';
        if (!$err && q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) $err['r_email'] = 'An account with this email already exists. Please log in.';
        if (!$err) {
            for ($try = 0; $try < 3; $try++) {
                try {
                    $n = (int)q("SELECT COALESCE(MAX(CAST(SUBSTRING(customer_id,-4) AS UNSIGNED)),0) m FROM users WHERE customer_id LIKE 'RT1504-USER-%'")->fetch()['m'] + 1;
                    q('INSERT INTO users (customer_id,name,email,mobile,password_hash) VALUES (?,?,?,?,?)', [sprintf('RT1504-USER-%04d', $n), $name, $email, digits_phone($mobile), password_hash($p1, PASSWORD_DEFAULT)]);
                    $uid = (int)db()->lastInsertId(); if ($addr !== '') q('INSERT INTO addresses (user_id,address_text,is_default) VALUES (?,?,1)', [$uid, $addr]);
                    $u = q('SELECT * FROM users WHERE id=?', [$uid])->fetch(); login_user($u);
                    flash('Account created! Your customer ID is ' . $u['customer_id'] . '.', 'ok'); redirect($next);
                } catch (PDOException $ex) { if ($ex->getCode() !== '23000') throw $ex; if (q('SELECT 1 FROM users WHERE email=?', [$email])->fetch()) { $err['r_email'] = 'An account with this email already exists. Please log in.'; break; } }
            }
        }
        $tab = 'register'; if ($err) $msg = 'Please fix the highlighted fields.';
    }
}
$fe = fn($k) => '<span class="err">' . e($err[$k] ?? '') . '</span>'; $inv = fn($k) => isset($err[$k]) ? ' invalid' : '';
$pwField = fn($id, $name, $label, $ac, $ek) => '<div class="field' . $inv($ek) . '"><label for="' . $id . '">' . $label . ' *</label><div class="pw"><input id="' . $id . '" name="' . $name . '" type="password" autocomplete="' . $ac . '" required><button type="button" data-toggle="' . $id . '" aria-label="Show password"></button></div>' . $fe($ek) . '</div>';
require __DIR__ . '/includes/header.php';
?>
<script>document.body.dataset.tab = <?= json_encode($tab) ?>;</script>
<main class="section--tight" style="padding:60px 0"><div class="container"><div class="auth-wrap">
  <div style="text-align:center;margin-bottom:26px"><h1 style="font-size:2.2rem" id="authTitle">Welcome back</h1><p style="color:var(--muted)">Log in or create your RT Fashion Hub account.</p></div>
  <?php if ($msg): ?><div class="alert alert--err" role="alert"><?= e($msg) ?></div><?php endif; ?>
  <div class="tabs" role="tablist"><button type="button" class="on" role="tab" id="tabLogin">Login</button><button type="button" role="tab" id="tabReg">Register</button></div>

  <form class="form-card" id="loginForm" method="post" action="<?= url('login.php') ?>" novalidate><?= csrf_field() ?><input type="hidden" name="action" value="login"><input type="hidden" name="next" value="<?= e($next) ?>">
    <div style="display:grid;gap:16px">
      <div class="field<?= $inv('l_email') ?>"><label for="lEmail">Email Address *</label><input id="lEmail" name="email" type="email" autocomplete="email" value="<?= $tab === 'login' ? old('email') : '' ?>" required><?= $fe('l_email') ?></div>
      <?= $pwField('lPass', 'password', 'Password', 'current-password', 'l_pass') ?>
      <div class="row-between"><label class="check"><input type="checkbox" name="remember" value="1"> Remember Me</label><a href="<?= url('forgot.php') ?>" class="link">Forgot Password?</a></div>
      <button class="btn btn--dark btn--block" type="submit">Login</button>
      <p style="text-align:center;font-size:.9rem">New here? <a href="#register" class="link" id="toReg">Create Account</a></p></div></form>

  <form class="form-card" id="regForm" method="post" action="<?= url('login.php') ?>" novalidate hidden><?= csrf_field() ?><input type="hidden" name="action" value="register"><input type="hidden" name="next" value="<?= e($next) ?>">
    <div class="form-grid">
      <div class="field full<?= $inv('r_name') ?>"><label for="rName">Full Name *</label><input id="rName" name="name" autocomplete="name" value="<?= old('name') ?>" required><?= $fe('r_name') ?></div>
      <div class="field<?= $inv('r_email') ?>"><label for="rEmail">Email Address *</label><input id="rEmail" name="reg_email" type="email" autocomplete="email" value="<?= e($tab === 'register' ? ($_POST['reg_email'] ?? '') : '') ?>" required><?= $fe('r_email') ?></div>
      <div class="field<?= $inv('r_mobile') ?>"><label for="rMobile">Mobile Number *</label><input id="rMobile" name="mobile" type="tel" autocomplete="tel" placeholder="10-digit mobile" value="<?= old('mobile') ?>" required><?= $fe('r_mobile') ?></div>
      <?= $pwField('rPass', 'password', 'Password', 'new-password', 'r_pass') ?><?= $pwField('rPass2', 'password2', 'Confirm Password', 'new-password', 'r_pass2') ?>
      <div class="field full"><label for="rAddr">Address (optional)</label><textarea id="rAddr" name="address" rows="3" autocomplete="street-address"><?= old('address') ?></textarea></div>
      <div class="full"><label class="check"><input type="checkbox" name="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?>> I accept the Terms &amp; Conditions *</label><?= isset($err['r_terms']) ? '<span class="err" style="color:var(--danger);font-size:.76rem">' . e($err['r_terms']) . '</span>' : '' ?></div>
      <div class="full"><button class="btn btn--dark btn--block" type="submit">Create Account</button><p style="text-align:center;font-size:.9rem;margin-top:12px">Already registered? <a href="#login" class="link" id="toLogin">Login</a></p></div></div></form>
</div></div></main>
<?php require __DIR__ . '/includes/footer.php';
