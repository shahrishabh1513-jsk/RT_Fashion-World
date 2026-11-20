<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'shop'; $CATS = ['Men', 'Women', 'Accessories', 'Bags', 'Jewelry']; $COLS = ['men' => "Men's Collection", 'women' => "Women's Collection", 'accessories' => 'Accessories Collection'];
$cat = in_array($_GET['cat'] ?? '', $CATS, true) ? $_GET['cat'] : ''; $col = isset($COLS[$_GET['col'] ?? '']) ? $_GET['col'] : '';
$sub = trim((string)($_GET['sub'] ?? '')); $qs = trim((string)($_GET['q'] ?? '')); $sort = in_array($_GET['sort'] ?? '', ['plh', 'phl', 'rating'], true) ? $_GET['sort'] : 'featured';
$where = ['is_active=1']; $par = [];
if ($cat) { $where[] = 'category=?'; $par[] = $cat; } if ($sub) { $where[] = 'subcategory=?'; $par[] = $sub; } if ($col) { $where[] = 'collection=?'; $par[] = $col; }
if ($qs !== '') { $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $qs) . '%'; $where[] = '(name LIKE ? OR category LIKE ? OR subcategory LIKE ? OR collection LIKE ?)'; array_push($par, $like, $like, $like, $like); }
$eff = 'IF(sale_price IS NOT NULL AND sale_price>0 AND sale_price<price, sale_price, price)';
$order = ['plh' => "$eff ASC, rating DESC", 'phl' => "$eff DESC, rating DESC", 'rating' => 'rating DESC, sort_order', 'featured' => 'sort_order'][$sort];
$products = q('SELECT * FROM products WHERE ' . implode(' AND ', $where) . " ORDER BY $order", $par)->fetchAll();
$all = (int)q('SELECT COUNT(*) c FROM products WHERE is_active=1')->fetch()['c'];
$counts = array_column(q('SELECT category, COUNT(*) c FROM products WHERE is_active=1 GROUP BY category')->fetchAll(), 'c', 'category');
$subs = $cat ? array_column(q('SELECT DISTINCT subcategory FROM products WHERE is_active=1 AND category=? ORDER BY subcategory', [$cat])->fetchAll(), 'subcategory') : [];
$h1 = $sub ?: ($cat ?: ($col ? $COLS[$col] : 'Shop All'));
$title = $h1 . ' | RT Fashion Hub'; $desc = 'Browse and filter all RT Fashion Hub products: men’s and women’s clothing, accessories, jewelry and bags. Sort by price or rating.';
$link = fn(array $o) => url('shop.php') . '?' . http_build_query(array_filter(array_merge(['cat' => $cat, 'sub' => $sub, 'col' => $col, 'q' => $qs, 'sort' => $sort === 'featured' ? '' : $sort], $o), fn($v) => $v !== '' && $v !== null));
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="crumbs">Home / Shop</div><h1><?= e($h1) ?></h1><p>Explore every piece in the RT Fashion Hub collection — search, filter and sort to find your look.</p></section>
<main class="section--tight"><div class="container"><div class="shop-layout">
  <aside class="filters" id="filters" aria-label="Filters"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px"><strong style="font-family:var(--font-head);font-size:1.2rem">Filters</strong><button class="icon-btn filter-toggle" id="filterClose" aria-label="Close filters"><?= icon('x') ?></button></div>
    <div class="grp"><h4>Category</h4><div class="chip-list">
      <a class="chip <?= !$cat ? 'active' : '' ?>" href="<?= $link(['cat' => '', 'sub' => '']) ?>">All Products <small><?= $all ?></small></a>
      <?php foreach ($CATS as $c): ?>
        <a class="chip <?= $cat === $c && !$sub ? 'active' : '' ?>" href="<?= $link(['cat' => $c, 'sub' => '']) ?>"><?= $c ?> <small><?= (int)($counts[$c] ?? 0) ?></small></a>
        <?php if ($cat === $c && $subs): ?><div class="subchips"><?php foreach ($subs as $s): ?><a class="chip <?= $sub === $s ? 'active' : '' ?>" href="<?= $link(['cat' => $c, 'sub' => $s]) ?>"><?= e($s) ?></a><?php endforeach; ?></div><?php endif; ?>
      <?php endforeach; ?></div></div>
    <div class="grp"><h4>Collection</h4><div class="chip-list"><a class="chip <?= !$col ? 'active' : '' ?>" href="<?= $link(['col' => '']) ?>">All</a>
      <?php foreach ($COLS as $k => $n): ?><a class="chip <?= $col === $k ? 'active' : '' ?>" href="<?= $link(['col' => $k]) ?>"><?= $n ?></a><?php endforeach; ?></div></div>
    <a class="btn btn--outline btn--sm btn--block" href="<?= url('shop.php') ?>">Clear all filters</a></aside>
  <section>
    <form class="shop-bar" method="get" action="<?= url('shop.php') ?>"><button type="button" class="btn btn--outline btn--sm filter-toggle" id="filterOpen"><?= icon('filter') ?> Filters</button>
      <?php foreach (['cat' => $cat, 'sub' => $sub, 'col' => $col] as $k => $v) if ($v) echo '<input type="hidden" name="' . $k . '" value="' . e($v) . '">'; ?>
      <label class="shop-search"><?= icon('search') ?><span class="sr-only">Search products</span><input name="q" value="<?= e($qs) ?>" type="search" placeholder="Search products by name or category"></label>
      <label class="sr-only" for="sort">Sort</label><select class="sort" id="sort" name="sort"><?php foreach (['featured' => 'Sort: Featured', 'plh' => 'Price: Low to High', 'phl' => 'Price: High to Low', 'rating' => 'Customer Rating'] as $k => $n): ?><option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?></select></form>
    <p class="count" role="status">Showing <?= count($products) ?> of <?= $all ?> products</p>
    <div class="product-grid">
      <?php if ($products): foreach ($products as $p) echo product_card($p); else: ?>
        <div class="empty" style="grid-column:1/-1"><?= icon('search') ?><h3><?= $qs !== '' ? 'No products match your search' : 'No products here yet' ?></h3><p><?= $qs !== '' ? 'Nothing found for “' . e($qs) . '”. Try a different word or clear the filters.' : 'New styles are coming soon.' ?></p><a class="btn btn--dark" href="<?= url('shop.php') ?>">Clear filters</a></div>
      <?php endif; ?></div>
  </section></div></div></main>
<div class="overlay" id="filterOverlay"></div>
<?php require __DIR__ . '/includes/footer.php';
