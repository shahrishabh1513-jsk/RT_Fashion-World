/* RT FASHION HUB – core: helpers, cart, wishlist, auth, orders.
   FRONTEND-ONLY DEMO: data lives in this browser's localStorage. Passwords are never stored
   in plain text (PBKDF2-SHA256 + per-user salt), but a real launch needs a server-side backend. */
(function(){
const C = window.RT_CONFIG, RT = window.RT = {};
RT.cfg = C; RT.products = window.RT_PRODUCTS || [];

/* ---------- helpers ---------- */
RT.esc = s => String(s ?? "").replace(/[&<>"']/g, c => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"}[c]));
RT.fmt = n => C.currency + Number(n).toLocaleString("en-IN", {maximumFractionDigits: 0});
RT.load = (k, d) => { try { const v = localStorage.getItem(k); return v ? JSON.parse(v) : d; } catch(e){ return d; } };
RT.save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); return true; } catch(e){ RT.toast("Browser storage is unavailable – changes won't be saved.", "err"); return false; } };
RT.qs = (k) => new URLSearchParams(location.search).get(k);
RT.byId = id => RT.products.find(p => p.id === id);
RT.isSale = p => typeof p.salePrice === "number" && p.salePrice > 0 && p.salePrice < p.price;
RT.price = p => RT.isSale(p) ? p.salePrice : p.price;
RT.off = p => RT.isSale(p) ? Math.round((1 - p.salePrice / p.price) * 100) : 0;

const I = {
 search:'<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
 bag:'<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>',
 user:'<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
 heart:'<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.7 1-1.1a5.5 5.5 0 0 0 0-7.8z"/>',
 menu:'<path d="M3 6h18M3 12h18M3 18h18"/>', x:'<path d="M18 6 6 18M6 6l12 12"/>',
 globe:'<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20z"/>',
 refresh:'<path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/>',
 shield:'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
 headset:'<path d="M3 14v-2a9 9 0 0 1 18 0v2"/><path d="M21 15a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2zM3 15a2 2 0 0 0 2 2h1v-5H5a2 2 0 0 0-2 2z"/><path d="M21 17v1a3 3 0 0 1-3 3h-4"/>',
 pin:'<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
 mail:'<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
 phone:'<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
 clock:'<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
 instagram:'<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17.5 6.5h.01"/>',
 facebook:'<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
 whatsapp:'<path d="M3 21l1.7-5A8.5 8.5 0 1 1 8 19.3z"/><path d="M9 10c0 3 2 5 5 5l1.5-1.5-2-1-1 .8c-1-.4-1.8-1.2-2.2-2.2l.8-1-1-2z"/>',
 github:'<path d="M9 19c-5 1.5-5-2.500-7-3m14 6v-3.900a3.400 3.400 0 0 0-.9-2.600c3-.3 6.200-1.500 6.200-6.700a5.200 5.200 0 0 0-1.400-3.600 4.800 4.800 0 0 0-.1-3.600s-1.100-.3-3.700 1.400a12.800 12.800 0 0 0-6.700 0C6.800 2.800 5.700 3.100 5.700 3.100a4.800 4.800 0 0 0-.1 3.600A5.200 5.200 0 0 0 4.200 10.300c0 5.200 3.200 6.400 6.200 6.700a3.400 3.400 0 0 0-.9 2.600V22"/>',
 check:'<path d="M20 6 9 17l-5-5"/>', up:'<path d="m18 15-6-6-6 6"/>',
 eye:'<path d="M2 12s4-8 10-8 10 8 10 8-4 8-10 8S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
 eyeOff:'<path d="M17.900 17.900A10 10 0 0 1 12 20C6 20 2 12 2 12a18 18 0 0 1 5-5.900M9.900 4.200A9 9 0 0 1 12 4c6 0 10 8 10 8a18 18 0 0 1-2.200 3.200M1 1l22 22"/>',
 star:'<path d="m12 2 3.100 6.300 6.900 1-5 4.900 1.200 6.800L12 17.700 5.800 21l1.200-6.800-5-4.900 6.900-1z"/>',
 hanger:'<path d="M12 7a2.500 2.500 0 1 0-2.500-2.500"/><path d="M12 7v2L3 15.500a1.500 1.500 0 0 0 .9 2.700h16.200a1.500 1.500 0 0 0 .9-2.700L12 9"/>',
 bed:'<path d="M2 18V6M2 14h20v4M22 14v-3a3 3 0 0 0-3-3h-8v6"/><circle cx="6.500" cy="11" r="1.500"/>',
 arrow:'<path d="M5 12h14M13 5l7 7-7 7"/>', trash:'<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
 filter:'<path d="M3 5h18l-7 8v6l-4 2v-8z"/>'
};
RT.icon = (n, cls="ico") => `<svg class="${cls}" viewBox="0 0 24 24" aria-hidden="true">${I[n]||""}</svg>`;

