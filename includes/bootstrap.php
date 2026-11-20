<?php
/* Loaded by every page: session, config, database, security + shared helpers. */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
$CFG = require __DIR__ . '/config.php';
date_default_timezone_set($CFG['timezone']);
error_reporting(E_ALL);
ini_set('display_errors', $CFG['debug'] ? '1' : '0');

/* ---- session (httponly cookie, strict mode) ---- */
ini_set('session.use_strict_mode', '1'); ini_set('session.gc_maxlifetime', '2592000');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_name('RTFHSESS');
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---- base URL (works in http://localhost/RT_Fashion/ or any folder) ---- */
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''), '/');
$rootFs  = str_replace('\\', '/', ROOT_PATH);
define('BASE_URL', $docRoot !== '' && str_starts_with($rootFs, $docRoot) ? substr($rootFs, strlen($docRoot)) : '');

function cfg(string $k, $default = null) { global $CFG; return $CFG['site'][$k] ?? $default; }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function e($s): string { return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path))); exit; }
function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'; }
function old(string $k, string $d = ''): string { return e($_POST[$k] ?? $d); }

/* ---- database (PDO, prepared statements only) ---- */
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    global $CFG; $d = $CFG['db'];
    try {
        $pdo = new PDO("mysql:host={$d['host']};dbname={$d['name']};charset={$d['charset']}", $d['user'], $d['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    } catch (PDOException $ex) {
        http_response_code(500);
        echo '<!doctype html><meta charset="utf-8"><title>Database error</title><body style="font-family:system-ui;max-width:640px;margin:60px auto;padding:0 20px"><h1>Database connection failed</h1>'
           . '<p>Start <b>Apache</b> and <b>MySQL</b> in the XAMPP Control Panel, create/import the database <code>' . e($d['name']) . '</code> using <code>database/rt_fashion.sql</code>, and check <code>includes/config.php</code>.</p>'
           . ($GLOBALS['CFG']['debug'] ? '<pre style="background:#f4f4f4;padding:12px;white-space:pre-wrap">' . e($ex->getMessage()) . '</pre>' : '') . '</body>';
        exit;
    }
    return $pdo;
}
function q(string $sql, array $params = []): PDOStatement { $s = db()->prepare($sql); $s->execute($params); return $s; }

/* ---- CSRF ---- */
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok(?string $t = null): bool { $t ??= ($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''); return is_string($t) && hash_equals($_SESSION['csrf'] ?? '', $t); }
function require_csrf(): void { if (!csrf_ok()) { http_response_code(419); exit('Your session expired. Please go back, refresh the page and try again.'); } }

/* ---- flash messages ---- */
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'][] = [$msg, $type]; }
function flashes(): array { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

/* ---- auth ---- */
function current_user(): ?array {
    static $u = false;
    if ($u === false) { $u = null; if (!empty($_SESSION['uid'])) { $u = q('SELECT id,customer_id,name,email,mobile,role,created_at FROM users WHERE id=?', [$_SESSION['uid']])->fetch() ?: null; if (!$u) unset($_SESSION['uid']); } }
    return $u;
}
function require_login(): array { $u = current_user(); if (!$u) { flash('Please log in to continue.', 'err'); redirect('login.php?next=' . urlencode(ltrim(substr($_SERVER['SCRIPT_NAME'], strlen(BASE_URL)), '/') . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''))); } return $u; }
function require_admin(): array { $u = require_login(); if ($u['role'] !== 'admin') { http_response_code(403); exit('403 – Admins only.'); } return $u; }
function safe_next(?string $n): string { return ($n && preg_match('#^[a-z0-9_\-/]+\.php(\?[A-Za-z0-9_=&%\-.]*)?$#i', $n)) ? $n : 'account.php'; }
function login_user(array $u): void {
    session_regenerate_id(true); $_SESSION['uid'] = (int)$u['id'];
    q('UPDATE users SET last_login=NOW() WHERE id=?', [$u['id']]);
    cart_merge_on_login((int)$u['id']);
}
function valid_email(string $e): bool { return (bool)filter_var($e, FILTER_VALIDATE_EMAIL); }
function valid_phone(string $p): bool { return (bool)preg_match('/^(\+?91[\s-]?)?[6-9]\d{9}$/', preg_replace('/[\s-]/', '', $p) ?? ''); }
function pw_problem(string $p): string { return strlen($p) < 8 ? 'Password must be at least 8 characters.' : (!preg_match('/[A-Za-z]/', $p) || !preg_match('/\d/', $p) ? 'Use letters and at least one number.' : ''); }
function digits_phone(string $p): string { $d = preg_replace('/\D/', '', $p) ?? ''; return substr($d, -10); }

/* ---- formatting ---- */
function money(float|int|string $n): string {
    $n = (float)$n; $neg = $n < 0; $n = abs($n); $dec = fmod($n, 1) > 0.004 ? 2 : 0;
    $s = number_format($n, $dec, '.', ''); [$i, $f] = array_pad(explode('.', $s), 2, null);
    if (strlen($i) > 3) { $last = substr($i, -3); $rest = substr($i, 0, -3); $i = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last; }
    return ($neg ? '-' : '') . cfg('currency') . $i . ($f ? '.' . $f : '');
}
function icon(string $n, string $cls = 'ico'): string { static $I = null; $I ??= require __DIR__ . '/icons.php'; return '<svg class="' . e($cls) . '" viewBox="0 0 24 24" aria-hidden="true">' . ($I[$n] ?? '') . '</svg>'; }

/* ---- products ---- */
function p_sale(array $p): bool { return $p['sale_price'] !== null && (float)$p['sale_price'] > 0 && (float)$p['sale_price'] < (float)$p['price']; }
function p_price(array $p): float { return p_sale($p) ? (float)$p['sale_price'] : (float)$p['price']; }
function p_off(array $p): int { return p_sale($p) ? (int)round((1 - $p['sale_price'] / $p['price']) * 100) : 0; }
function p_img(array $p): string {
    $f = ROOT_PATH . '/' . ltrim((string)$p['image'], '/');
    if ($p['image'] !== '' && is_file($f)) return url($p['image']) . '?v=' . filemtime($f);
    return url('placeholder.php') . '?n=' . rawurlencode($p['name']) . '&s=' . rawurlencode($p['subcategory']);
}
function stars(float $r): string {
    $h = ''; for ($i = 1; $i <= 5; $i++) $h .= '<svg class="star ' . ($r >= $i - .75 ? 'f' : 'e') . '" viewBox="0 0 24 24">' . (require __DIR__ . '/icons.php')['star'] . '</svg>';
    return '<div class="stars" title="' . $r . ' out of 5"><span class="row">' . $h . '</span><span>' . number_format($r, 1) . '</span></div>';
}
function product(string $id): ?array { return q('SELECT * FROM products WHERE id=? AND is_active=1', [$id])->fetch() ?: null; }
function wish_ids(): array {
    static $w = null; if ($w !== null) return $w; $u = current_user();
    return $w = $u ? array_column(q('SELECT product_id FROM wishlist WHERE user_id=?', [$u['id']])->fetchAll(), 'product_id') : [];
}
function product_card(array $p): string {
    $sale = p_sale($p); $on = in_array($p['id'], wish_ids(), true); $tags = '';
    if ($sale) $tags .= '<span class="tag tag--sale">Sale ' . p_off($p) . '% off</span>';
    if ($p['badge']) $tags .= '<span class="tag tag--gold">' . e($p['badge']) . '</span>';
    $href = url('product.php?id=' . urlencode($p['id'])); $img = p_img($p);
    return '<article class="pcard"><a class="thumb" href="' . $href . '" aria-label="View ' . e($p['name']) . '"><img src="' . e($img) . '" alt="' . e($p['name']) . '" loading="lazy" onerror="rtImgFail(this)" data-n="' . e($p['name']) . '" data-s="' . e($p['subcategory']) . '"><span class="tags">' . $tags . '</span></a>'
      . '<button class="wish ' . ($on ? 'on' : '') . '" data-wish="' . e($p['id']) . '" aria-label="Toggle wishlist" aria-pressed="' . ($on ? 'true' : 'false') . '">' . icon('heart') . '</button>'
      . '<div class="pinfo"><span class="pcat">' . e($p['category']) . ' · ' . e($p['subcategory']) . '</span><a class="pname" href="' . $href . '">' . e($p['name']) . '</a>' . stars((float)$p['rating'])
      . '<div class="price-row"><span class="price">' . money(p_price($p)) . '</span>' . ($sale ? '<span class="price-old">' . money($p['price']) . '</span>' : '') . '</div>'
      . '<div class="pactions"><button class="btn btn--outline btn--sm" data-add="' . e($p['id']) . '">Add to Cart</button><button class="btn btn--dark btn--sm" data-buy="' . e($p['id']) . '">Buy Now</button></div></div></article>';
}

/* ---- cart: session for guests, mirrored to cart_items for logged-in users ---- */
function cart_get(): array { return $_SESSION['cart'] ?? []; }
function cart_persist(array $c): void {
    $_SESSION['cart'] = $c; $u = current_user(); if (!$u) return;
    q('DELETE FROM cart_items WHERE user_id=?', [$u['id']]);
    foreach ($c as $pid => $qty) q('INSERT INTO cart_items (user_id,product_id,qty) VALUES (?,?,?)', [$u['id'], $pid, $qty]);
}
function cart_add(string $id, int $qty = 1): bool { if (!product($id)) return false; $c = cart_get(); $c[$id] = min(20, ($c[$id] ?? 0) + max(1, $qty)); cart_persist($c); return true; }
function cart_set(string $id, int $qty): void { $c = cart_get(); if (!isset($c[$id])) return; if ($qty <= 0) unset($c[$id]); else $c[$id] = min(20, $qty); cart_persist($c); }
function cart_merge_on_login(int $uid): void {
    $c = cart_get(); foreach (q('SELECT product_id,qty FROM cart_items WHERE user_id=?', [$uid])->fetchAll() as $r) $c[$r['product_id']] = min(20, ($c[$r['product_id']] ?? 0) + (int)$r['qty']);
    $_SESSION['cart'] = $c; cart_persist($c);
}
function cart_lines(): array {
    $c = cart_get(); if (!$c) return []; $in = implode(',', array_fill(0, count($c), '?'));
    $rows = q("SELECT * FROM products WHERE is_active=1 AND id IN ($in)", array_keys($c))->fetchAll(); $out = [];
    foreach ($rows as $p) $out[] = ['p' => $p, 'qty' => $c[$p['id']], 'unit' => p_price($p), 'line' => p_price($p) * $c[$p['id']]];
    return $out;
}
function cart_count(): int { return (int)array_sum(cart_get()); }
function cart_totals(?array $lines = null): array {
    $lines ??= cart_lines(); $sub = array_sum(array_column($lines, 'line')); $ship = $lines ? (float)cfg('shipping_fee') : 0.0;
    $gst = round($sub * (float)cfg('gst_rate'), 2); return ['subtotal' => (float)$sub, 'shipping' => $ship, 'gst' => $gst, 'total' => round($sub + $ship + $gst, 2)];
}

/* ---- orders ---- */
const ORDER_STEPS = ['Order Placed', 'Confirmed', 'Packed', 'Shipped', 'Out for Delivery', 'Delivered'];
function order_step(string $status): int { if ($status === 'Cancelled') return -1; $i = array_search($status, ORDER_STEPS, true); return $i === false ? 0 : (int)$i; }
function order_can_view(array $o, ?string $email = null): bool {
    $u = current_user(); if ($u && ($u['role'] === 'admin' || (int)$u['id'] === (int)$o['user_id'])) return true;
    return $email !== null && strcasecmp(trim($email), $o['customer_email']) === 0;
}
function order_load(string $orderNo): ?array {
    $o = q('SELECT * FROM orders WHERE order_no=?', [$orderNo])->fetch(); if (!$o) return null;
    $o['items'] = q('SELECT * FROM order_items WHERE order_id=?', [$o['id']])->fetchAll();
    $o['history'] = q('SELECT * FROM order_history WHERE order_id=? ORDER BY id', [$o['id']])->fetchAll(); return $o;
}
function order_set_status(int $id, string $status, string $tracking, string $note = ''): void {
    $o = q('SELECT status FROM orders WHERE id=?', [$id])->fetch(); if (!$o) return;
    q('UPDATE orders SET status=?, tracking=? WHERE id=?', [$status, $tracking, $id]);
    if ($o['status'] !== $status) q('INSERT INTO order_history (order_id,status,note) VALUES (?,?,?)', [$id, $status, mb_substr($note, 0, 250)]);
}
function json_out($d, int $code = 200): never { http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($d, JSON_UNESCAPED_UNICODE); exit; }
