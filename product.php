<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'shop'; $p = product((string)($_GET['id'] ?? ''));
if (!$p) { http_response_code(404); $title = 'Product not found | RT Fashion Hub'; require __DIR__ . '/includes/header.php'; ?>
<main class="section"><div class="container"><div class="empty"><?= icon('search') ?><h3>Product not found</h3><p>This product may have been removed.</p><a class="btn btn--dark" href="<?= url('shop.php') ?>">Back to Shop</a></div></div></main>
<?php require __DIR__ . '/includes/footer.php'; exit; }
$title = $p['name'] . ' | RT Fashion Hub'; $desc = $p['name'] . ' – ' . $p['category'] . ' / ' . $p['subcategory'] . ' at RT Fashion Hub. Rated ' . $p['rating'] . '/5.';
$sale = p_sale($p); $on = in_array($p['id'], wish_ids(), true);
$related = q('SELECT * FROM products WHERE is_active=1 AND id<>? AND (subcategory=? OR collection=?) ORDER BY (subcategory=?) DESC, rating DESC LIMIT 4', [$p['id'], $p['subcategory'], $p['collection'], $p['subcategory']])->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<main class="section--tight"><div class="container">
  <div class="crumbs" style="color:var(--muted);margin-bottom:24px"><a href="<?= url('index.php') ?>">Home</a> / <a href="<?= url('shop.php') ?>">Shop</a> / <a href="<?= url('shop.php?cat=' . $p['category']) ?>"><?= e($p['category']) ?></a> / <?= e($p['subcategory']) ?></div>
  <div class="pd">
    <div><div class="pd-main"><img src="<?= e(p_img($p)) ?>" alt="<?= e($p['name']) ?>" data-n="<?= e($p['name']) ?>" data-s="<?= e($p['subcategory']) ?>" onerror="rtImgFail(this)">
      <span class="tags"><?php if ($sale): ?><span class="tag tag--sale">Sale <?= p_off($p) ?>% off</span><?php endif; ?><?php if ($p['badge']): ?><span class="tag tag--gold"><?= e($p['badge']) ?></span><?php endif; ?></span></div></div>
    <div class="pd-info"><span class="pcat"><?= e($p['category']) ?> · <?= e($p['subcategory']) ?></span><h1><?= e($p['name']) ?></h1><?= stars((float)$p['rating']) ?>
      <div class="price-row" style="margin-top:14px"><span class="price"><?= money(p_price($p)) ?></span><?php if ($sale): ?><span class="price-old"><?= money($p['price']) ?></span><span class="off"><?= p_off($p) ?>% off</span><?php endif; ?></div>
      <p style="color:var(--muted);margin-top:16px"><?= e($p['description'] ?: 'A quality pick from the RT Fashion Hub ' . strtolower($p['subcategory']) . ' range.') ?></p>
      <dl class="pd-meta"><dt>Category</dt><dd><?= e($p['category']) ?></dd><dt>Type</dt><dd><?= e($p['subcategory']) ?></dd><dt>Collection</dt><dd style="text-transform:capitalize"><?= e($p['collection']) ?></dd><dt>Customer rating</dt><dd><?= $p['rating'] ?> / 5</dd><dt>Product code</dt><dd><?= strtoupper(e($p['id'])) ?></dd></dl>
      <div class="qty" id="pdQty"><button type="button" aria-label="Decrease">−</button><span id="pdQtyVal">1</span><button type="button" aria-label="Increase">+</button></div>
      <div class="pd-buy"><button class="btn btn--outline" data-add="<?= e($p['id']) ?>">Add to Cart</button><button class="btn btn--dark" data-buy="<?= e($p['id']) ?>">Buy Now</button><button class="wish <?= $on ? 'on' : '' ?>" style="position:static" data-wish="<?= e($p['id']) ?>" aria-label="Toggle wishlist"><?= icon('heart') ?></button></div>
      <?php if ($p['link']): ?><p class="note">Original listing: <a class="link" href="<?= e($p['link']) ?>" target="_blank" rel="noopener sponsored">View on Flipkart ↗</a></p><?php endif; ?></div>
  </div>
  <?php if ($related): ?><div style="margin-top:80px"><div class="section-head"><h2>You May Also Like</h2></div><div class="product-grid"><?php foreach ($related as $r) echo product_card($r); ?></div></div><?php endif; ?>
</div></main>
<?php require __DIR__ . '/includes/footer.php';
