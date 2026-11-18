/* OPTIONAL helper (needs Node 18+): run on YOUR computer, not in a sandbox.
   Reads each Flipkart link in js/products-data.js, finds the listing's main image and
   downloads it to images/products/<id>.jpg. Then set  image: "images/products/<id>.jpg"
   in products-data.js for the ones that worked.  Usage:  node tools/fetch-product-images.js
   Flipkart may block automated requests; failures are listed so you can add photos by hand.
   Only use images you have the right to use (e.g. under your affiliate terms). NOT TESTED against live Flipkart. */
const fs = require("fs"), vm = require("vm"), path = require("path");
const ctx = { window: {} }; vm.createContext(ctx);
vm.runInContext(fs.readFileSync(path.join(__dirname, "../js/products-data.js"), "utf8"), ctx);
const UA = { "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36" };
(async () => {
  fs.mkdirSync(path.join(__dirname, "../images/products"), { recursive: true });
  for (const p of ctx.window.RT_PRODUCTS) {
    if (!p.link) { console.log("skip (no link)", p.id); continue; }
    try {
      const html = await (await fetch(p.link, { headers: UA, redirect: "follow" })).text();
      const m = html.match(/property="og:image"\s+content="([^"]+)"/i) || html.match(/content="([^"]+)"\s+property="og:image"/i);
      if (!m) throw new Error("no og:image");
      const url = m[1].replace(/\/image\/\d+\/\d+\//, "/image/600/800/");
      const buf = Buffer.from(await (await fetch(url, { headers: UA })).arrayBuffer());
      fs.writeFileSync(path.join(__dirname, `../images/products/${p.id}.jpg`), buf);
      console.log("OK  ", p.id, p.name.slice(0, 50));
    } catch (e) { console.log("FAIL", p.id, e.message); }
    await new Promise(r => setTimeout(r, 1200));
  }
})();
