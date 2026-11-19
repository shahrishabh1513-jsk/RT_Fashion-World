(function () {
  const C = RT.cfg, page = document.body.dataset.page || "";
  const nav = [["index.html", "Home", "home"], ["shop.html", "Shop", "shop"], ["about.html", "About", "about"], ["contact.html", "Contact", "contact"]];

  function header() {
    const u = RT.auth.user(), q = RT.esc(RT.qs("q") || "");
    const links = nav.map(([h, t, k]) => `<a href="${h}" class="${page === k ? "active" : ""}" ${page === k ? 'aria-current="page"' : ""}>${t}</a>`).join("");
    const searchForm = cls => `<form class="search-form ${cls}" action="shop.html" method="get" role="search" autocomplete="off"><input type="search" name="q" value="${q}" placeholder="Search products or category" aria-label="Search products"><button class="icon-btn" type="submit" aria-label="Search">${RT.icon("search")}</button><div class="search-suggest" role="listbox"></div></form>`;
    const account = u ? `<div class="user-menu"><button class="icon-btn" aria-label="My profile" aria-haspopup="true" id="userBtn">${RT.icon("user")}</button>
      <div class="user-dropdown"><div class="who">Signed in as<br><b>${RT.esc(u.name)}</b></div><a href="account.html">My Account</a><a href="account.html#orders">My Orders</a><a href="account.html#wishlist">Wishlist</a><button id="logoutBtn">Logout</button></div></div>`
      : `<a class="btn btn--dark btn--sm login-btn" href="login.html">Login / Register</a>`;
    return `${C.showPlaceholderPriceNotice ? `<div class="notice-bar"><b>Note:</b> product prices are placeholders until updated in <code>js/products-data.js</code>.</div>` : ""}
  <header class="site-header"><div class="container header-inner">
    <button class="icon-btn menu-toggle" id="menuBtn" aria-label="Open menu" aria-expanded="false">${RT.icon("menu")}</button>
    <a class="logo" href="index.html" aria-label="RT Fashion Hub – home"><img src="images/brand/logo.png" alt="RT Fashion Hub" width="108" height="46"></a>
    <nav class="main-nav" id="mainNav" aria-label="Main">${links}</nav>
    <div class="header-actions">${searchForm("desktop")}
      ${u ? "" : `<a class="icon-btn menu-toggle" href="login.html" aria-label="Login or register">${RT.icon("user")}</a>`}
      ${account}
      <button class="icon-btn" id="cartBtn" aria-label="Open cart">${RT.icon("bag")}<span class="badge-count" id="cartCount">0</span></button>
    </div></div><div class="mobile-search">${searchForm("")}</div></header>
  <div class="overlay" id="overlay"></div>
  <aside class="drawer" id="drawer" aria-label="Shopping cart" aria-hidden="true"><div class="drawer-head"><h3>Your Cart</h3><button class="icon-btn" id="drawerClose" aria-label="Close cart">${RT.icon("x")}</button></div><div class="drawer-body" id="drawerBody"></div><div class="drawer-foot" id="drawerFoot"></div></aside>`;
  }

  function footer() {
    const s = C.social, soc = [["instagram", "Instagram"], ["facebook", "Facebook"], ["whatsapp", "WhatsApp"]].filter(([k]) => s[k]).map(([k, n]) => `<a href="${s[k]}" target="_blank" rel="noopener" aria-label="${n}">${RT.icon(k)}</a>`).join("");
    return `<footer class="site-footer"><div class="container"><div class="footer-grid">
   <div><img class="flogo" src="images/brand/logo-white.png" alt="RT Fashion Hub"><p>Discover your style with RT Fashion Hub. Explore modern fashion, lifestyle essentials, and collections designed for every occasion.</p><div class="socials">${soc || ""}<a href="mailto:${C.email}" aria-label="Email us">${RT.icon("mail")}</a></div></div>
   <div><h4>Quick Access</h4><ul><li><a href="index.html">Home</a></li><li><a href="shop.html">Shop</a></li><li><a href="about.html">About Us</a></li><li><a href="contact.html">Contact Us</a></li></ul></div>
   <div><h4>Customer Services</h4><ul><li><a href="account.html">My Account</a></li><li><a href="account.html#orders">My Orders</a></li><li><a href="track.html">Track Order</a></li><li><a href="cart.html">Shopping Cart</a></li><li><a href="account.html#wishlist">Wishlist</a></li><li><a href="contact.html#faq">FAQ</a></li></ul></div>
   <div><h4>Contact Information</h4><ul>
     <li class="contact-li">${RT.icon("pin")}<span>${C.location}</span></li>
     <li class="contact-li">${RT.icon("mail")}<a href="mailto:${C.email}">${C.email}</a></li>
     <li class="contact-li">${RT.icon("phone")}<a href="tel:${C.phoneHref}">${C.phone}</a></li>
     <li class="contact-li">${RT.icon("clock")}<span>${C.hours.replace("Monday to Saturday: ", "Monday to Saturday:<br>")}</span></li></ul></div>
  </div>
  <div class="footer-bottom"><span>© 2026 RT FASHION HUB. All Rights Reserved.</span>
   <div class="fb-right"><span>Made with <span class="heart">♥</span> by RT1504</span>${C.githubUrl ? `<a class="btn btn--light btn--sm" href="${C.githubUrl}" target="_blank" rel="noopener">${RT.icon("github")} GitHub Project</a>` : ""}</div></div></div></footer>
  <button class="to-top" id="toTop" aria-label="Back to top">${RT.icon("up")}</button>`;
  }

  function mount() {
    const h = document.getElementById("site-header"), f = document.getElementById("site-footer");
    if (h) h.outerHTML = header(); if (f) f.outerHTML = footer();
    const $ = id => document.getElementById(id), nv = $("mainNav"), ov = $("overlay"), dr = $("drawer");
    const closeAll = () => { nv.classList.remove("open"); dr.classList.remove("open"); ov.classList.remove("open"); dr.setAttribute("aria-hidden", "true"); $("menuBtn").setAttribute("aria-expanded", "false"); document.body.style.overflow = ""; };
    $("menuBtn").onclick = () => { nv.classList.add("open"); ov.classList.add("open"); $("menuBtn").setAttribute("aria-expanded", "true"); document.body.style.overflow = "hidden"; };
    $("cartBtn").onclick = () => { renderDrawer(); dr.classList.add("open"); dr.setAttribute("aria-hidden", "false"); ov.classList.add("open"); document.body.style.overflow = "hidden"; };
    $("drawerClose").onclick = ov.onclick = closeAll; nv.addEventListener("click", e => e.target.closest("a") && closeAll());
    document.addEventListener("keydown", e => e.key === "Escape" && closeAll());
    const ub = $("userBtn"); if (ub) { ub.onclick = e => { e.stopPropagation(); ub.parentElement.classList.toggle("open"); }; document.addEventListener("click", () => ub.parentElement.classList.remove("open")); $("logoutBtn").onclick = RT.auth.logout; }

    /* cart count + drawer */
    const upd = () => { const n = RT.cart.count(), c = $("cartCount"); c.textContent = n; c.style.display = n ? "flex" : "none"; if (dr.classList.contains("open")) renderDrawer(); };
    function renderDrawer() {
      const L = RT.cart.lines(), b = $("drawerBody"), f = $("drawerFoot");
      if (!L.length) { b.innerHTML = `<div class="empty">${RT.icon("bag")}<h3>Your cart is empty</h3><p>Discover something you'll love.</p></div>`; f.innerHTML = `<a class="btn btn--dark btn--block" href="shop.html">Continue Shopping</a>`; return; }
      b.innerHTML = L.map(l => `<div class="mini-item"><a href="product.html?id=${l.p.id}">${RT.imgTag(l.p)}</a><div><h4>${RT.esc(l.p.name)}</h4><div class="price">${RT.fmt(RT.price(l.p))}</div>
      <div class="qty" style="margin-top:8px"><button data-q="${l.id}" data-d="-1" aria-label="Decrease">−</button><span>${l.qty}</span><button data-q="${l.id}" data-d="1" aria-label="Increase">+</button></div></div>
      <button class="icon-btn" data-rm="${l.id}" aria-label="Remove item">${RT.icon("trash")}</button></div>`).join("");
      f.innerHTML = `<div class="row"><span>Subtotal</span><span>${RT.fmt(RT.cart.totals(L).subtotal)}</span></div><a class="btn btn--outline btn--block" href="cart.html">View Cart</a><a class="btn btn--dark btn--block" href="checkout.html">Proceed to Checkout</a>`;
    }
    $("drawerBody").addEventListener("click", e => {
      const q = e.target.closest("[data-q]"), r = e.target.closest("[data-rm]");
      if (q) { const l = RT.cart.get().find(i => i.id === q.dataset.q); RT.cart.setQty(q.dataset.q, l.qty + Number(q.dataset.d)); } else if (r) RT.cart.remove(r.dataset.rm);
    });
    document.addEventListener("rt:cart", upd); window.addEventListener("storage", upd); upd();

    /* live search suggestions (name + category) */
    document.querySelectorAll(".search-form").forEach(form => {
      const inp = form.querySelector("input"), box = form.querySelector(".search-suggest");
      inp.addEventListener("input", () => {
        const t = inp.value.trim().toLowerCase(); if (t.length < 2) { box.classList.remove("open"); return; }
        const m = RT.products.filter(p => (p.name + " " + p.category + " " + p.subcategory + " " + p.collection).toLowerCase().includes(t)).slice(0, 6);
        box.innerHTML = m.length ? m.map(p => `<a href="product.html?id=${p.id}">${RT.imgTag(p)}<span>${RT.esc(p.name)}<small>${RT.esc(p.category)} · ${RT.esc(p.subcategory)}</small></span></a>`).join("") : `<div style="padding:14px;font-size:.85rem;color:var(--muted)">No matching products</div>`; box.classList.add("open");
      });
      document.addEventListener("click", e => !form.contains(e.target) && box.classList.remove("open"));
      form.addEventListener("submit", e => { if (!inp.value.trim()) { e.preventDefault(); location.href = "shop.html"; } });
    });
    const tt = $("toTop"); window.addEventListener("scroll", () => tt.classList.toggle("show", scrollY > 500), { passive: true }); tt.onclick = () => scrollTo({ top: 0, behavior: "smooth" });
    RT.fillIcons(); RT.reveal();
  }
  document.readyState === "loading" ? document.addEventListener("DOMContentLoaded", mount) : mount();
})();
