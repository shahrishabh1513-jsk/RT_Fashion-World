(function(){
const P = RT.products, top = (arr, n) => [...arr].sort((a,b) => b.rating - a.rating).slice(0, n);
document.getElementById("statProducts").textContent = P.length;
/* Trending = products flagged badge:"Trending" first, topped up with the highest-rated to fill two rows of four */
const flagged = P.filter(p => p.badge === "Trending"), rest = top(P.filter(p => p.badge !== "Trending"), 8);
document.getElementById("trending").innerHTML = [...flagged, ...rest].slice(0, 8).map(RT.card).join("");

const defs = [["collectionMen","Men's Collection","Tees, shirts, denim and more for him.","men"],["collectionWomen","Women's Collection","Dresses, skirts, tops and denim for her.","women"],["collectionAcc","Accessories Collection","Watches, jewelry, bags and everyday accessories.","accessories"]];
defs.forEach(([id,title,txt,col]) => {
  const items = top(P.filter(p => p.collection === col), 3);
  document.getElementById(id).innerHTML = `<div class="collection reveal"><div class="collection-intro"><span class="eyebrow" style="color:var(--accent)">Collection</span><h3>${title}</h3><p>${txt}</p><a class="btn btn--gold btn--sm" href="shop.html?col=${col}">Shop Collection</a></div><div class="product-grid">${items.map(RT.card).join("")}</div></div>`;
});
RT.reveal();

/* newsletter */
const f = document.getElementById("newsForm"), out = document.getElementById("newsMsg"), inp = document.getElementById("newsEmail");
f.addEventListener("submit", async e => {
  e.preventDefault(); const v = inp.value.trim(); out.className = "form-msg";
  if (!v){ out.classList.add("err"); out.textContent = "Please enter your email address."; return; }
  if (!RT.validEmail(v)){ out.classList.add("err"); out.textContent = "That email address doesn't look right."; return; }
  if (!RT.cfg.newsletterEndpoint){ out.classList.add("info"); out.textContent = "Thanks! Your email looks valid, but newsletter delivery isn't connected yet, so nothing was sent."; return; }
  try { const r = await fetch(RT.cfg.newsletterEndpoint, {method:"POST", headers:{"Content-Type":"application/json", Accept:"application/json"}, body: JSON.stringify({email:v})});
    if (!r.ok) throw 0; out.classList.add("ok"); out.textContent = "You're subscribed. Welcome to RT Fashion Hub!"; f.reset(); }
  catch(_){ out.classList.add("err"); out.textContent = "Couldn't subscribe right now. Please try again later."; }
});
})();
