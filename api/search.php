<?php
require __DIR__ . '/../includes/bootstrap.php';
$t = trim((string)($_GET['q'] ?? '')); if (mb_strlen($t) < 2) json_out([]);
$like = '%' . str_replace(['%', '_'], ['\%', '\_'], $t) . '%';
$rows = q('SELECT * FROM products WHERE is_active=1 AND (name LIKE ? OR category LIKE ? OR subcategory LIKE ?) ORDER BY rating DESC LIMIT 6', [$like, $like, $like])->fetchAll();
json_out(array_map(fn($p) => ['name' => $p['name'], 'cat' => $p['category'] . ' · ' . $p['subcategory'], 'img' => p_img($p), 'url' => url('product.php?id=' . $p['id'])], $rows));
