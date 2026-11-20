<?php
$title = 'Products | Admin'; $heading = 'Products'; require_once __DIR__ . '/../includes/bootstrap.php'; require_admin();
if (is_post()) { require_csrf(); $id = (string)($_POST['id'] ?? '');
    if (($_POST['action'] ?? '') === 'quick') {
        $price = (float)($_POST['price'] ?? 0); $sale = trim((string)($_POST['sale_price'] ?? '')); $sale = $sale === '' ? null : (float)$sale;
        if ($price <= 0) flash('Price must be greater than 0.', 'err'); elseif ($sale !== null && ($sale <= 0 || $sale >= $price)) flash('Sale price must be lower than the regular price (leave blank for no sale).', 'err');
        else { q('UPDATE products SET price=?, sale_price=?, badge=?, is_active=? WHERE id=?', [$price, $sale, trim((string)($_POST['badge'] ?? '')) ?: null, empty($_POST['is_active']) ? 0 : 1, $id]); flash("$id updated.", 'ok'); }
    }
    redirect('admin/products.php?' . ($_POST['back'] ?? '')); }
$s = trim((string)($_GET['q'] ?? '')); $c = (string)($_GET['cat'] ?? ''); $w = ['1=1']; $p = [];
if ($s !== '') { $w[] = '(name LIKE ? OR id LIKE ?)'; array_push($p, "%$s%", "%$s%"); } if ($c !== '') { $w[] = 'category=?'; $p[] = $c; }
$rows = q('SELECT * FROM products WHERE ' . implode(' AND ', $w) . ' ORDER BY sort_order', $p)->fetchAll(); require __DIR__ . '/_head.php'; $back = http_build_query(array_filter(['q' => $s, 'cat' => $c]));
?>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;align-items:center"><form method="get" style="display:flex;gap:10px;flex:1;flex-wrap:wrap"><input name="q" value="<?= e($s) ?>" placeholder="Search name or ID" style="flex:1;min-width:200px;padding:11px 16px;border:1px solid var(--line);border-radius:999px">
  <select name="cat" class="sort"><option value="">All categories</option><?php foreach (['Men', 'Women', 'Accessories', 'Bags', 'Jewelry'] as $x): ?><option <?= $c === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select><button class="btn btn--dark btn--sm">Filter</button></form><a class="btn btn--gold btn--sm" href="<?= url('admin/product-edit.php') ?>">+ Add product</a></div>
<p class="note" style="margin:0 0 12px">Set a <b>Sale price</b> lower than the regular price to show the SALE tag automatically. Leave it blank to end the sale.</p>
<div class="tbl-wrap"><table class="tbl" style="min-width:980px"><thead><tr><th>Image</th><th>ID / Name</th><th>Price (₹)</th><th>Sale (₹)</th><th>Badge</th><th>Live</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): $f = 'f-' . $r['id']; ?><tr>
  <td><img src="<?= e(p_img($r)) ?>" alt="" style="width:46px;height:58px;object-fit:cover;border-radius:6px;background:var(--warm)"></td>
  <td><b><?= e($r['id']) ?></b><br><span style="font-size:.82rem"><?= e($r['name']) ?></span></td>
  <td><input form="<?= $f ?>" name="price" type="number" step="0.01" min="1" value="<?= e($r['price']) ?>" style="width:110px"></td><td><input form="<?= $f ?>" name="sale_price" type="number" step="0.01" min="0" value="<?= e($r['sale_price']) ?>" placeholder="no sale" style="width:110px"></td>
  <td><input form="<?= $f ?>" name="badge" value="<?= e($r['badge']) ?>" placeholder="Trending" style="width:120px"></td><td><input form="<?= $f ?>" type="checkbox" name="is_active" value="1" <?= $r['is_active'] ? 'checked' : '' ?> style="width:auto"></td>
  <td style="white-space:nowrap"><form id="<?= $f ?>" method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="quick"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><input type="hidden" name="back" value="<?= e($back) ?>"><button class="btn btn--dark btn--sm">Save</button></form> <a class="btn btn--outline btn--sm" href="<?= url('admin/product-edit.php?id=' . $r['id']) ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php require __DIR__ . '/_foot.php';
