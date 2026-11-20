<?php
$_social = cfg('social'); $_soc = '';
foreach (['instagram' => 'Instagram', 'facebook' => 'Facebook', 'whatsapp' => 'WhatsApp'] as $_k => $_n) if (!empty($_social[$_k])) $_soc .= '<a href="' . e($_social[$_k]) . '" target="_blank" rel="noopener" aria-label="' . $_n . '">' . icon($_k) . '</a>';
$extraScripts ??= [];
?>
<footer class="site-footer"><div class="container"><div class="footer-grid">
  <div><img class="flogo" src="<?= url('images/brand/logo-white.png') ?>" alt="RT Fashion Hub"><p>Discover your style with RT Fashion Hub. Explore modern fashion, lifestyle essentials, and collections designed for every occasion.</p><div class="socials"><?= $_soc ?><a href="mailto:<?= e(cfg('email')) ?>" aria-label="Email us"><?= icon('mail') ?></a></div></div>
  <div><h4>Quick Access</h4><ul><li><a href="<?= url('index.php') ?>">Home</a></li><li><a href="<?= url('shop.php') ?>">Shop</a></li><li><a href="<?= url('about.php') ?>">About Us</a></li><li><a href="<?= url('contact.php') ?>">Contact Us</a></li></ul></div>
  <div><h4>Customer Services</h4><ul><li><a href="<?= url('account.php') ?>">My Account</a></li><li><a href="<?= url('account.php?tab=orders') ?>">My Orders</a></li><li><a href="<?= url('track.php') ?>">Track Order</a></li><li><a href="<?= url('cart.php') ?>">Shopping Cart</a></li><li><a href="<?= url('account.php?tab=wishlist') ?>">Wishlist</a></li><li><a href="<?= url('contact.php#faq') ?>">FAQ</a></li></ul></div>
  <div><h4>Contact Information</h4><ul>
    <li class="contact-li"><?= icon('pin') ?><span><?= e(cfg('location')) ?></span></li>
    <li class="contact-li"><?= icon('mail') ?><a href="mailto:<?= e(cfg('email')) ?>"><?= e(cfg('email')) ?></a></li>
    <li class="contact-li"><?= icon('phone') ?><a href="tel:<?= e(cfg('phone_href')) ?>"><?= e(cfg('phone')) ?></a></li>
    <li class="contact-li"><?= icon('clock') ?><span>Monday to Saturday:<br>10:00 AM – 6:00 PM</span></li></ul></div>
</div>
<div class="footer-bottom"><span>© <?= date('Y') ?> RT FASHION HUB. All Rights Reserved.</span>
  <div class="fb-right"><span>Made with <span class="heart">♥</span> by RT1504</span><?php if (cfg('github_url')): ?><a class="btn btn--light btn--sm" href="<?= e(cfg('github_url')) ?>" target="_blank" rel="noopener"><?= icon('github') ?> GitHub Project</a><?php endif; ?></div></div></div></footer>
<button class="to-top" id="toTop" aria-label="Back to top"><?= icon('up') ?></button>
<script>window.RT = <?= json_encode(['base' => BASE_URL, 'csrf' => csrf_token(), 'loggedIn' => (bool)current_user(), 'currency' => cfg('currency'), 'flash' => flashes()], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<?php foreach ($extraScripts as $_sc): ?><script src="<?= url($_sc) ?>"></script>
<?php endforeach; ?><script src="<?= url('js/app.js') ?>?v=<?= filemtime(ROOT_PATH . '/js/app.js') ?>"></script>
</body>
</html>