RT.fillIcons = (root=document) => root.querySelectorAll("[data-ico]").forEach(el => { el.innerHTML = RT.icon(el.dataset.ico); });
RT.stars = r => { let h = ""; for (let i = 1; i <= 5; i++) h += `<svg class="star ${r >= i - .25 ? "f" : r >= i - .75 ? "f" : "e"}" viewBox="0 0 24 24" style="${r < i && r >= i - .75 && r < i - .25 ? "opacity:.55" : ""}">${I.star}</svg>`; return `<div class="stars" title="${r} out of 5"><span class="row">${h}</span><span>${Number(r).toFixed(1)}</span></div>`; };

/* Branded placeholder (used when a product has no photo or its image fails to load) */
RT.placeholder = p => {
  const m = (p.name || "").match(/^(.*?)\s+(Men|Women|Solid|Pack|Casual|Large)\b/); const brand = RT.esc((m ? m[1] : (p.name||"").split(" ").slice(0,2).join(" ")).slice(0,26));
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800"><rect width="600" height="800" fill="#F8F6F3"/><g fill="none" stroke="#C6A46A" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" transform="translate(210 250) scale(7)"><path d="M12 7a2.5 2.5 0 1 0-2.5-2.5"/><path d="M12 7v2L3 15.5a1.5 1.5 0 0 0 .9 2.7h16.2a1.5 1.5 0 0 0 .9-2.7L12 9"/></g><text x="300" y="500" text-anchor="middle" font-family="Georgia,serif" font-size="30" fill="#171717">${brand}</text><text x="300" y="545" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" letter-spacing="4" fill="#777">${RT.esc((p.subcategory||"").toUpperCase())}</text><text x="300" y="740" text-anchor="middle" font-family="Arial,sans-serif" font-size="15" letter-spacing="3" fill="#bbb">RT FASHION HUB</text></svg>`;
  return "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg);
};
RT.imgSrc = p => p.image || RT.placeholder(p);
RT.imgTag = (p, src, alt) => `<img src="${RT.esc(src || RT.imgSrc(p))}" alt="${RT.esc(alt || p.name)}" loading="lazy" referrerpolicy="no-referrer" onerror="RT.imgFail(this,'${p.id}')">`;
RT.imgFail = (el, id) => { const p = RT.byId(id); if (p && !el.dataset.f) { el.dataset.f = 1; el.src = RT.placeholder(p); } };

RT.toast = (msg, type="") => {
  let box = document.querySelector(".toasts"); if (!box) { box = document.createElement("div"); box.className = "toasts"; box.setAttribute("aria-live","polite"); document.body.appendChild(box); }
  const t = document.createElement("div"); t.className = "toast " + type; t.textContent = msg; box.appendChild(t); setTimeout(() => t.remove(), 3200);
};
RT.emit = n => document.dispatchEvent(new CustomEvent(n));

/* ---------- cart ---------- */
RT.cart = {
  get: () => RT.load("rt_cart", []).filter(i => RT.byId(i.id)),
  set(a){ RT.save("rt_cart", a); RT.emit("rt:cart"); },
  add(id, qty=1){ const a = this.get(), f = a.find(i => i.id === id); f ? f.qty = Math.min(f.qty + qty, 20) : a.push({id, qty}); this.set(a); },
  setQty(id, q){ let a = this.get(); const f = a.find(i => i.id === id); if (!f) return; f.qty = Math.max(0, Math.min(q, 20)); a = a.filter(i => i.qty > 0); this.set(a); },
  remove(id){ this.set(this.get().filter(i => i.id !== id)); },
  clear(){ this.set([]); },
  count: () => RT.cart.get().reduce((s, i) => s + i.qty, 0),
  lines: () => RT.cart.get().map(i => ({...i, p: RT.byId(i.id)})),
  totals(lines = RT.cart.lines()){
    const subtotal = lines.reduce((s, l) => s + RT.price(l.p) * l.qty, 0);
    const shipping = lines.length ? C.shippingFee : 0, gst = Math.round(subtotal * C.gstRate * 100) / 100;
    return {subtotal, shipping, gst, total: subtotal + shipping + gst};
  }
};
RT.addToCart = id => { RT.cart.add(id); RT.toast("Added to cart", "ok"); };
RT.buyNow = id => { RT.cart.add(id); location.href = "checkout.html"; };

