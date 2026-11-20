<?php
require __DIR__ . '/includes/bootstrap.php';
$page = 'contact'; $title = 'Contact Us | RT Fashion Hub'; $desc = 'Contact RT Fashion Hub in Vadodara, Gujarat. Find our address, phone, email, map, FAQs and send us a message.';
$err = []; $addr = cfg('address'); $u = current_user();
if (is_post()) {
    require_csrf(); $n = trim((string)($_POST['name'] ?? '')); $em = trim((string)($_POST['email'] ?? '')); $sb = trim((string)($_POST['subject'] ?? '')); $ms = trim((string)($_POST['message'] ?? ''));
    if (mb_strlen($n) < 2) $err['name'] = 'Please enter your name.'; if (!valid_email($em)) $err['email'] = $em === '' ? 'Please enter your email.' : 'Enter a valid email address.';
    if (mb_strlen($sb) < 3) $err['subject'] = 'Please add a subject.'; if (mb_strlen($ms) < 10) $err['message'] = 'Message should be at least 10 characters.';
    if (!$err) { q('INSERT INTO contact_messages (name,email,subject,message) VALUES (?,?,?,?)', [mb_substr($n, 0, 100), mb_substr($em, 0, 150), mb_substr($sb, 0, 200), mb_substr($ms, 0, 5000)]); flash('Thank you! Your message has been received. We\'ll reply soon.', 'ok'); redirect('contact.php#msg'); }
}
$faqs = [['How do I track my order?', 'You can track your order by visiting the Order Tracking page and entering your order number and email address.'],
['What is your return policy?', 'We offer a 30-day return policy for all unworn items in their original packaging. Please visit our Returns page for more information.'],
['Do you ship across India?', 'Yes! We deliver to over 20,000 pin codes across India, including most Tier 2 and Tier 3 towns. Delivery times vary by location.'],
['What payment methods do you accept?', "We accept Credit/Debit Cards, UPI, NetBanking and Wallets via Razorpay's secure checkout, as well as Cash on Delivery (COD) on most pin codes."],
['Is it safe to pay online on FashionHub India?', 'Absolutely. All online payments are processed by Razorpay, a PCI-DSS Level 1 certified payment gateway. We never see or store your card, UPI or banking details.'],
['Is GST included in the price?', 'Yes, all prices shown are inclusive-ready — GST (5%) is calculated and shown separately at checkout for full transparency.'],
['How can I change or cancel my order?', 'Orders can be modified or cancelled within 1 hour of placement. Please contact our customer support immediately for assistance.']];
$fe = fn($k) => '<span class="err">' . e($err[$k] ?? '') . '</span>'; $inv = fn($k) => isset($err[$k]) ? ' invalid' : '';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero page-hero--img" style="--bg-img:url('../images/background/banner4.webp')"><div class="crumbs">Home / Contact</div><h1>#LET'S_TALK</h1><p>We'd love to hear from you!</p></section>
<main>
<section class="section"><div class="container"><div class="section-head reveal"><span class="eyebrow">Head Office</span><h2>GET IN TOUCH</h2><p>Visit one of our agency locations or contact us today.</p></div>
  <div class="info-cards">
    <div class="info-card reveal"><div class="ico-wrap"><?= icon('pin') ?></div><h3>Head Office</h3><p><b style="color:var(--text)">RT Fashion Hub</b><br>Opp. Sumandeep College, Near Suzlon Company, Vadodara, Gujarat 391760, India.</p></div>
    <div class="info-card reveal"><div class="ico-wrap"><?= icon('mail') ?></div><h3>Email</h3><a href="mailto:contact@rtfashionhub.com">contact@rtfashionhub.com</a></div>
    <div class="info-card reveal"><div class="ico-wrap"><?= icon('phone') ?></div><h3>Phone</h3><a href="tel:+917861976128">+91 78619 76128</a></div>
    <div class="info-card reveal"><div class="ico-wrap"><?= icon('clock') ?></div><h3>Business Hours</h3><p>Monday to Saturday:<br>10:00 AM to 6:00 PM.</p></div></div></div></section>
<section class="section" style="padding-top:0"><div class="container"><div class="map-wrap reveal"><iframe title="Map showing the RT Fashion Hub address" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="https://www.google.com/maps?q=<?= urlencode($addr) ?>&output=embed"></iframe></div>
  <div style="text-align:center;margin-top:22px"><a class="btn btn--dark" target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($addr) ?>">Get Directions</a><p class="note">The map searches the address above; it does not mark a verified business listing.</p></div></div></section>
<section class="section" id="msg" style="padding-top:0"><div class="container" style="max-width:820px"><div class="section-head reveal"><span class="eyebrow">Write to us</span><h2>LEAVE A MESSAGE</h2><p>We'd love to hear from you.</p></div>
  <form class="form-card reveal" method="post" action="<?= url('contact.php#msg') ?>" novalidate><?= csrf_field() ?><div class="form-grid">
    <div class="field<?= $inv('name') ?>"><label for="cName">Your Name *</label><input id="cName" name="name" autocomplete="name" value="<?= e($_POST['name'] ?? ($u['name'] ?? '')) ?>" required><?= $fe('name') ?></div>
    <div class="field<?= $inv('email') ?>"><label for="cEmail">Your Email *</label><input id="cEmail" name="email" type="email" autocomplete="email" value="<?= e($_POST['email'] ?? ($u['email'] ?? '')) ?>" required><?= $fe('email') ?></div>
    <div class="field full<?= $inv('subject') ?>"><label for="cSubject">Subject *</label><input id="cSubject" name="subject" value="<?= old('subject') ?>" required><?= $fe('subject') ?></div>
    <div class="field full<?= $inv('message') ?>"><label for="cMsg">Your Message *</label><textarea id="cMsg" name="message" rows="6" required><?= old('message') ?></textarea><?= $fe('message') ?></div>
    <div class="full"><button class="btn btn--dark" type="submit">Submit</button></div></div></form></div></section>
<section class="section" id="faq" style="padding-top:0"><div class="container"><div class="section-head reveal"><span class="eyebrow">Help Centre</span><h2>Frequently Asked Questions</h2></div><div class="faq">
  <?php foreach ($faqs as $i => [$qn, $an]): ?><div class="faq-item"><h3 style="font-family:var(--font)"><button class="faq-q" aria-expanded="false" aria-controls="fa<?= $i ?>" id="fq<?= $i ?>"><?= e($qn) ?><span class="plus"></span></button></h3><div class="faq-a" id="fa<?= $i ?>" role="region" aria-labelledby="fq<?= $i ?>"><p><?= e($an) ?></p></div></div><?php endforeach; ?></div></div></section>
<section class="section" style="padding-top:0"><div class="container"><div class="section-head reveal"><span class="eyebrow">The People</span><h2>Meet Our Team</h2></div><?php require __DIR__ . '/includes/team_cards.php'; ?></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php';
