<?php
/* Shared <head> + site header. Set before including: $page, $title, $desc */
require_once __DIR__ . '/bootstrap.php';
$hu = current_user(); $page ??= ''; $title ??= 'RT Fashion Hub'; $desc ??= cfg('tagline');
$nav = [['index.php', 'Home', 'home'], ['shop.php', 'Shop', 'shop'], ['about.php', 'About', 'about'], ['contact.php', 'Contact', 'contact']];
$searchForm = function (string $cls) { $q = e($_GET['q'] ?? ''); return '<form class="search-form ' . $cls . '" action="' . url('shop.php') . '" method="get" role="search" autocomplete="off"><input type="search" name="q" value="' . $q . '" placeholder="Search products or category" aria-label="Search products"><button class="icon-btn" type="submit" aria-label="Search">' . icon('search') . '</button><div class="search-suggest" role="listbox"></div></form>'; };
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<meta property="og:title" content="<?= e($title) ?>"><meta property="og:description" content="<?= e($desc) ?>"><meta property="og:image" content="<?= url('images/brand/logo.png') ?>">
<link rel="icon" type="image/png" href="<?= url('images/brand/favicon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('css/styles.css') ?>?v=<?= filemtime(ROOT_PATH . '/css/styles.css') ?>">
</head>
<body data-page="<?= e($page) ?>">
<header class="site-header"><div class="container header-inner">
  <button class="icon-btn menu-toggle" id="menuBtn" aria-label="Open menu" aria-expanded="false"><?= icon('menu') ?></button>
  <a class="logo" href="<?= url('index.php') ?>" aria-label="RT Fashion Hub – home"><img src="<?= url('images/brand/logo.png') ?>" alt="RT Fashion Hub" width="108" height="46"></a>
  <nav class="main-nav" id="mainNav" aria-label="Main">
    <?php foreach ($nav as [$_h, $_t, $_k]): ?><a href="<?= url($_h) ?>" class="<?= $page === $_k ? 'active' : '' ?>" <?= $page === $_k ? 'aria-current="page"' : '' ?>><?= $_t ?></a><?php endforeach; ?>
  </nav>
  <div class="header-actions">
    <?= $searchForm('desktop') ?>
    <?php if ($hu): ?>
      <div class="user-menu"><button class="icon-btn" aria-label="My profile" aria-haspopup="true" id="userBtn"><?= icon('user') ?></button>
        <div class="user-dropdown"><div class="who">Signed in as<br><b><?= e($hu['name']) ?></b></div>
          <a href="<?= url('account.php') ?>">My Account</a><a href="<?= url('account.php?tab=orders') ?>">My Orders</a><a href="<?= url('account.php?tab=wishlist') ?>">Wishlist</a>
          <?php if ($hu['role'] === 'admin'): ?><a href="<?= url('admin/index.php') ?>">Admin Panel</a><?php endif; ?>
          <form method="post" action="<?= url('logout.php') ?>"><?= csrf_field() ?><button type="submit">Logout</button></form></div></div>
    <?php else: ?>
      <a class="icon-btn menu-toggle" href="<?= url('login.php') ?>" aria-label="Login or register"><?= icon('user') ?></a>
      <a class="btn btn--dark btn--sm login-btn" href="<?= url('login.php') ?>">Login / Register</a>
    <?php endif; ?>
    <button class="icon-btn" id="cartBtn" aria-label="Open cart"><?= icon('bag') ?><span class="badge-count" id="cartCount"><?= cart_count() ?></span></button>
  </div></div><div class="mobile-search"><?= $searchForm('') ?></div></header>
<div class="overlay" id="overlay"></div>
<aside class="drawer" id="drawer" aria-label="Shopping cart" aria-hidden="true"><div class="drawer-head"><h3>Your Cart</h3><button class="icon-btn" id="drawerClose" aria-label="Close cart"><?= icon('x') ?></button></div><div class="drawer-body" id="drawerBody"></div><div class="drawer-foot" id="drawerFoot"></div></aside>
