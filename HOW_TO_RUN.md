# RT FASHION HUB – one folder, two ways to run

## A) Frontend only (client-side) – NO XAMPP needed
Double-click **`index.html`** (or any other `.html` page). That is all.
* Uses the `.html` pages + `js/` files (store.js, components.js, products-data.js …).
* Products are edited in `js/products-data.js`, settings in `js/config.js`.
* Accounts, cart, orders and tracking are saved **inside your browser only** (demo mode).
* The PDF bill also works offline.

## B) Full website with PHP + MySQL (server-side) – needs XAMPP
1. Start **Apache** + **MySQL** in XAMPP.
2. Copy this whole folder to `C:\xampp\htdocs\RT_Fashion`.
3. phpMyAdmin → Import → `database/rt_fashion.sql` (creates database `rt_fashion`).
4. Open **http://localhost/RT_Fashion/** (the PHP site).
5. Admin panel: **http://localhost/RT_Fashion/admin/**  –  `admin@rtfashionhub.com` / `Admin@123` (change it!).
Full details: `README_XAMPP.md`.

## Which file is which
| | Frontend only | PHP + MySQL |
|---|---|---|
| Pages | `index.html shop.html product.html cart.html checkout.html login.html account.html track.html about.html contact.html admin.html` | `index.php shop.php product.php cart.php checkout.php login.php account.php track.php invoice.php about.php contact.php` + `admin/` |
| Scripts | `js/store.js components.js home.js shop.js … products-data.js config.js` | `js/app.js invoice-pdf.js` + `includes/` `api/` |
| Data | browser storage | MySQL database `rt_fashion` |
| Shared | `css/styles.css`, `images/`, `js/vendor/jspdf.umd.min.js` | same |

The two versions do not share data: orders placed in the frontend-only version are not in MySQL.
