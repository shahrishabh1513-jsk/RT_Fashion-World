(function(){
const $ = id => document.getElementById(id), box = $("trackResult"), E = RT.esc, S = RT.orders.STEPS;
const dtm = d => new Date(d).toLocaleString("en-IN", {day:"numeric", month:"short", hour:"numeric", minute:"2-digit"});
let cur = null, last = "";
function render(){
  const o = cur && RT.orders.all().find(x => x.id === cur.id); if (!o){ box.innerHTML = ""; return; }
  const sig = JSON.stringify([o.status, o.tracking, o.history]); if (sig === last) return; last = sig;
  const i = RT.orders.step(o), canc = o.status === "Cancelled", hist = o.history || [{status:o.status, at:o.date}];
  const when = s => { const h = [...hist].reverse().find(x => x.status === s); return h ? dtm(h.at) : ""; };
  box.innerHTML = `<div class="panel" style="margin-top:28px"><div class="order-head" style="border-radius:10px;margin-bottom:22px"><span><b>${o.id}</b></span><span>Placed ${new Date(o.date).toLocaleDateString("en-IN")}</span><span class="status ${canc ? "" : "status--pending"}">${E(o.status)}</span><b>${RT.fmt(o.total)}</b></div>
  ${canc ? `<div class="alert alert--err">This order was cancelled.</div>` : ""}
  <ol class="track ${canc ? "cancelled" : ""}" aria-label="Order progress">${S.map((s, k) => `<li class="${canc ? "" : k < i ? "done" : k === i ? "now" : ""}"><span class="dot">${!canc && k <= i ? RT.icon("check") : k + 1}</span><b>${s}</b><small>${canc ? "" : when(s)}</small></li>`).join("")}</ol>
  <div class="kv" style="margin:24px 0">${kv("Tracking / courier details", E(o.tracking) || "Not added yet")}${kv("Shipping to", E(o.address))}${kv("Payment", E(o.payment) + " · " + E(o.paymentStatus))}${kv("Last updated", dtm(hist[hist.length-1].at))}</div>
  <h3 style="font-size:1.1rem;font-family:var(--font);margin-bottom:10px">Activity</h3><ul class="log">${[...hist].reverse().map(h => `<li><b>${E(h.status)}</b> <small>${dtm(h.at)}</small>${h.note ? `<br><span style="color:var(--muted)">${E(h.note)}</span>` : ""}</li>`).join("")}</ul>
  <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:22px"><button class="btn btn--dark" id="dlBill">Download Bill (PDF)</button><a class="btn btn--outline" href="contact.html">Need help?</a></div>
  <p class="note"><span class="live-dot"></span> Live: this page refreshes automatically. Status changes are made by the store owner in this same browser (demo) – there is no courier integration yet.</p></div>`;
  RT.fillIcons(box); $("dlBill").onclick = () => RT.invoice.download(o);
}
const kv = (k, v) => `<div><small>${k}</small><b>${v || "—"}</b></div>`;
function lookup(id, email, quiet){
  const o = RT.orders.find(id, email); const out = $("tOut");
  if (!o){ cur = null; last = ""; box.innerHTML = ""; if (!quiet){ out.className = "form-msg err"; out.textContent = "No order found for that order number and email on this device."; } return; }
  out.textContent = ""; cur = o; last = ""; render();
}
$("trackForm").addEventListener("submit", e => { e.preventDefault(); const id = $("tId").value.trim(), em = $("tEmail").value.trim(), out = $("tOut");
  if (!id || !RT.validEmail(em)){ out.className = "form-msg err"; out.textContent = "Enter your order number and a valid email."; return; } lookup(id, em); });
const u = RT.auth.user(), qid = RT.qs("id"); if (u) $("tEmail").value = u.email;
if (qid){ $("tId").value = qid; const o = RT.orders.find(qid); if (o && u && o.userId === u.id) lookup(qid, u.email, true); }
setInterval(render, 4000); window.addEventListener("storage", render); document.addEventListener("visibilitychange", render);
})();
