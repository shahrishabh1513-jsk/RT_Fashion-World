(function () {
  if (!RT.requireLogin()) return;
  const nav = document.getElementById("accNav"), main = document.getElementById("accMain");
  const TABS = [["dashboard", "Dashboard"], ["profile", "My Profile"], ["orders", "My Orders"], ["cart", "Shopping Cart"], ["wishlist", "Wishlist"], ["addresses", "Address Book"], ["settings", "Account Settings"], ["logout", "Logout"]];
  const me = () => RT.auth.user(), E = RT.esc, date = d => new Date(d).toLocaleDateString("en-IN", { day: "numeric", month: "short", year: "numeric" });
  const kv = (k, v) => `<div><small>${k}</small><b>${v || "—"}</b></div>`;
  const field = (id, label, val = "", type = "text", extra = "") => `<div class="field"><label for="${id}">${label}</label><input id="${id}" type="${type}" value="${E(val)}" ${extra}><span class="err"></span></div>`;
  const mark = (el, m) => { const f = el.closest(".field"); f.classList.toggle("invalid", !!m); f.querySelector(".err").textContent = m || ""; return !m; };
  const msg = (id, t, c) => { const o = document.getElementById(id); o.className = "form-msg " + c; o.textContent = t; };

  const views = {
    dashboard(u) {
      const o = RT.orders.mine(); return `<div class="welcome"><h2>Welcome, ${E(u.name.split(" ")[0])}!</h2><p>Manage your orders, wishlist and details from here.</p></div>
  <div class="panel"><h2>Account Information</h2><div class="kv">${kv("Customer Name", E(u.name))}${kv("Customer ID", u.customerId)}${kv("Registered Email", E(u.email))}${kv("Mobile Number", E(u.mobile))}${kv("Saved Address", E((u.addresses || [])[0] || "No address saved yet"))}${kv("Member Since", date(u.created))}</div>
  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px"><a class="btn btn--dark btn--sm" href="#orders">${o.length} order${o.length === 1 ? "" : "s"}</a><a class="btn btn--outline btn--sm" href="#wishlist">${RT.wish.get().length} wishlist item${RT.wish.get().length === 1 ? "" : "s"}</a></div></div>`;
    },
    profile(u) {
      return `<div class="panel"><h2>My Profile</h2><form id="profForm" novalidate><div class="form-grid">${field("pName", "Full Name", u.name)}${field("pMobile", "Mobile Number", u.mobile, "tel")}${field("pEmail", "Email", u.email, "email")}
  <div class="field"><label>Customer ID</label><input value="${u.customerId}" readonly></div><div class="field full"><label for="pAddr">Primary Address</label><textarea id="pAddr" rows="3">${E((u.addresses || [])[0] || "")}</textarea></div>
  <div class="full"><button class="btn btn--dark" type="submit">Save Changes</button><div class="form-msg" id="pOut" role="status"></div></div></div></form></div>`;
    },
    orders(u) {
      const o = RT.orders.mine(); if (!o.length) return `<div class="panel"><h2>My Orders</h2><div class="empty">${RT.icon("bag")}<h3>No orders yet</h3><p>Your orders will appear here.</p><a class="btn btn--dark" href="shop.html">Start Shopping</a></div></div>`;
      return `<div class="panel"><h2>My Orders</h2>${o.map(x => `<div class="order"><div class="order-head"><span><b>${x.id}</b></span><span>${date(x.date)}</span><span>${x.payment}</span><span class="status status--pending">${E(x.status)}</span><b>${RT.fmt(x.total)}</b></div>
   <div class="order-body">${x.items.map(i => { const p = RT.byId(i.id); return `<div class="order-line">${p ? RT.imgTag(p) : "<span></span>"}<div style="font-size:.9rem">${E(i.name)}<br><span style="color:var(--muted)">Qty ${i.qty} × ${RT.fmt(i.price)}</span></div><b>${RT.fmt(i.qty * i.price)}</b></div>`; }).join("")}
   <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px"><a class="btn btn--dark btn--sm" href="track.html?id=${x.id}">Track Order</a><button class="btn btn--outline btn--sm" data-bill="${x.id}">Download Bill (PDF)</button></div><details style="margin-top:12px"><summary class="link" style="cursor:pointer">Order details</summary><div class="kv" style="margin:14px 0">${kv("Customer", E(x.customer.name) + "<br>" + E(x.customer.email) + "<br>" + E(x.customer.mobile))}${kv("Shipping Address", E(x.address))}${kv("Payment Status", E(x.paymentStatus))}${kv("Tracking", E(x.tracking) || "Not available")}</div>
   <div class="totals"><div class="row"><span>Subtotal</span><span>${RT.fmt(x.subtotal)}</span></div><div class="row"><span>Delivery</span><span>${x.shipping ? RT.fmt(x.shipping) : "Free"}</span></div><div class="row"><span>Tax (GST)</span><span>${RT.fmt(x.gst)}</span></div><div class="row grand"><span>Final Amount</span><span>${RT.fmt(x.total)}</span></div></div></details></div></div>`).join("")}</div>`;
    },
    wishlist() { const w = RT.wish.get().map(RT.byId); return `<div class="panel"><h2>Wishlist</h2>${w.length ? `<div class="product-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr))">${w.map(RT.card).join("")}</div>` : `<div class="empty">${RT.icon("heart")}<h3>Your wishlist is empty</h3><p>Tap the heart on any product to save it.</p><a class="btn btn--dark" href="shop.html">Browse Products</a></div>`}</div>`; },
    addresses(u) {
      const a = u.addresses || []; return `<div class="panel"><h2>Address Book</h2>${a.length ? a.map((x, i) => `<div class="addr-card"><div>${i === 0 ? `<span class="tag tag--gold" style="display:inline-block;margin-bottom:8px">Default</span><br>` : ""}${E(x)}</div><div style="display:grid;gap:6px;justify-items:end">${i ? `<button class="link" data-def="${i}">Make default</button>` : ""}<button class="remove-link" data-del="${i}">Delete</button></div></div>`).join("") : `<p style="color:var(--muted);margin-bottom:16px">No saved addresses yet.</p>`}
  <form id="addrForm" style="margin-top:18px" novalidate><div class="field"><label for="aNew">Add a new address</label><textarea id="aNew" rows="3" placeholder="House no., street, city, state, PIN"></textarea><span class="err"></span></div><button class="btn btn--dark btn--sm" style="margin-top:12px" type="submit">Save Address</button></form></div>`;
    },
    settings() {
      const f = (id, l) => `<div class="field"><label for="${id}">${l}</label><div class="pw"><input id="${id}" type="password" autocomplete="${id === "sOld" ? "current-password" : "new-password"}"><button type="button" data-toggle="${id}" aria-label="Show password">${RT.icon("eye")}</button></div><span class="err"></span></div>`;
      return `<div class="panel"><h2>Account Settings</h2><h3 style="font-size:1.1rem;margin-bottom:14px;font-family:var(--font)">Change password</h3><form id="pwForm" style="display:grid;gap:14px;max-width:420px" novalidate>${f("sOld", "Current password")}${f("sNew", "New password")}${f("sNew2", "Confirm new password")}<button class="btn btn--dark" type="submit">Update Password</button><div class="form-msg" id="sOut" role="status"></div></form>
  <p class="note" style="margin-top:24px">Demo note: account data is stored in this browser only.</p></div>`;
    }
  };
  function bind(name, u) {
    main.querySelectorAll("[data-bill]").forEach(b => b.onclick = () => RT.invoice.download(RT.orders.all().find(o => o.id === b.dataset.bill)));
    if (name === "profile") document.getElementById("profForm").addEventListener("submit", e => {
      e.preventDefault();
      const n = pName, m = pMobile, em = pEmail; const ok = [mark(n, n.value.trim().length < 2 ? "Enter your name." : ""), mark(m, RT.validPhone(m.value) ? "" : "Enter a valid mobile number."), mark(em, RT.validEmail(em.value.trim()) ? "" : "Enter a valid email.")].every(Boolean); if (!ok) return;
      try { const a = [...(u.addresses || [])]; pAddr.value.trim() ? a[0] = pAddr.value.trim() : a.shift(); RT.auth.update({ name: n.value.trim(), mobile: m.value.trim(), email: em.value.trim().toLowerCase(), addresses: a }); msg("pOut", "Profile updated.", "ok"); RT.toast("Profile updated", "ok"); }
      catch (err) { msg("pOut", err.message, "err"); }
    });
    if (name === "addresses") {
      document.getElementById("addrForm").addEventListener("submit", e => { e.preventDefault(); const v = aNew.value.trim(); if (v.length < 10) return mark(aNew, "Enter the full address."); RT.auth.update({ addresses: [...(u.addresses || []), v] }); show("addresses"); RT.toast("Address saved", "ok"); });
      main.querySelectorAll("[data-del]").forEach(b => b.onclick = () => { const a = [...u.addresses]; a.splice(+b.dataset.del, 1); RT.auth.update({ addresses: a }); show("addresses"); });
      main.querySelectorAll("[data-def]").forEach(b => b.onclick = () => { const a = [...u.addresses], [x] = a.splice(+b.dataset.def, 1); a.unshift(x); RT.auth.update({ addresses: a }); show("addresses"); });
    }
    if (name === "settings") {
      main.querySelectorAll("[data-toggle]").forEach(b => b.onclick = () => { const i = document.getElementById(b.dataset.toggle), s = i.type === "password"; i.type = s ? "text" : "password"; b.innerHTML = RT.icon(s ? "eyeOff" : "eye"); });
      document.getElementById("pwForm").addEventListener("submit", async e => {
        e.preventDefault(); const pr = RT.pwProblem(sNew.value);
        const ok = [mark(sOld, sOld.value ? "" : "Enter your current password."), mark(sNew, pr), mark(sNew2, sNew.value === sNew2.value ? "" : "Passwords do not match.")].every(Boolean); if (!ok) return;
        try { await RT.auth.changePassword(sOld.value, sNew.value); e.target.reset(); msg("sOut", "Password updated.", "ok"); } catch (err) { msg("sOut", err.message, "err"); }
      });
    }
  }
  function show(name) {
    if (name === "logout") return RT.auth.logout();
    if (name === "cart") return location.href = "cart.html";
    if (!views[name]) name = "dashboard"; const u = me(); if (!u) return RT.requireLogin();
    nav.innerHTML = TABS.map(([k, l]) => `<button class="${k === name ? "on" : ""}" data-tab="${k}">${l}</button>`).join("");
    main.innerHTML = views[name](u); bind(name, u); if (location.hash.slice(1) !== name) history.replaceState(null, "", "#" + name);
  }
  nav.addEventListener("click", e => { const b = e.target.closest("[data-tab]"); if (b) show(b.dataset.tab); });
  window.addEventListener("hashchange", () => show(location.hash.slice(1) || "dashboard"));
  document.addEventListener("rt:wish", () => location.hash === "#wishlist" && show("wishlist"));
  show(location.hash.slice(1) || "dashboard");
})();
