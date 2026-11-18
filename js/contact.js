(function(){
/* FAQ accordion */
document.querySelectorAll(".faq-item").forEach(it => {
  const b = it.querySelector(".faq-q"), a = it.querySelector(".faq-a");
  b.addEventListener("click", () => { const open = !it.classList.contains("open");
    document.querySelectorAll(".faq-item.open").forEach(o => { o.classList.remove("open"); o.querySelector(".faq-q").setAttribute("aria-expanded","false"); o.querySelector(".faq-a").style.maxHeight = 0; });
    if (open){ it.classList.add("open"); b.setAttribute("aria-expanded","true"); a.style.maxHeight = a.scrollHeight + "px"; } });
});

/* Contact form */
const f = document.getElementById("contactForm"), out = document.getElementById("cMsgOut"), btn = document.getElementById("cSubmit");
const rules = { cName: v => v.length < 2 ? "Please enter your name." : "", cEmail: v => !v ? "Please enter your email." : !RT.validEmail(v) ? "Enter a valid email address." : "",
  cSubject: v => v.length < 3 ? "Please add a subject." : "", cMsg: v => v.length < 10 ? "Message should be at least 10 characters." : "" };
const check = el => { const m = rules[el.id](el.value.trim()), fld = el.closest(".field"); fld.classList.toggle("invalid", !!m); fld.querySelector(".err").textContent = m; return !m; };
Object.keys(rules).forEach(id => document.getElementById(id).addEventListener("blur", e => check(e.target)));
f.addEventListener("submit", async e => {
  e.preventDefault(); out.className = "form-msg"; out.textContent = "";
  const ok = Object.keys(rules).map(id => check(document.getElementById(id))).every(Boolean);
  if (!ok){ out.classList.add("err"); out.textContent = "Please fix the highlighted fields."; return; }
  const d = {name:cName.value.trim(), email:cEmail.value.trim(), subject:cSubject.value.trim(), message:cMsg.value.trim()};
  if (!RT.cfg.contactEndpoint){
    const href = `mailto:${RT.cfg.email}?subject=${encodeURIComponent(d.subject)}&body=${encodeURIComponent(d.message + "\n\n— " + d.name + " (" + d.email + ")")}`;
    out.classList.add("info"); out.innerHTML = `Your message was <b>not sent</b> – the website's message service isn't connected yet. <a class="link" href="${href}">Open it in your email app</a> to send it to ${RT.esc(RT.cfg.email)}.`; return; }
  btn.disabled = true; btn.textContent = "Sending…";
  try { const r = await fetch(RT.cfg.contactEndpoint, {method:"POST", headers:{"Content-Type":"application/json", Accept:"application/json"}, body: JSON.stringify(d)}); if (!r.ok) throw 0;
    out.classList.add("ok"); out.textContent = "Thank you! Your message has been sent. We'll reply soon."; f.reset(); }
  catch(_){ out.classList.add("err"); out.textContent = "Sorry, we couldn't send your message. Please try again or email us directly."; }
  btn.disabled = false; btn.textContent = "Submit";
});
})();
