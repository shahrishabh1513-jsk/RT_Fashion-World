<?php
require __DIR__ . '/../includes/bootstrap.php';
function payload(): array {
    $L = cart_lines(); $t = cart_totals($L);
    return ['ok' => true, 'count' => cart_count(), 'totals' => $t, 'lines' => array_map(fn($l) => ['id' => $l['p']['id'], 'name' => $l['p']['name'], 'qty' => $l['qty'], 'unit' => $l['unit'], 'unit_fmt' => money($l['unit']), 'line_fmt' => money($l['line']), 'img' => p_img($l['p']), 'url' => url('product.php?id=' . $l['p']['id'])], $L), 'subtotal_fmt' => money($t['subtotal'])];
}
if (!is_post()) json_out(payload());
if (!csrf_ok()) json_out(['ok' => false, 'error' => 'Session expired. Refresh the page.'], 419);
$in = json_decode(file_get_contents('php://input') ?: '[]', true) ?: []; $id = (string)($in['id'] ?? '');
switch ($in['action'] ?? '') {
    case 'add': if (!cart_add($id, (int)($in['qty'] ?? 1))) json_out(['ok' => false, 'error' => 'Product not found.'], 404); break;
    case 'set': cart_set($id, (int)($in['qty'] ?? 0)); break;
    case 'remove': cart_set($id, 0); break;
    default: json_out(['ok' => false, 'error' => 'Bad request'], 400);
}
json_out(payload());
