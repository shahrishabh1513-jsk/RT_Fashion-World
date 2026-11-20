/* RT FASHION HUB – front-end behaviour (the data lives in PHP/MySQL; this file only talks to /api/*.php) */
(function () {
  'use strict';
  const R = window.RT, $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const ICON = { trash: '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>', bag: '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>', eye: '<path d="M2 12s4-8 10-8 10 8 10 8-4 8-10 8S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>', eyeOff: '<path d="M17.9 17.9A10 10 0 0 1 12 20C6 20 2 12 2 12a18 18 0 0 1 5-5.9M9.9 4.2A9 9 0 0 1 12 4c6 0 10 8 10 8a18 18 0 0 1-2.2 3.2M1 1l22 22"/>' };
  const svg = n => `<svg class="ico" viewBox="0 0 24 24" aria-hidden="true">${ICON[n]}</svg>`;

  /* toasts */
  function toast(msg, type = '') {
    let box = $('.toasts'); if (!box) { box = document.createElement('div'); box.className = 'toasts'; box.setAttribute('aria-live', 'polite'); document.body.appendChild(box); }
    const t = document.createElement('div'); t.className = 'toast ' + type; t.textContent = msg; box.appendChild(t); setTimeout(() => t.remove(), 3800);
  }
  window.rtToast = toast; (R.flash || []).forEach(([m, t]) => toast(m, t));

  /* image fallback */
  window.rtImgFail = img => { if (img.dataset.f) return; img.dataset.f = 1; img.src = `${R.base}/placeholder.php?n=${encodeURIComponent(img.dataset.n || '')}&s=${encodeURIComponent(img.dataset.s || '')}`; };

  /* JSON API helper */
  async function api(path, body) {
    const r = await fetch(`${R.base}/api/${path}`, { method: body ? 'POST' : 'GET', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': R.csrf }, body: body ? JSON.stringify(body) : undefined, credentials: 'same-origin' });
    let j = {}; try { j = await r.json(); } catch (_) { } j._status = r.status; return j;
  }

  /* header: mobile menu, drawer, user menu */
  const nav = $('#mainNav'), ov = $('#overlay'), dr = $('#drawer');
  const closeAll = () => { nav.classList.remove('open'); dr.classList.remove('open'); ov.classList.remove('open'); dr.setAttribute('aria-hidden', 'true'); $('#menuBtn').setAttribute('aria-expanded', 'false'); document.body.style.overflow = ''; $('#filters')?.classList.remove('open'); };
  $('#menuBtn').onclick = () => { nav.classList.add('open'); ov.classList.add('open'); $('#menuBtn').setAttribute('aria-expanded', 'true'); document.body.style.overflow = 'hidden'; };
  $('#cartBtn').onclick = async () => { dr.classList.add('open'); dr.setAttribute('aria-hidden', 'false'); ov.classList.add('open'); document.body.style.overflow = 'hidden'; renderDrawer(await api('cart.php')); };
  $('#drawerClose').onclick = ov.onclick = closeAll; nav.addEventListener('click', e => e.target.closest('a') && closeAll());
  document.addEventListener('keydown', e => e.key === 'Escape' && closeAll());
  const ub = $('#userBtn'); if (ub) { ub.onclick = e => { e.stopPropagation(); ub.parentElement.classList.toggle('open'); }; document.addEventListener('click', () => ub.parentElement.classList.remove('open')); }

  function setCount(n) { const c = $('#cartCount'); c.textContent = n; c.style.display = n ? 'flex' : 'none'; }
  setCount(Number($('#cartCount').textContent));
  function renderDrawer(d) {
    setCount(d.count); const b = $('#drawerBody'), f = $('#drawerFoot');
    if (!d.lines || !d.lines.length) { b.innerHTML = `<div class="empty">${svg('bag')}<h3>Your cart is empty</h3><p>Discover something you'll love.</p></div>`; f.innerHTML = `<a class="btn btn--dark btn--block" href="${R.base}/shop.php">Continue Shopping</a>`; return; }
    b.innerHTML = d.lines.map(l => `<div class="mini-item"><a href="${l.url}"><img src="${esc(l.img)}" alt="" data-n="${esc(l.name)}" onerror="rtImgFail(this)"></a><div><h4>${esc(l.name)}</h4><div class="price">${esc(l.unit_fmt)}</div>
      <div class="qty" style="margin-top:8px"><button data-q="${l.id}" data-v="${l.qty - 1}" aria-label="Decrease">−</button><span>${l.qty}</span><button data-q="${l.id}" data-v="${l.qty + 1}" aria-label="Increase">+</button></div></div>
      <button class="icon-btn" data-rm="${l.id}" aria-label="Remove item">${svg('trash')}</button></div>`).join('');
    f.innerHTML = `<div class="row"><span>Subtotal</span><span>${esc(d.subtotal_fmt)}</span></div><a class="btn btn--outline btn--block" href="${R.base}/cart.php">View Cart</a><a class="btn btn--dark btn--block" href="${R.base}/checkout.php">Proceed to Checkout</a>`;
  }
  $('#drawerBody').addEventListener('click', async e => {
    const q = e.target.closest('[data-q]'), r = e.target.closest('[data-rm]');
    if (q) renderDrawer(await api('cart.php', { action: 'set', id: q.dataset.q, qty: +q.dataset.v })); else if (r) renderDrawer(await api('cart.php', { action: 'remove', id: r.dataset.rm }));
  });

  /* product-card buttons (works on every page) */
  document.addEventListener('click', async e => {
    const a = e.target.closest('[data-add]'), b = e.target.closest('[data-buy]'), w = e.target.closest('[data-wish]');
    if (a || b) {
      const id = (a || b).dataset.add || (a || b).dataset.buy, qty = Number($('#pdQtyVal')?.textContent || 1);
      const d = await api('cart.php', { action: 'add', id, qty: $('#pdQtyVal') && (a || b).closest('.pd-info') ? qty : 1 });
      if (!d.ok) return toast(d.error || 'Could not add to cart', 'err');
      setCount(d.count); if (b) location.href = `${R.base}/checkout.php`; else toast('Added to cart', 'ok');
    } else if (w) {
      const d = await api('wishlist.php', { id: w.dataset.wish });
      if (d._status === 401) { location.href = d.login; return; }
      if (!d.ok) return toast(d.error || 'Could not update wishlist', 'err');
      $$(`[data-wish="${w.dataset.wish}"]`).forEach(x => { x.classList.toggle('on', d.on); x.setAttribute('aria-pressed', d.on); });
      toast(d.on ? 'Saved to wishlist' : 'Removed from wishlist');
    }
  });

  /* live search suggestions */
  $$('.search-form').forEach(form => {
    const inp = $('input', form), box = $('.search-suggest', form); let t;
    inp.addEventListener('input', () => { clearTimeout(t); const v = inp.value.trim(); if (v.length < 2) { box.classList.remove('open'); return; }
      t = setTimeout(async () => { const r = await fetch(`${R.base}/api/search.php?q=${encodeURIComponent(v)}`).then(x => x.json()).catch(() => []);
        box.innerHTML = r.length ? r.map(p => `<a href="${p.url}"><img src="${esc(p.img)}" alt=""><span>${esc(p.name)}<small>${esc(p.cat)}</small></span></a>`).join('') : '<div style="padding:14px;font-size:.85rem;color:var(--muted)">No matching products</div>'; box.classList.add('open'); }, 200); });
    document.addEventListener('click', e => !form.contains(e.target) && box.classList.remove('open'));
  });

  /* FAQ accordion */
  $$('.faq-item').forEach(it => { const b = $('.faq-q', it), a = $('.faq-a', it);
    b.addEventListener('click', () => { const open = !it.classList.contains('open');
      $$('.faq-item.open').forEach(o => { o.classList.remove('open'); $('.faq-q', o).setAttribute('aria-expanded', 'false'); $('.faq-a', o).style.maxHeight = 0; });
      if (open) { it.classList.add('open'); b.setAttribute('aria-expanded', 'true'); a.style.maxHeight = a.scrollHeight + 'px'; } }); });

  /* password show/hide */
  $$('[data-toggle]').forEach(b => { b.innerHTML = svg('eye'); b.addEventListener('click', () => { const i = document.getElementById(b.dataset.toggle), s = i.type === 'password'; i.type = s ? 'text' : 'password'; b.innerHTML = svg(s ? 'eyeOff' : 'eye'); b.setAttribute('aria-label', s ? 'Hide password' : 'Show password'); }); });

  /* login / register tabs */
  const tl = $('#tabLogin'), tr = $('#tabReg');
  if (tl && tr) { const show = reg => { $('#loginForm').hidden = reg; $('#regForm').hidden = !reg; tl.classList.toggle('on', !reg); tr.classList.toggle('on', reg); $('#authTitle').textContent = reg ? 'Create your account' : 'Welcome back'; };
    tl.onclick = () => show(false); tr.onclick = () => show(true); $('#toReg')?.addEventListener('click', e => { e.preventDefault(); show(true); }); $('#toLogin')?.addEventListener('click', e => { e.preventDefault(); show(false); });
    show(document.body.dataset.tab === 'register'); }

  /* shop filter drawer (mobile) */
  $('#filterOpen')?.addEventListener('click', () => { $('#filters').classList.add('open'); $('#filterOverlay').classList.add('open'); });
  $$('#filterClose, #filterOverlay').forEach(x => x.addEventListener('click', () => { $('#filters').classList.remove('open'); $('#filterOverlay').classList.remove('open'); }));
  $('#sort')?.addEventListener('change', e => e.target.form.submit());

  /* product page quantity stepper */
  const pq = $('#pdQty'); if (pq) { const v = $('#pdQtyVal'); const [m, , p] = pq.children; m.onclick = () => v.textContent = Math.max(1, +v.textContent - 1); p.onclick = () => v.textContent = Math.min(20, +v.textContent + 1); }

  /* confirm dialogs on destructive forms */
  $$('form[data-confirm]').forEach(f => f.addEventListener('submit', e => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  /* LIVE order tracking: poll the server; refresh the page when the status changes */
  const tr2 = $('#trackRoot'); if (tr2) {
    let sig = tr2.dataset.sig; const q = `no=${encodeURIComponent(tr2.dataset.no)}&email=${encodeURIComponent(tr2.dataset.email || '')}`;
    const tick = async () => { if (document.hidden) return; const d = await fetch(`${R.base}/api/order_status.php?${q}`).then(x => x.json()).catch(() => null);
      if (d && d.ok) { const s = [d.status, d.tracking, d.history.length].join('|'); if (s !== sig) { toast('Order status updated: ' + d.status, 'ok'); setTimeout(() => location.reload(), 900); sig = s; } } };
    setInterval(tick, 5000); document.addEventListener('visibilitychange', tick);
  }

  /* back to top + reveal animation */
  const tt = $('#toTop'); window.addEventListener('scroll', () => tt.classList.toggle('show', scrollY > 500), { passive: true }); tt.onclick = () => scrollTo({ top: 0, behavior: 'smooth' });
  const io = new IntersectionObserver(es => es.forEach(x => x.isIntersecting && (x.target.classList.add('in'), io.unobserve(x.target))), { threshold: .12 }); $$('.reveal').forEach(el => io.observe(el));
  $$('[data-ico]').forEach(el => { /* static icons used in page markup */ });
})();
