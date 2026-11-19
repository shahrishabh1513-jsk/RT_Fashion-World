/* OPTIONAL helper (needs Node 18+): run on YOUR computer, not in a sandbox.
   Reads each Flipkart link in js/products-data.js, finds the listing's main image and
   downloads it to the category folder as <id>.webp (images/man, images/woman, images/Accessories,
   images/Bags, images/Jewelry) – the exact paths already set in products-data.js. (Flipkart serves JPEG data;
   browsers display it fine under a .webp name, or convert to real WebP later.)  Usage:  node tools/fetch-product-images.js
   Flipkart may block automated requests; failures are listed so you can add photos by hand.
   Only use images you have the right to use (e.g. under your affiliate terms). NOT TESTED against live Flipkart. */
const fs = require("fs"), vm = require("vm"), path = require("path");
const ctx = { window: {} }; vm.createContext(ctx);
vm.runInContext(fs.readFileSync(path.join(__dirname, "../js/products-data.js"), "utf8"), ctx);
const FOLDER = {Men:"man", Women:"woman", Accessories:"Accessories", Bags:"Bags", Jewelry:"Jewelry"};
const UA = { "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120 Safari/537.36" };
(async () => {
  
  for (const p of ctx.window.RT_PRODUCTS) {
    if (!p.link) { console.log("skip (no link)", p.id); continue; }
    try {
      const html = await (await fetch(p.link, { headers: UA, redirect: "follow" })).text();
      const m = html.match(/property="og:image"\s+content="([^"]+)"/i) || html.match(/content="([^"]+)"\s+property="og:image"/i);
      if (!m) throw new Error("no og:image");
      const url = m[1].replace(/\/image\/\d+\/\d+\//, "/image/600/800/");
      const buf = Buffer.from(await (await fetch(url, { headers: UA })).arrayBuffer());
      const dir = path.join(__dirname, "../images", FOLDER[p.category]); fs.mkdirSync(dir, { recursive: true }); fs.writeFileSync(path.join(dir, `${p.id}.webp`), buf);
      console.log("OK  ", p.id, p.name.slice(0, 50));
    } catch (e) { console.log("FAIL", p.id, e.message); }
    await new Promise(r => setTimeout(r, 1200));
  }
})();
