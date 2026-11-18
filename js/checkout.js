(function(){
if (!RT.requireLogin()) return;
const u = RT.auth.user(), main = document.getElementById("coMain"), L = RT.cart.lines();
if (!L.length){ main.innerHTML = `<div class="empty">${RT.icon("bag")}<h3>Your cart is empty</h3><a class="btn btn--dark" href="shop.html">Continue Shopping</a></div>`; return; }
const t = RT.cart.totals(L), saved = u.addresses || [];
const opt = (v, title, sub, off) => `<label class="pay-opt ${off ? "disabled" : ""}"><input type="radio" name="pay" value="${v}" ${off ? "disabled" : ""} ${v === "Cash on Delivery" ? "checked" : ""}><span><b>${title}</b><small>${sub}</small></span></label>`;
const why = "Not available yet – needs a payment gateway (e.g. Razorpay) with your API keys and a server.";
main.innerHTML = `<div class="alert alert--warn"><b>Demo checkout.</b> There is no payment gateway or order server connected. Placing an order saves an order <i>request</i> in this browser's order history only – no payment is taken and no store receives it.</div>
<form class="cart-layout" id="coForm" novalidate><div style="display:grid;gap:26px">
 <div class="panel"><h2>Customer Information</h2><div class="form-grid">
  <div class="field"><label for="coName">Full Name *</label><input id="coName" value="${RT.esc(u.name)}" required><span class="err"></span></div>
  <div class="field"><label for="coMobile">Mobile *</label><input id="coMobile" value="${RT.esc(u.mobile)}" type="tel" required><span class="err"></span></div>
  <div class="field full"><label for="coEmail">Email</label><input id="coEmail" value="${RT.esc(u.email)}" type="email" readonly></div></div></div>
 <div class="panel"><h2>Shipping Address</h2>${saved.length ? `<div class="field" style="margin-bottom:14px"><label for="coSaved">Saved addresses</label><select id="coSaved"><option value="">Enter a new address</option>${saved.map((a,i) => `<option value="${i}" ${i===0?"selected":""}>${RT.esc(a.slice(0,70))}</option>`).join("")}</select></div>` : ""}
  <div class="field"><label for="coAddr">Delivery address *</label><textarea id="coAddr" rows="4" required>${RT.esc(saved[0] || "")}</textarea><span class="err"></span></div>
  <div class="field" style="margin-top:14px;max-width:220px"><label for="coPin">PIN code *</label><input id="coPin" inputmode="numeric" maxlength="6" required><span class="err"></span></div></div>
 <div class="panel"><h2>Payment Method</h2>${opt("UPI","UPI",why,1)}${opt("Card","Debit / Credit Card",why,1)}${opt("NetBanking","NetBanking",why,1)}${opt("Cash on Delivery","Cash on Delivery","Pay when your order arrives.",0)}</div></div>
 <aside class="summary"><h3>Order Summary</h3>${L.map(l => `<div class="order-line"><a href="product.html?id=${l.p.id}">${RT.imgTag(l.p)}</a><div style="font-size:.84rem">${RT.esc(l.p.name)}<br><span style="color:var(--muted)">Qty ${l.qty} × ${RT.fmt(RT.price(l.p))}</span></div><b>${RT.fmt(RT.price(l.p) * l.qty)}</b></div>`).join("")}
  <div class="totals" style="margin-top:14px"><div class="row"><span>Subtotal</span><span>${RT.fmt(t.subtotal)}</span></div><div class="row"><span>Delivery</span><span>${t.shipping ? RT.fmt(t.shipping) : "Free"}</span></div><div class="row"><span>GST (${RT.cfg.gstRate*100}%)</span><span>${RT.fmt(t.gst)}</span></div><div class="row grand"><span>Final Amount</span><span>${RT.fmt(t.total)}</span></div></div>
  <button class="btn btn--dark btn--block" style="margin-top:20px" type="submit">Place Order (Cash on Delivery)</button><div class="form-msg err" id="coOut" role="alert"></div></aside></form>`;
const $ = id => document.getElementById(id);
$("coSaved")?.addEventListener("change", e => { $("coAddr").value = e.target.value === "" ? "" : saved[+e.target.value]; });
const mark = (el, m) => { const f = el.closest(".field"); f.classList.toggle("invalid", !!m); f.querySelector(".err").textContent = m || ""; return !m; };
$("coForm").addEventListener("submit", e => {
  e.preventDefault();
  const ok = [mark($("coName"), $("coName").value.trim().length < 2 ? "Enter your name." : ""), mark($("coMobile"), RT.validPhone($("coMobile").value) ? "" : "Enter a valid mobile number."),
    mark($("coAddr"), $("coAddr").value.trim().length < 10 ? "Enter your full delivery address." : ""), mark($("coPin"), /^[1-9]\d{5}$/.test($("coPin").value) ? "" : "Enter a valid 6-digit PIN code.")].every(Boolean);
  if (!ok) return;
  const lines = RT.cart.lines(), tt = RT.cart.totals(lines);
  const o = RT.orders.place({userId:u.id, date:new Date().toISOString(), status:"Request saved (demo)", payment:"Cash on Delivery", paymentStatus:"Pay on delivery", tracking:"",
    customer:{name:$("coName").value.trim(), email:u.email, mobile:$("coMobile").value.trim()}, address:$("coAddr").value.trim() + " – " + $("coPin").value,
    items:lines.map(l => ({id:l.id, name:l.p.name, qty:l.qty, price:RT.price(l.p)})), ...tt});
  if (!(u.addresses||[]).length) RT.auth.update({addresses:[$("coAddr").value.trim()]});
  RT.cart.clear();
  main.innerHTML = `<div class="empty" style="max-width:560px;margin:0 auto">${RT.icon("check")}<h3>Order request saved</h3><p>Reference <b>${o.id}</b> · Total ${RT.fmt(o.total)}</p><p class="note">Demo mode: this request is stored only in this browser. No payment was taken and no store has received it. See it under My Orders.</p><a class="btn btn--dark" href="account.html#orders">View My Orders</a> <a class="btn btn--outline" href="shop.html">Continue Shopping</a></div>`;
  scrollTo({top:0,behavior:"smooth"});
});
})();
