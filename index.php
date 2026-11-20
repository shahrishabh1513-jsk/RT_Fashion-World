<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'home'; $title = 'RT Fashion Hub – Style That Defines You | Fashion & Lifestyle Store';
$desc = 'Shop modern fashion for men and women, plus accessories, jewelry and bags, at RT Fashion Hub, Vadodara. Style That Defines You.';
$total = (int)q('SELECT COUNT(*) c FROM products WHERE is_active=1')->fetch()['c'];
$avg = (float)q('SELECT AVG(rating) a FROM products WHERE is_active=1')->fetch()['a'];
$trending = q("SELECT * FROM products WHERE is_active=1 ORDER BY (badge='Trending') DESC, rating DESC, sort_order LIMIT 8")->fetchAll();
$top = fn(string $col) => q('SELECT * FROM products WHERE is_active=1 AND collection=? ORDER BY rating DESC, sort_order LIMIT 3', [$col])->fetchAll();
$benefits = [['globe', 'Shipping Worldwide', 'Fast and reliable delivery.'], ['refresh', '14 Days Return', 'Easy return and exchange policy.'], ['shield', 'Secure Payment', 'Safe and secure checkout experience.'], ['headset', '24/7 Support', 'Customer assistance whenever needed.']];
$cats = [['Accessories', 'images/Accessories/rt-042.webp'], ['Bags', 'images/Bags/rt-045.webp'], ['Jewelry', 'images/Jewelry/rt-049.webp'], ['Women', 'images/woman/women_home.jpg'], ['Men', 'images/man/men_home.png']];
$cols = [['Men\'s Collection', 'Tees, shirts, denim and more for him.', 'men'], ['Women\'s Collection', 'Dresses, skirts, tops and denim for her.', 'women'], ['Accessories Collection', 'Watches, jewelry, bags and everyday accessories.', 'accessories']];
require __DIR__ . '/includes/header.php';
?>
<main>
<section class="hero"><div class="container hero-grid">
  <div><span class="eyebrow">New Season · Fashion &amp; Lifestyle</span>
    <h1>Style That <em>Defines</em> You.</h1>
    <p>Discover your style with RT Fashion Hub. Modern fashion and lifestyle essentials, curated for every occasion and every personality.</p>
    <div class="hero-cta"><a class="btn btn--dark" href="<?= url('shop.php') ?>">Shop Now</a><a class="btn btn--outline" href="#collections">Explore Collection</a></div>
    <div class="hero-stats"><div><b><?= $total ?></b><span>Styles</span></div><div><b>5</b><span>Categories</span></div><div><b><?= number_format($avg, 1) ?></b><span>Avg. Rating</span></div></div></div>
  <div class="hero-media"><div class="frame"><img src="<?= url('images/background/banner1.png') ?>" alt="Model wearing a relaxed white top and bucket hat" fetchpriority="high"></div>
    <div class="hero-tag"><?= icon('star') ?><div><b>Top rated picks</b><small>Customer favourites, 4.5★ &amp; up</small></div></div></div>
</div></section>

<section class="section--tight"><div class="container"><div class="benefits">
  <?php foreach ($benefits as [$i, $t, $d]): ?><div class="benefit reveal"><div class="ico-wrap"><?= icon($i) ?></div><h3><?= $t ?></h3><p><?= $d ?></p></div><?php endforeach; ?>
</div></div></section>

<section class="section"><div class="container"><div class="section-head reveal"><span class="eyebrow">Browse</span><h2>Shop by Categories</h2><p>Find exactly what you're looking for, from everyday essentials to statement pieces.</p></div>
  <div class="cat-grid"><?php foreach ($cats as [$n, $img]): ?>
    <a class="cat-card reveal" href="<?= url('shop.php?cat=' . $n) ?>"><img src="<?= url($img) ?>" alt="<?= $n ?> collection" loading="lazy"><div class="cap"><h3><?= $n ?></h3><span class="go">Shop Now</span></div></a>
  <?php endforeach; ?></div></div></section>

<section class="section" style="padding-top:20px"><div class="container"><div class="section-head reveal"><span class="eyebrow">Handpicked</span><h2>Trending Products</h2><p>The pieces our customers rate highest right now.</p></div>
  <div class="product-grid"><?php foreach ($trending as $p) echo product_card($p); ?></div>
  <div style="text-align:center;margin-top:40px"><a class="btn btn--dark" href="<?= url('shop.php') ?>">View All Products</a></div></div></section>

<section class="section" id="collections" style="padding-top:20px"><div class="container"><div class="section-head reveal"><span class="eyebrow">Collections</span><h2>Featured Collections</h2></div>
  <?php foreach ($cols as [$t, $d, $k]): ?>
  <div class="collection reveal"><div class="collection-intro"><span class="eyebrow" style="color:var(--accent)">Collection</span><h3><?= e($t) ?></h3><p><?= e($d) ?></p><a class="btn btn--gold btn--sm" href="<?= url('shop.php?col=' . $k) ?>">Shop Collection</a></div>
    <div class="product-grid"><?php foreach ($top($k) as $p) echo product_card($p); ?></div></div>
  <?php endforeach; ?></div></section>

<section><div class="promo reveal"><img src="<?= url('images/background/slide16_bdfdea89-9df3-48da-9f0a-888fd50a83d5.webp') ?>" alt="" loading="lazy"><div class="promo-inner"><span class="eyebrow" style="color:var(--accent)">The Style Edit</span><h2>Discover Your Style.<br><em>Define Your Look.</em></h2><p>From wardrobe staples to accessories that finish the outfit — find your signature look.</p><a class="btn btn--gold" href="<?= url('shop.php') ?>">Explore Now</a></div></div></section>

<section class="section"><div class="container"><div class="newsletter reveal"><span class="eyebrow">Stay in the loop</span><h2>Join the RT Fashion Hub Newsletter</h2><p style="color:var(--muted);margin-top:10px">New arrivals and style notes, straight to your inbox.</p>
  <form class="news-form" method="post" action="<?= url('newsletter.php') ?>"><?= csrf_field() ?><label class="sr-only" for="newsEmail">Email address</label><input id="newsEmail" name="email" type="email" placeholder="Enter your email address" autocomplete="email" required><button class="btn btn--dark" type="submit">Subscribe</button></form></div></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php';
