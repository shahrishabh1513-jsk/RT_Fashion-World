# RT FASHION HUB – Fashion & Lifestyle Store

Static HTML/CSS/JavaScript site (same stack as the original project, no build step).

## Run it
Open through a local server (needed for secure password hashing in the browser):
```
python3 -m http.server 8000      # then visit http://localhost:8000
```

## Where to edit things
| What | File |
|---|---|
| Products, prices, **sale tags**, badges, images | `js/products-data.js` |
| Contact details, GST, delivery fee, GitHub URL, form endpoints | `js/config.js` |
| Colours / layout | `css/styles.css` (`:root` variables at top) |
| Product photos | put files in `images/products/` and set `image:` in `products-data.js` |
| Logo files | `images/brand/` |
| Bill (PDF) layout, GSTIN, bill terms, owner PIN | `js/invoice.js`, `js/config.js` |
| Optional: auto-download product photos from the Flipkart links | `node tools/fetch-product-images.js` (run locally; untested against live Flipkart) |

### Add a SALE tag
In `js/products-data.js` set `salePrice` lower than `price`, e.g. `price: 1499, salePrice: 999`.
A red "Sale xx% off" tag, the struck-through old price and the discount appear automatically. Set it back to `null` to end the sale.

## Not connected (needs your credentials / a server)
* **Payments** – UPI/Card/NetBanking are disabled; Razorpay needs keys + a backend.
* **Contact form / newsletter** – set `contactEndpoint` / `newsletterEndpoint` in `config.js` (e.g. Formspree). Until then the site says the message was not sent and offers an email link.
* **Accounts & orders** – demo only, stored in the browser (passwords salted + hashed with PBKDF2). Replace with a real backend before launch.

The previous version of the site is kept in `_legacy/`.
