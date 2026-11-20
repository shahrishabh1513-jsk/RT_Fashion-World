<?php
require __DIR__ . '/includes/bootstrap.php';
if (!is_post()) redirect('index.php'); require_csrf();
$email = strtolower(trim((string)($_POST['email'] ?? '')));
if (!valid_email($email)) flash('That email address doesn\'t look right.', 'err');
elseif (q('SELECT 1 FROM newsletter_subscribers WHERE email=?', [$email])->fetch()) flash('You\'re already subscribed. Thank you!', 'ok');
else { q('INSERT INTO newsletter_subscribers (email) VALUES (?)', [$email]); flash('You\'re subscribed. Welcome to RT Fashion Hub!', 'ok'); }
$back = $_SERVER['HTTP_REFERER'] ?? ''; redirect(str_contains($back, $_SERVER['HTTP_HOST'] ?? '#') ? $back : 'index.php');
