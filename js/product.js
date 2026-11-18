(function(){
const p = RT.byId(RT.qs("id")), box = document.getElementById("detail");
if (!p){ box.innerHTML = `<div class="empty">${RT.icon("search")}<h3>Product not found</h3><p>This product may have been removed.</p><a class="btn btn--dark" href="shop.html">Back to Shop</a></div>`; document.getElementById("related").parentElement.hidden = true; return; }
document.title = `${p.name} | RT Fashion Hub`;
document.querySelector('meta[name="description"]').content = `${p.name} – ${p.category} / ${p.subcategory} at RT Fashion Hub. Rated ${p.rating}/5.`;
document.getElementById("pcrumbs").innerHTML = `<a href="index.html">Home</a> / <a href="shop.html">Shop</a> / <a href="shop.html?cat=${p.category}">${p.category}</a> / ${RT.esc(p.subcategory)}`;
const imgs = [p.image, ...(p.images || [])].filter(Boolean), sale = RT.isSale(p), on = RT.wish.has(p.id);
box.innerHTML = `<div class="pd"><div><div class="pd-main" id="pdMain">${RT.imgTag(p, imgs[0])}<span class="tags">${sale ? `<span class="tag tag--sale">Sale ${RT.off(p)}% off</span>` : ""}${p.badge ? `<span class="tag tag--gold">${RT.esc(p.badge)}</span>` : ""}</span></div>
  ${imgs.length > 1 ? `<div class="pd-thumbs">${imgs.map((s,i) => `<button class="${i?"":"on"}" data-src="${RT.esc(s)}" aria-label="Show image ${i+1}"><img src="${RT.esc(s)}" alt=""></button>`).join("")}</div>` : ""}</div>
 <div class="pd-info"><span class="pcat">${RT.esc(p.category)} · ${RT.esc(p.subcategory)}</span><h1>${RT.esc(p.name)}</h1>${RT.stars(p.rating)}
  <div class="price-row" style="margin-top:14px"><span class="price">${RT.fmt(RT.price(p))}</span>${sale ? `<span class="price-old">${RT.fmt(p.price)}</span><span class="off">${RT.off(p)}% off</span>` : ""}${RT.cfg.showPlaceholderPriceNotice ? `<span class="price-note">placeholder price</span>` : ""}</div>
  <p style="color:var(--muted);margin-top:16px">${RT.esc(p.description || "A quality pick from the RT Fashion Hub " + p.subcategory.toLowerCase() + " range.")}</p>
  <dl class="pd-meta"><dt>Category</dt><dd>${RT.esc(p.category)}</dd><dt>Type</dt><dd>${RT.esc(p.subcategory)}</dd><dt>Collection</dt><dd style="text-transform:capitalize">${RT.esc(p.collection)}</dd><dt>Customer rating</dt><dd>${p.rating} / 5</dd><dt>Product code</dt><dd>${p.id.toUpperCase()}</dd></dl>
  <div class="qty" id="pdQty"><button aria-label="Decrease">−</button><span>1</span><button aria-label="Increase">+</button></div>
  <div class="pd-buy"><button class="btn btn--outline" id="pdAdd">Add to Cart</button><button class="btn btn--dark" id="pdBuy">Buy Now</button><button class="wish ${on?"on":""}" style="position:static" data-wish="${p.id}" aria-label="Toggle wishlist">${RT.icon("heart")}</button></div>
  ${p.link ? `<p class="note">Original listing: <a class="link" href="${RT.esc(p.link)}" target="_blank" rel="noopener sponsored">View on Flipkart ↗</a></p>` : ""}</div></div>`;
let q = 1; const qs = box.querySelector("#pdQty"), [m, , pl] = qs.children;
m.onclick = () => qs.children[1].textContent = q = Math.max(1, q - 1); pl.onclick = () => qs.children[1].textContent = q = Math.min(20, q + 1);
document.getElementById("pdAdd").onclick = () => { RT.cart.add(p.id, q); RT.toast("Added to cart", "ok"); };
document.getElementById("pdBuy").onclick = () => { RT.cart.add(p.id, q); location.href = "checkout.html"; };
box.querySelector(".pd-thumbs")?.addEventListener("click", e => { const b = e.target.closest("button"); if (!b) return; box.querySelectorAll(".pd-thumbs button").forEach(x => x.classList.toggle("on", x === b)); box.querySelector("#pdMain img").src = b.dataset.src; });
const rel = RT.products.filter(x => x.id !== p.id && x.subcategory === p.subcategory).concat(RT.products.filter(x => x.id !== p.id && x.subcategory !== p.subcategory && x.collection === p.collection)).slice(0, 4);
document.getElementById("related").innerHTML = rel.map(RT.card).join("");
})();
