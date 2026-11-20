<?php
$title = 'Edit product | Admin'; $heading = 'Product'; require_once __DIR__ . '/../includes/bootstrap.php'; require_admin();
$id = (string)($_GET['id'] ?? $_POST['orig'] ?? ''); $p = $id !== '' ? q('SELECT * FROM products WHERE id=?', [$id])->fetch() : null; if ($id !== '' && !$p) { flash('Product not found.', 'err'); redirect('admin/products.php'); }
$CATS = ['Men' => 'men', 'Women' => 'women', 'Accessories' => 'accessories', 'Bags' => 'accessories', 'Jewelry' => 'accessories']; $err = [];
if (is_post()) { require_csrf();
    $d = ['name' => trim((string)$_POST['name']), 'category' => (string)$_POST['category'], 'collection' => (string)$_POST['collection'], 'subcategory' => trim((string)$_POST['subcategory']), 'rating' => (float)$_POST['rating'], 'price' => (float)$_POST['price'],
          'sale' => trim((string)$_POST['sale_price']) === '' ? null : (float)$_POST['sale_price'], 'badge' => trim((string)$_POST['badge']) ?: null, 'image' => trim((string)$_POST['image']), 'link' => trim((string)$_POST['link']), 'description' => trim((string)$_POST['description']) ?: null, 'active' => empty($_POST['is_active']) ? 0 : 1];
    if ($d['name'] === '') $err[] = 'Name is required.'; if (!isset($CATS[$d['category']])) $err[] = 'Choose a category.'; if (!in_array($d['collection'], ['men', 'women', 'accessories'], true)) $err[] = 'Choose a collection.';
    if ($d['subcategory'] === '') $err[] = 'Sub-category is required.'; if ($d['price'] <= 0) $err[] = 'Price must be above 0.'; if ($d['sale'] !== null && ($d['sale'] <= 0 || $d['sale'] >= $d['price'])) $err[] = 'Sale price must be lower than price.'; if ($d['rating'] < 0 || $d['rating'] > 5) $err[] = 'Rating must be 0–5.';
    if (!$err) {
        if ($p) q('UPDATE products SET name=?,category=?,collection=?,subcategory=?,rating=?,price=?,sale_price=?,badge=?,image=?,link=?,description=?,is_active=? WHERE id=?', [$d['name'], $d['category'], $d['collection'], $d['subcategory'], $d['rating'], $d['price'], $d['sale'], $d['badge'], $d['image'], $d['link'], $d['description'], $d['active'], $p['id']]);
        else { $n = (int)q("SELECT COALESCE(MAX(CAST(SUBSTRING(id,4) AS UNSIGNED)),0) m FROM products")->fetch()['m'] + 1; $nid = sprintf('rt-%03d', $n); $so = (int)q('SELECT COALESCE(MAX(sort_order),0)+1 m FROM products')->fetch()['m'];
            $folder = ['Men' => 'man', 'Women' => 'woman'][$d['category']] ?? $d['category']; if ($d['image'] === '') $d['image'] = "images/$folder/$nid.webp";
            q('INSERT INTO products (id,name,category,collection,subcategory,rating,price,sale_price,badge,image,link,description,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$nid, $d['name'], $d['category'], $d['collection'], $d['subcategory'], $d['rating'], $d['price'], $d['sale'], $d['badge'], $d['image'], $d['link'], $d['description'], $d['active'], $so]); $p = ['id' => $nid]; }
        flash('Product saved (' . $p['id'] . ').', 'ok'); redirect('admin/products.php'); }
    $p = array_merge($p ?: ['id' => ''], ['name' => $d['name'], 'category' => $d['category'], 'collection' => $d['collection'], 'subcategory' => $d['subcategory'], 'rating' => $d['rating'], 'price' => $d['price'], 'sale_price' => $d['sale'], 'badge' => $d['badge'], 'image' => $d['image'], 'link' => $d['link'], 'description' => $d['description'], 'is_active' => $d['active']]); }
$v = fn($k, $dflt = '') => e($p[$k] ?? $dflt); $heading = $p && !empty($p['id']) ? 'Edit ' . $p['id'] : 'Add product'; require __DIR__ . '/_head.php';
?>
<?php if ($err): ?><div class="alert alert--err"><?= e(implode(' ', $err)) ?></div><?php endif; ?>
<form class="form-card" method="post"><?= csrf_field() ?><input type="hidden" name="orig" value="<?= $v('id') ?>"><div class="form-grid">
  <div class="field full"><label>Product name *</label><input name="name" value="<?= $v('name') ?>" required></div>
  <div class="field"><label>Category *</label><select name="category"><?php foreach ($CATS as $c => $_): ?><option <?= ($p['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></div>
  <div class="field"><label>Collection *</label><select name="collection"><?php foreach (['men', 'women', 'accessories'] as $c): ?><option <?= ($p['collection'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option><?php endforeach; ?></select></div>
  <div class="field"><label>Sub-category *</label><input name="subcategory" value="<?= $v('subcategory') ?>" required></div><div class="field"><label>Rating (0–5)</label><input name="rating" type="number" step="0.1" min="0" max="5" value="<?= $v('rating', '4.0') ?>"></div>
  <div class="field"><label>Price (₹) *</label><input name="price" type="number" step="0.01" min="1" value="<?= $v('price', '999') ?>" required></div><div class="field"><label>Sale price (₹) – blank = no sale</label><input name="sale_price" type="number" step="0.01" min="0" value="<?= $v('sale_price') ?>"></div>
  <div class="field"><label>Badge (e.g. Trending, New Arrival)</label><input name="badge" value="<?= $v('badge') ?>"></div>
  <div class="field"><label>Image path (blank = images/&lt;folder&gt;/&lt;id&gt;.webp)</label><input name="image" value="<?= $v('image') ?>" placeholder="images/man/rt-001.webp"></div>
  <div class="field full"><label>Original link (optional)</label><input name="link" value="<?= $v('link') ?>"></div>
  <div class="field full"><label>Description (optional)</label><textarea name="description" rows="4"><?= $v('description') ?></textarea></div>
  <div class="full"><label class="check"><input type="checkbox" name="is_active" value="1" <?= ($p['is_active'] ?? 1) ? 'checked' : '' ?>> Visible in the store</label></div>
  <div class="full" style="display:flex;gap:10px"><button class="btn btn--dark">Save product</button><a class="btn btn--outline" href="<?= url('admin/products.php') ?>">Cancel</a></div></div></form>
<?php require __DIR__ . '/_foot.php';
