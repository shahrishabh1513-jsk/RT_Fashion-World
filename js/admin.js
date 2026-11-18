(function () {
  const $ = id => document.getElementById(id), E = RT.esc, S = [...RT.orders.STEPS, "Cancelled"], main = $("adminMain");
  if (!RT.cfg.adminPin) { main.innerHTML = `<div class="alert alert--warn">Owner order management is switched off. Set <code>adminPin</code> in <code>js/config.js</code> to enable it. This is a demo tool – a PIN in front-end code is not real security.</div>`; return; }
  const ok = () => sessionStorage.getItem("rt_admin") === "1";
  function gate() {
    main.innerHTML = `<form class="form-card" id="pinForm" style="max-width:380px;margin:auto"><div class="field"><label for="pin">Owner PIN</label><input id="pin" type="password" autocomplete="off"><span class="err"></span></div><button class="btn btn--dark btn--block" style="margin-top:14px">Open</button></form>`;
    $("pinForm").onsubmit = e => { e.preventDefault(); if ($("pin").value === RT.cfg.adminPin) { sessionStorage.setItem("rt_admin", "1"); list(); } else $("pin").closest(".field").querySelector(".err").textContent = "Incorrect PIN."; };
  }
  function list() {
    const all = RT.orders.all().slice().reverse();
    main.innerHTML = `<div class="alert alert--warn"><b>Demo owner tool.</b> Edits apply to orders stored in this browser only. Customers see changes live on the Order Tracking page (same browser).</div>` +
      (all.length ? all.map(o => `<div class="order" data-o="${o.id}"><div class="order-head"><b>${o.id}</b><span>${E(o.customer.name)} · ${E(o.customer.email)}</span><span>${new Date(o.date).toLocaleDateString("en-IN")}</span><b>${RT.fmt(o.total)}</b></div>
   <div class="order-body"><div class="form-grid"><div class="field"><label>Status</label><select class="st">${S.map(s => `<option ${s === (RT.orders.STEPS.includes(o.status) || o.status === "Cancelled" ? o.status : "Order Placed") ? "selected" : ""}>${s}</option>`).join("")}</select></div>
   <div class="field"><label>Tracking / courier no.</label><input class="tr" value="${E(o.tracking)}" placeholder="e.g. Courier name + AWB"></div><div class="field full"><label>Note shown to customer (optional)</label><input class="nt"></div></div>
   <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap"><button class="btn btn--dark btn--sm sv">Save update</button><button class="btn btn--outline btn--sm bill">Download Bill</button></div></div></div>`).join("") : `<div class="empty"><h3>No orders yet</h3></div>`) + `<button class="btn btn--outline btn--sm" id="lock">Lock</button>`;
    main.querySelectorAll("[data-o]").forEach(el => {
      const id = el.dataset.o;
      el.querySelector(".sv").onclick = () => { RT.orders.update(id, { status: el.querySelector(".st").value, tracking: el.querySelector(".tr").value.trim(), note: el.querySelector(".nt").value.trim() }); RT.toast("Order updated", "ok"); list(); };
      el.querySelector(".bill").onclick = () => RT.invoice.download(RT.orders.all().find(x => x.id === id));
    });
    $("lock")?.addEventListener("click", () => { sessionStorage.removeItem("rt_admin"); gate(); });
  }
  ok() ? list() : gate();
})();