/* ---------- auth (demo, client-side) ---------- */
const enc = new TextEncoder(), hex = b => [...new Uint8Array(b)].map(x => x.toString(16).padStart(2,"0")).join("");
async function hashPw(pw, saltHex){
  if (!window.crypto?.subtle) throw new Error("Secure hashing needs https:// or localhost. Open the site via a local server (see README).");
  const salt = saltHex ? Uint8Array.from(saltHex.match(/../g).map(h => parseInt(h,16))) : crypto.getRandomValues(new Uint8Array(16));
  const key = await crypto.subtle.importKey("raw", enc.encode(pw), "PBKDF2", false, ["deriveBits"]);
  const bits = await crypto.subtle.deriveBits({name:"PBKDF2", salt, iterations:150000, hash:"SHA-256"}, key, 256);
  return {salt: hex(salt), hash: hex(bits)};
}
RT.validEmail = e => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(e);
RT.validPhone = p => /^(\+?91[\s-]?)?[6-9]\d{9}$/.test(String(p).replace(/\s|-/g,"").replace(/^(\+?91)/,"$1"));
RT.pwProblem = p => p.length < 8 ? "Password must be at least 8 characters." : !/[A-Za-z]/.test(p) || !/\d/.test(p) ? "Use letters and at least one number." : "";
const users = () => RT.load("rt_users", []);
RT.auth = {
  user(){ const id = sessionStorage.getItem("rt_session") || localStorage.getItem("rt_session"); return id ? users().find(u => u.id === id) || null : null; },
  safe: u => u && {id:u.id, customerId:u.customerId, name:u.name, email:u.email, mobile:u.mobile, addresses:u.addresses||[], created:u.created},
  async register({name,email,mobile,password,address}){
    email = email.trim().toLowerCase(); const all = users();
    if (all.some(u => u.email === email)) throw new Error("An account with this email already exists. Please log in.");
    const seq = (RT.load("rt_user_seq", 0)) + 1, cred = await hashPw(password);
    const u = {id:"u"+Date.now().toString(36)+Math.random().toString(36).slice(2,6), customerId:`RT1504-USER-${String(seq).padStart(4,"0")}`, name:name.trim(), email, mobile:mobile.trim(), addresses: address?.trim() ? [address.trim()] : [], created:new Date().toISOString(), ...cred};
    all.push(u); RT.save("rt_users", all); RT.save("rt_user_seq", seq); return u;
  },
  async login(email, password, remember){
    const u = users().find(x => x.email === email.trim().toLowerCase()); if (!u) throw new Error("Incorrect email or password.");
    const {hash} = await hashPw(password, u.salt); if (hash !== u.hash) throw new Error("Incorrect email or password.");
    sessionStorage.removeItem("rt_session"); localStorage.removeItem("rt_session");
    (remember ? localStorage : sessionStorage).setItem("rt_session", u.id);
    const g = RT.load("rt_wish_guest", []); if (g.length){ const w = RT.load("rt_wish_"+u.id, []); RT.save("rt_wish_"+u.id, [...new Set([...w, ...g])]); localStorage.removeItem("rt_wish_guest"); }
    return u;
  },
  logout(){ sessionStorage.removeItem("rt_session"); localStorage.removeItem("rt_session"); location.href = "index.html"; },
  update(patch){ const all = users(), u = all.find(x => x.id === this.user()?.id); if (!u) throw new Error("Please log in again."); 
    if (patch.email && patch.email !== u.email && all.some(x => x.email === patch.email)) throw new Error("That email is already used by another account.");
    Object.assign(u, patch); RT.save("rt_users", all); return u; },
  async changePassword(oldPw, newPw){ const u = this.user(); const {hash} = await hashPw(oldPw, u.salt); if (hash !== u.hash) throw new Error("Current password is incorrect."); const c = await hashPw(newPw); this.update(c); },
  async demoReset(email, newPw){ const all = users(), u = all.find(x => x.email === email.trim().toLowerCase()); if (!u) throw new Error("No account found with that email."); Object.assign(u, await hashPw(newPw)); RT.save("rt_users", all); }
};
RT.wish = {
  key: () => "rt_wish_" + (RT.auth.user()?.id || "guest"),
  get(){ return RT.load(this.key(), []).filter(id => RT.byId(id)); },
  has(id){ return this.get().includes(id); },
  toggle(id){ const a = this.get(), i = a.indexOf(id); i >= 0 ? a.splice(i,1) : a.push(id); RT.save(this.key(), a); RT.emit("rt:wish"); RT.toast(i >= 0 ? "Removed from wishlist" : "Saved to wishlist" + (RT.auth.user() ? "" : " (log in to keep it on your account)")); return i < 0; }
};
RT.orders = {
  STEPS: ["Order Placed","Confirmed","Packed","Shipped","Out for Delivery","Delivered"],
  all: () => RT.load("rt_orders", []),
  mine(){ const u = RT.auth.user(); return u ? this.all().filter(o => o.userId === u.id).reverse() : []; },
  place(o){ const a = this.all(), n = a.length + 1; o.id = `RT-ORD-${new Date().getFullYear()}-${String(n).padStart(5,"0")}`;
    o.status = "Order Placed"; o.history = [{status:"Order Placed", at:new Date().toISOString(), note:"Order request received (demo – stored in this browser)."}]; a.push(o); RT.save("rt_orders", a); return o; },
  find(id, email){ const o = this.all().find(x => x.id.toLowerCase() === String(id).trim().toLowerCase()); return o && (!email || o.customer.email.toLowerCase() === String(email).trim().toLowerCase()) ? o : null; },
  /* index of the current step in STEPS (-1 = cancelled). Older saved orders with other labels count as "Order Placed". */
  step(o){ if (o.status === "Cancelled") return -1; const i = this.STEPS.indexOf(o.status); return i < 0 ? 0 : i; },
  update(id, {status, tracking, note}){ const a = this.all(), o = a.find(x => x.id === id); if (!o) return null;
    o.history = o.history || [{status:o.status, at:o.date}];
    if (status && status !== o.status){ o.status = status; o.history.push({status, at:new Date().toISOString(), note:note || ""}); }
    if (tracking !== undefined) o.tracking = tracking; RT.save("rt_orders", a); return o; }
};

