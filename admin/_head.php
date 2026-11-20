<?php
/* Admin layout – include at the top of every admin page. Only users with role=admin get in. */
require_once __DIR__ . '/../includes/bootstrap.php';
require_admin();
$page = 'admin'; $title ??= 'Admin | RT Fashion Hub'; $desc = 'RT Fashion Hub admin panel.';
$A = ['index.php' => 'Dashboard', 'orders.php' => 'Orders', 'products.php' => 'Products', 'messages.php' => 'Messages', 'customers.php' => 'Customers'];
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero" style="padding:40px 24px"><div class="crumbs">Admin Panel</div><h1><?= e($heading ?? 'Admin') ?></h1></section>
<main class="section--tight"><div class="container">
<nav class="admin-nav"><?php foreach ($A as $f => $l): ?><a class="<?= basename($_SERVER['SCRIPT_NAME']) === $f ? 'on' : '' ?>" href="<?= url('admin/' . $f) ?>"><?= $l ?></a><?php endforeach; ?><a href="<?= url('index.php') ?>">View Store ↗</a></nav>
