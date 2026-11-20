/* Original-style PDF bill generator (jsPDF, bundled in js/vendor). Data comes from PHP (window.RT_ORDER / RT_BILL). */
(function(){
const C = window.RT_BILL, INK = [23,23,23], GOLD = [198,164,106], WARM = [248,246,243], MUTED = [119,119,119];
const money = n => "Rs. " + Number(n).toLocaleString("en-IN", {minimumFractionDigits: 2, maximumFractionDigits: 2});
function words(n){
  const a = ["","One","Two","Three","Four","Five","Six","Seven","Eight","Nine","Ten","Eleven","Twelve","Thirteen","Fourteen","Fifteen","Sixteen","Seventeen","Eighteen","Nineteen"], b = ["","","Twenty","Thirty","Forty","Fifty","Sixty","Seventy","Eighty","Ninety"];
  const two = x => x < 20 ? a[x] : b[Math.floor(x/10)] + (x%10 ? " " + a[x%10] : "");
  const three = x => (x >= 100 ? a[Math.floor(x/100)] + " Hundred" + (x%100 ? " " : "") : "") + two(x%100);
  const r = Math.floor(n), p = Math.round((n - r) * 100); if (!r && !p) return "Zero Rupees Only";
  let out = "", x = r; const parts = [[10000000,"Crore"],[100000,"Lakh"],[1000,"Thousand"]];
  parts.forEach(([v,l]) => { if (x >= v){ out += three(Math.floor(x/v)) + " " + l + " "; x %= v; } }); if (x) out += three(x);
  return (out.trim() || "Zero") + " Rupees" + (p ? " and " + two(p) + " Paise" : "") + " Only";
}
let logoCache;
function logo(){ return logoCache || (logoCache = new Promise(res => { const i = new Image(); i.onload = () => { try { const c = document.createElement("canvas"); c.width = i.naturalWidth; c.height = i.naturalHeight; c.getContext("2d").drawImage(i, 0, 0); res({data:c.toDataURL("image/png"), w:i.naturalWidth, h:i.naturalHeight}); } catch (e) { res(null); } }; i.onerror = () => res(null); i.src = window.RT_LOGO || LOGO_URL; })); }
const LOGO_URL = C.logo;
const dt = d => new Date(d).toLocaleDateString("en-IN", {day:"2-digit", month:"short", year:"numeric"});

window.RTInvoice = { async download(o){
  if (!window.jspdf) { window.rtToast("PDF library failed to load.", "err"); return; }
  const doc = new window.jspdf.jsPDF({unit:"mm", format:"a4"}), W = 210, H = 297, M = 14, lg = await logo();
  const fill = c => doc.setFillColor(...c), txt = c => doc.setTextColor(...c), draw = c => doc.setDrawColor(...c);
  const invNo = "INV-" + o.id.replace("RT-ORD-","");

  function frame(){ /* corner ornaments + footer, repeated on every page */
    fill(GOLD); doc.triangle(W, 0, W - 38, 0, W, 38, "F"); fill(INK); doc.triangle(W, 0, W - 22, 0, W, 22, "F");
    fill(INK); doc.rect(0, H - 22, W, 22, "F"); fill(GOLD); doc.rect(0, H - 22, W, 1.4, "F"); fill(GOLD); doc.triangle(0, H - 22, 26, H - 22, 0, H - 48, "F");
    txt([255,255,255]); doc.setFont("helvetica","bold"); doc.setFontSize(8.5); doc.text("RT FASHION HUB", W/2, H - 14.5, {align:"center"});
    doc.setFont("helvetica","normal"); doc.setFontSize(7.5); txt([205,205,205]);
    doc.text(`${C.email}   |   ${C.phone}   |   ${C.location}`, W/2, H - 10, {align:"center"});
    txt(GOLD); doc.text("Style That Defines You.  -  This is a computer-generated bill.", W/2, H - 5.5, {align:"center"});
  }
  frame();
  /* header */
  if (lg){ const w = 46, h = w * lg.h / lg.w; doc.addImage(lg.data, "PNG", M, 10, w, h); } else { doc.setFont("times","bold"); doc.setFontSize(20); txt(INK); doc.text("RT FASHION HUB", M, 20); }
  doc.setFont("times","bold"); doc.setFontSize(24); txt(INK); doc.text("INVOICE", W - M - 24, 22, {align:"right"});
  doc.setFont("helvetica","normal"); doc.setFontSize(8.5); txt(MUTED);
  doc.text(`Invoice No:  ${invNo}`, W - M - 24, 29, {align:"right"}); doc.text(`Date:  ${dt(o.date)}`, W - M - 24, 34, {align:"right"}); doc.text(`Order No:  ${o.id}`, W - M - 24, 39, {align:"right"});
  draw(GOLD); doc.setLineWidth(.7); doc.line(M, 46, W - M, 46);

  /* parties */
  const box = (x, y, w, title, lines) => { fill(WARM); doc.roundedRect(x, y, w, 36, 2, 2, "F"); fill(GOLD); doc.rect(x, y, 1.4, 36, "F");
    doc.setFont("helvetica","bold"); doc.setFontSize(7.5); txt(GOLD); doc.text(title.toUpperCase(), x + 5, y + 6); doc.setFont("helvetica","normal"); doc.setFontSize(8.5); txt(INK); let yy = y + 12;
    lines.forEach((l, i) => { const sp = doc.splitTextToSize(l, w - 9); if (i === 0){ doc.setFont("helvetica","bold"); } else doc.setFont("helvetica","normal"); sp.forEach(s => { if (yy < y + 35){ doc.text(s, x + 5, yy); yy += 4.3; } }); }); };
  const bw = (W - 2*M - 8) / 3;
  box(M, 52, bw, "Sold by", ["RT Fashion Hub", C.address, C.gstin ? "GSTIN: " + C.gstin : ""].filter(Boolean));
  box(M + bw + 4, 52, bw, "Bill to", [o.customer.name, o.customer.email, o.customer.mobile]);
  box(M + 2*(bw + 4), 52, bw, "Ship to", [o.customer.name, o.address]);

  /* items table */
  const col = {n:M + 3, d:M + 12, q:W - M - 62, r:W - M - 34, a:W - M - 3};
  let y = 96;
  const head = yy => { fill(INK); doc.rect(M, yy, W - 2*M, 8, "F"); txt([255,255,255]); doc.setFont("helvetica","bold"); doc.setFontSize(8);
    doc.text("#", col.n, yy + 5.3); doc.text("ITEM DESCRIPTION", col.d, yy + 5.3); doc.text("QTY", col.q, yy + 5.3, {align:"center"}); doc.text("RATE", col.r, yy + 5.3, {align:"right"}); doc.text("AMOUNT", col.a, yy + 5.3, {align:"right"}); };
  head(y); y += 8;
  o.items.forEach((it, i) => {
    const nm = doc.splitTextToSize(it.name, col.q - col.d - 10), rh = Math.max(9, nm.length * 4.2 + 4);
    if (y + rh > H - 60){ doc.addPage(); frame(); y = 18; head(y); y += 8; }
    if (i % 2 === 0){ fill(WARM); doc.rect(M, y, W - 2*M, rh, "F"); }
    doc.setFont("helvetica","normal"); doc.setFontSize(8.5); txt(INK);
    doc.text(String(i + 1), col.n, y + 5.6); doc.text(nm, col.d, y + 5.6); doc.text(String(it.qty), col.q, y + 5.6, {align:"center"}); doc.text(money(it.price), col.r, y + 5.6, {align:"right"}); doc.text(money(it.price * it.qty), col.a, y + 5.6, {align:"right"});
    y += rh; });
  draw(GOLD); doc.setLineWidth(.4); doc.line(M, y, W - M, y); y += 6;

  /* totals + payment */
  if (y > H - 100){ doc.addPage(); frame(); y = 18; }
  const tx = W - M - 78; let ty = y;
  [["Subtotal", money(o.subtotal)], ["Delivery charges", o.shipping ? money(o.shipping) : "Free"], [`GST (${Math.round(C.gstRate*100)}%)`, money(o.gst)]].forEach(([k, v]) => { doc.setFont("helvetica","normal"); doc.setFontSize(9); txt(MUTED); doc.text(k, tx, ty + 4); txt(INK); doc.text(v, W - M - 3, ty + 4, {align:"right"}); ty += 6.5; });
  fill(INK); doc.roundedRect(tx - 3, ty + 1, 81, 11, 1.5, 1.5, "F"); txt(GOLD); doc.setFont("helvetica","bold"); doc.setFontSize(10.5); doc.text("GRAND TOTAL", tx, ty + 8.2); txt([255,255,255]); doc.text(money(o.total), W - M - 3, ty + 8.2, {align:"right"});
  /* payment box left */
  fill(WARM); doc.roundedRect(M, y, 92, 30, 2, 2, "F"); doc.setFont("helvetica","bold"); doc.setFontSize(7.5); txt(GOLD); doc.text("PAYMENT DETAILS", M + 5, y + 6);
  doc.setFont("helvetica","normal"); doc.setFontSize(8.5); txt(INK); doc.text(`Method: ${o.payment}`, M + 5, y + 12); doc.text(`Payment status: ${o.paymentStatus}`, M + 5, y + 17); doc.text(`Order status: ${o.status}`, M + 5, y + 22); if (o.tracking) doc.text(`Tracking: ${o.tracking}`, M + 5, y + 27);
  y = Math.max(ty + 16, y + 34);
  doc.setFont("helvetica","bold"); doc.setFontSize(8); txt(INK); doc.text("Amount in words:", M, y); doc.setFont("helvetica","italic"); txt(MUTED); doc.text(doc.splitTextToSize(words(o.total), W - 2*M - 30), M + 29, y);
  y += 12; doc.setFont("helvetica","bold"); txt(INK); doc.text("Terms & notes", M, y); doc.setFont("helvetica","normal"); doc.setFontSize(7.8); txt(MUTED); doc.text(doc.splitTextToSize(C.billTerms + " Track your order at the Order Tracking page using your order number and email.", 110), M, y + 4.5);
  /* signature */
  const sy = Math.min(y + 14, H - 40); draw(INK); doc.setLineWidth(.3); doc.line(W - M - 58, sy + 8, W - M, sy + 8);
  doc.setFont("helvetica","bold"); doc.setFontSize(8); txt(INK); doc.text("For RT FASHION HUB", W - M - 29, sy, {align:"center"}); doc.setFont("helvetica","normal"); txt(MUTED); doc.text("Authorised Signatory", W - M - 29, sy + 12.5, {align:"center"});
  doc.setProperties({title: invNo, subject: "Bill - " + o.id, author: "RT Fashion Hub"});
  doc.save(`${invNo}.pdf`);
} };
})();
