(function(){
const $ = id => document.getElementById(id), next = RT.qs("next");
const safeNext = /^[a-z]+\.html([?#].*)?$/i.test(next || "") ? next : "account.html";
if (RT.auth.user()) location.replace(safeNext);
RT.fillIcons();
const show = which => { const reg = which === "register"; $("loginForm").hidden = reg; $("regForm").hidden = !reg; $("tabLogin").classList.toggle("on", !reg); $("tabReg").classList.toggle("on", reg);
  $("tabLogin").setAttribute("aria-selected", !reg); $("tabReg").setAttribute("aria-selected", reg); $("authTitle").textContent = reg ? "Create your account" : "Welcome back"; };
$("tabLogin").onclick = () => { show("login"); history.replaceState(null,"","#login"); }; $("tabReg").onclick = () => { show("register"); history.replaceState(null,"","#register"); };
$("toReg").onclick = e => { e.preventDefault(); $("tabReg").click(); }; $("toLogin").onclick = e => { e.preventDefault(); $("tabLogin").click(); };
show(location.hash === "#register" ? "register" : "login");

document.querySelectorAll("[data-toggle]").forEach(b => b.addEventListener("click", () => { const i = $(b.dataset.toggle), show = i.type === "password"; i.type = show ? "text" : "password"; b.innerHTML = RT.icon(show ? "eyeOff" : "eye"); b.setAttribute("aria-label", show ? "Hide password" : "Show password"); }));
const mark = (el, msg) => { const f = el.closest(".field"); f.classList.toggle("invalid", !!msg); f.querySelector(".err").textContent = msg || ""; return !msg; };
const msg = (el, t, c) => { el.className = "form-msg " + c; el.textContent = t; };

$("loginForm").addEventListener("submit", async e => {
  e.preventDefault(); const em = $("lEmail"), pw = $("lPass"), out = $("lOut"); msg(out, "", "");
  const ok = [mark(em, !em.value.trim() ? "Enter your email." : !RT.validEmail(em.value.trim()) ? "Enter a valid email." : ""), mark(pw, pw.value ? "" : "Enter your password.")].every(Boolean); if (!ok) return;
  const btn = e.target.querySelector("button[type=submit]"); btn.disabled = true;
  try { const u = await RT.auth.login(em.value, pw.value, $("lRemember").checked); msg(out, `Welcome back, ${u.name.split(" ")[0]}! Redirecting…`, "ok"); setTimeout(() => location.href = safeNext, 600); }
  catch(err){ msg(out, err.message, "err"); btn.disabled = false; }
});
$("regForm").addEventListener("submit", async e => {
  e.preventDefault(); const out = $("rOut"); msg(out, "", ""); const n = $("rName"), em = $("rEmail"), mo = $("rMobile"), p1 = $("rPass"), p2 = $("rPass2");
  const ok = [mark(n, n.value.trim().length < 2 ? "Enter your full name." : ""), mark(em, !RT.validEmail(em.value.trim()) ? "Enter a valid email." : ""), mark(mo, !RT.validPhone(mo.value) ? "Enter a valid 10-digit Indian mobile number." : ""),
    mark(p1, RT.pwProblem(p1.value)), mark(p2, p1.value !== p2.value ? "Passwords do not match." : "")].every(Boolean);
  const t = $("rTerms").checked; $("rTermsErr").textContent = t ? "" : "Please accept the Terms & Conditions."; if (!ok || !t) return;
  const btn = e.target.querySelector("button[type=submit]"); btn.disabled = true;
  try { const u = await RT.auth.register({name:n.value, email:em.value, mobile:mo.value, password:p1.value, address:$("rAddr").value}); await RT.auth.login(em.value, p1.value, false);
    msg(out, `Account created! Your customer ID is ${u.customerId}. Redirecting…`, "ok"); setTimeout(() => location.href = safeNext, 1100); }
  catch(err){ msg(out, err.message, "err"); btn.disabled = false; }
});
/* forgot password (demo) */
const modal = $("forgotModal"); $("forgotLink").onclick = e => { e.preventDefault(); modal.classList.add("open"); $("fEmail").value = $("lEmail").value; $("fEmail").focus(); };
$("fClose").onclick = () => modal.classList.remove("open"); modal.addEventListener("click", e => e.target === modal && modal.classList.remove("open"));
$("forgotForm").addEventListener("submit", async e => { e.preventDefault(); const out = $("fOut"), em = $("fEmail").value, pw = $("fPass").value;
  if (!RT.validEmail(em)) return msg(out, "Enter a valid email.", "err"); const pr = RT.pwProblem(pw); if (pr) return msg(out, pr, "err");
  try { await RT.auth.demoReset(em, pw); msg(out, "Password updated. You can log in now.", "ok"); setTimeout(() => modal.classList.remove("open"), 1200); } catch(err){ msg(out, err.message, "err"); } });
})();
