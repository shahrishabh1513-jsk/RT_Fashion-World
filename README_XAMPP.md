# RT FASHION HUB – PHP + MySQL (XAMPP) version

Server-rendered PHP 8 site using PDO/MySQL. Database name: **`rt_fashion`**.

## Setup (5 steps)
1. Install/open **XAMPP** and start **Apache** and **MySQL** in the Control Panel.
2. Copy this folder to `C:\xampp\htdocs\RT_Fashion` (the folder name can be anything).
3. Open **http://localhost/phpmyadmin** → *Import* → choose `database/rt_fashion.sql` → *Go*.
   (It creates the `rt_fashion` database, all tables, 58 products and the admin account.)
4. Check `includes/config.php` – XAMPP defaults are already set (host `localhost`, user `root`, empty password, db `rt_fashion`).
5. Open **http://localhost/RT_Fashion/**

## Admin panel
Log in at `/login.php` with **admin@rtfashionhub.com / Admin@123**, then open *Admin Panel* from the profile menu
(`/admin/`). **Change this password straight away** (My Account → Account Settings).

| Admin page | What it does |
|---|---|
| Dashboard | order / customer / message counts, recent orders |
| Orders | change status (Order Placed → Delivered / Cancelled), add courier/AWB + note – customers see it **live** on the tracking page |
| Products | edit price, **sale price (SALE tag shows automatically)**, badge, visibility; *Add product* / *Edit* for everything else |
| Messages | contact-form messages saved in the database |
| Customers | registered users + newsletter subscribers |

## Product images
`images/<folder>/<product id>.webp` – folders `man`, `woman`, `Accessories`, `Bags`, `Jewelry` (e.g. `images/man/rt-001.webp`).
Missing files show a branded placeholder automatically.

## Database tables
`users`, `addresses`, `products`, `cart_items`, `wishlist`, `orders`, `order_items`, `order_history`,
`contact_messages`, `newsletter_subscribers`, `password_resets`.

## What is real vs. not yet connected
* **Real (PHP + MySQL):** registration/login (bcrypt `password_hash`), sessions, CSRF protection, prepared statements, cart (guest → saved to account on login), wishlist, orders, live order tracking, bills (PDF made in the browser), contact messages, newsletter, admin panel.
* **Not connected:** online payments (UPI/Card/NetBanking are disabled – needs Razorpay keys; only Cash on Delivery works), e-mail sending (XAMPP has no mail server – contact messages and reset links are stored/shown instead; with `debug` = true the password-reset link is displayed on screen), courier APIs (statuses are set by the admin).
* Prices are placeholders (₹999) until you edit them in Admin → Products.

## Before going live
Set `'debug' => false` in `includes/config.php`, change the admin password, use a MySQL user other than `root`, enable HTTPS, and add real e-mail sending for password resets.

## Folder map
```
index.php shop.php product.php cart.php checkout.php order-success.php
login.php logout.php forgot.php reset.php account.php track.php invoice.php
about.php contact.php newsletter.php placeholder.php
api/        cart.php wishlist.php search.php order_status.php   (JSON used by js/app.js)
admin/      index.php orders.php products.php product-edit.php messages.php customers.php
includes/   config.php bootstrap.php header.php footer.php icons.php team*.php
database/   rt_fashion.sql
css/ js/ images/
_static_version/  your previous HTML/JS version (kept for reference, not used)
```
