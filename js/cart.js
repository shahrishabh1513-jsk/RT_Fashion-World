(function () {
  const main = document.getElementById("cartMain");
  function render() {
    const L = RT.cart.lines();
    if (!L.length) { main.innerHTML = `<div class="empty">${RT.icon("bag")}<h3>Your cart is empty</h3><p>Looks like you haven't added anything yet.</p><a class="btn btn--dark" href="shop.html">Continue Shopping</a></div>`; return; }
    const t = RT.cart.totals(L);
    main.innerHTML = `<div class="cart-layout"><div>${L.map(l => `<div class="cart-row"><a href="product.html?id=${l.p.id}">${RT.imgTag(l.p)}</a>
    <div><span class="pcat">${RT.esc(l.p.category)}</span><h3><a href="product.html?id=${l.p.id}">${RT.esc(l.p.name)}</a></h3><div style="color:var(--muted);font-size:.88rem;margin-top:4px">Unit price: ${RT.fmt(RT.price(l.p))}</div>
     <div class="controls"><div class="qty"><button data-q="${l.id}" data-d="-1" aria-label="Decrease quantity">−</button><span>${l.qty}</span><button data-q="${l.id}" data-d="1" aria-label="Increase quantity">+</button></div><button class="remove-link" data-rm="${l.id}">Remove</button></div></div>
    <div class="line-total price">${RT.fmt(RT.price(l.p) * l.qty)}</div></div>`).join("")}
    <div style="margin-top:22px"><a class="btn btn--outline" href="shop.html">← Continue Shopping</a></div></div>
   <aside class="summary"><h3>Order Summary</h3><div class="totals"><div class="row"><span>Subtotal</span><span>${RT.fmt(t.subtotal)}</span></div><div class="row"><span>Delivery</span><span>${t.shipping ? RT.fmt(t.shipping) : "Free"}</span></div><div class="row"><span>GST (${RT.cfg.gstRate * 100}%)</span><span>${RT.fmt(t.gst)}</span></div><div class="row grand"><span>Total</span><span>${RT.fmt(t.total)}</span></div></div>
   <a class="btn btn--dark btn--block" style="margin-top:20px" href="checkout.html">Proceed to Checkout</a></aside></div>`;
  }
  main.addEventListener("click", e => {
    const q = e.target.closest("[data-q]"), r = e.target.closest("[data-rm]");
    if (q) { const l = RT.cart.get().find(i => i.id === q.dataset.q); RT.cart.setQty(q.dataset.q, l.qty + Number(q.dataset.d)); } else if (r) { RT.cart.remove(r.dataset.rm); RT.toast("Item removed"); }
  });
  document.addEventListener("rt:cart", render); render();
})();