/* ---------- product card ---------- */
RT.card = p => {
  const sale = RT.isSale(p), tags = [];
  if (sale) tags.push(`<span class="tag tag--sale">Sale ${RT.off(p)}% off</span>`);
  if (p.badge) tags.push(`<span class="tag tag--gold">${RT.esc(p.badge)}</span>`);
  const on = RT.wish.has(p.id);
  return `<article class="pcard" data-id="${p.id}">
   <a class="thumb" href="product.html?id=${p.id}" aria-label="View ${RT.esc(p.name)}">${RT.imgTag(p)}<span class="tags">${tags.join("")}</span></a>
   <button class="wish ${on?"on":""}" data-wish="${p.id}" aria-label="Toggle wishlist" aria-pressed="${on}">${RT.icon("heart")}</button>
   <div class="pinfo"><span class="pcat">${RT.esc(p.category)} · ${RT.esc(p.subcategory)}</span>
    <a class="pname" href="product.html?id=${p.id}">${RT.esc(p.name)}</a>${RT.stars(p.rating)}
    <div class="price-row"><span class="price">${RT.fmt(RT.price(p))}</span>${sale ? `<span class="price-old">${RT.fmt(p.price)}</span>` : ""}</div>
    <div class="pactions"><button class="btn btn--outline btn--sm" data-add="${p.id}">Add to Cart</button><button class="btn btn--dark btn--sm" data-buy="${p.id}">Buy Now</button></div></div></article>`;
};
/* one delegated listener handles every card button on every page */
document.addEventListener("click", e => {
  const a = e.target.closest("[data-add]"), b = e.target.closest("[data-buy]"), w = e.target.closest("[data-wish]");
  if (a) RT.addToCart(a.dataset.add); else if (b) RT.buyNow(b.dataset.buy);
  else if (w){ const on = RT.wish.toggle(w.dataset.wish); document.querySelectorAll(`[data-wish="${w.dataset.wish}"]`).forEach(x => { x.classList.toggle("on", on); x.setAttribute("aria-pressed", on); }); }
});
RT.reveal = () => { const io = new IntersectionObserver(es => es.forEach(x => x.isIntersecting && (x.target.classList.add("in"), io.unobserve(x.target))), {threshold:.12}); document.querySelectorAll(".reveal:not(.in)").forEach(el => io.observe(el)); };
RT.requireLogin = () => { if (!RT.auth.user()){ location.replace("login.html?next=" + encodeURIComponent(location.pathname.split("/").pop() + location.search + location.hash)); return false; } return true; };
})();
