<?php
require __DIR__ . '/../includes/bootstrap.php';
if (!is_post() || !csrf_ok()) json_out(['ok' => false, 'error' => 'Bad request'], 400);
$u = current_user(); if (!$u) json_out(['ok' => false, 'login' => url('login.php?next=shop.php')], 401);
$id = (string)(json_decode(file_get_contents('php://input') ?: '[]', true)['id'] ?? ''); if (!product($id)) json_out(['ok' => false, 'error' => 'Product not found'], 404);
if (q('SELECT 1 FROM wishlist WHERE user_id=? AND product_id=?', [$u['id'], $id])->fetch()) { q('DELETE FROM wishlist WHERE user_id=? AND product_id=?', [$u['id'], $id]); $on = false; }
else { q('INSERT INTO wishlist (user_id,product_id) VALUES (?,?)', [$u['id'], $id]); $on = true; }
json_out(['ok' => true, 'on' => $on]);
