(function(){
const P = RT.products, $ = id => document.getElementById(id);
const CATS = ["Men","Women","Accessories","Bags","Jewelry","Bedroom"], COLS = [["men","Men's Collection"],["women","Women's Collection"],["accessories","Accessories Collection"]];
const st = {cat: RT.qs("cat") || "", sub: RT.qs("sub") || "", col: RT.qs("col") || "", q: RT.qs("q") || "", sort: "featured"};
if (!CATS.includes(st.cat)) st.cat = "";
$("shopSearch").value = st.q;

function renderFilters(){
  const cnt = c => P.filter(p => p.category === c).length;
  $("catList").innerHTML = `<button class="chip ${!st.cat?"active":""}" data-cat="">All Products <small>${P.length}</small></button>` + CATS.map(c => {
    const subs = [...new Set(P.filter(p => p.category === c).map(p => p.subcategory))];
    return `<button class="chip ${st.cat===c&&!st.sub?"active":""}" data-cat="${c}">${c} <small>${cnt(c)}</small></button>` +
      (st.cat === c && subs.length ? `<div class="subchips">${subs.map(s => `<button class="chip ${st.sub===s?"active":""}" data-cat="${c}" data-sub="${RT.esc(s)}">${RT.esc(s)}</button>`).join("")}</div>` : "");
  }).join("");
  $("colList").innerHTML = `<button class="chip ${!st.col?"active":""}" data-col="">All</button>` + COLS.map(([k,n]) => `<button class="chip ${st.col===k?"active":""}" data-col="${k}">${n}</button>`).join("");
}
function render(){
  const t = st.q.trim().toLowerCase();
  let r = P.filter(p => (!st.cat || p.category === st.cat) && (!st.sub || p.subcategory === st.sub) && (!st.col || p.collection === st.col) &&
     (!t || (p.name + " " + p.category + " " + p.subcategory + " " + p.collection).toLowerCase().includes(t)));
  if (st.sort === "plh") r.sort((a,b) => RT.price(a) - RT.price(b) || b.rating - a.rating);
  else if (st.sort === "phl") r.sort((a,b) => RT.price(b) - RT.price(a) || b.rating - a.rating);
  else if (st.sort === "rating") r.sort((a,b) => b.rating - a.rating);
  $("shopTitle").textContent = st.sub || st.cat || (st.col ? COLS.find(c => c[0] === st.col)?.[1] : "") || "Shop All";
  $("count").textContent = `Showing ${r.length} of ${P.length} product${P.length===1?"":"s"}`;
  $("grid").innerHTML = r.length ? r.map(RT.card).join("") : `<div class="empty" style="grid-column:1/-1">${RT.icon("search")}<h3>${t ? "No products match your search" : "No products here yet"}</h3><p>${t ? `Nothing found for “${RT.esc(st.q)}”. Try a different word or clear the filters.` : `${st.cat} products are coming soon.`}</p><button class="btn btn--dark" id="emptyClear">Clear filters</button></div>`;
  renderFilters();
  const u = new URLSearchParams(); ["cat","sub","col","q"].forEach(k => st[k] && u.set(k, st[k])); history.replaceState(null, "", u.toString() ? "?" + u : location.pathname);
}
$("filters").addEventListener("click", e => {
  const b = e.target.closest(".chip"); if (b){ if ("col" in b.dataset) st.col = b.dataset.col; else { st.cat = b.dataset.cat; st.sub = b.dataset.sub || ""; } render(); closeF(); }
  if (e.target.id === "clearFilters"){ Object.assign(st, {cat:"",sub:"",col:"",q:""}); $("shopSearch").value = ""; render(); closeF(); }
});
$("grid").addEventListener("click", e => { if (e.target.id === "emptyClear"){ Object.assign(st, {cat:"",sub:"",col:"",q:""}); $("shopSearch").value = ""; render(); } });
$("shopSearch").addEventListener("input", e => { st.q = e.target.value; render(); });
$("sort").addEventListener("change", e => { st.sort = e.target.value; render(); });
const openF = () => { $("filters").classList.add("open"); $("filterOverlay").classList.add("open"); }, closeF = () => { $("filters").classList.remove("open"); $("filterOverlay").classList.remove("open"); };
$("filterOpen").onclick = openF; $("filterClose").onclick = $("filterOverlay").onclick = closeF;
RT.fillIcons(); render();
})();
