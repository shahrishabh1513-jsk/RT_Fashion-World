<?php
/* Branded SVG placeholder shown when a product image file is missing. */
header('Content-Type: image/svg+xml; charset=utf-8'); header('Cache-Control: public, max-age=86400');
$n = (string)($_GET['n'] ?? ''); $s = strtoupper((string)($_GET['s'] ?? ''));
$brand = preg_match('/^(.*?)\s+(Men|Women|Solid|Pack|Casual|Large)\b/u', $n, $m) ? $m[1] : implode(' ', array_slice(explode(' ', $n), 0, 2));
$x = fn($t) => htmlspecialchars(mb_substr($t, 0, 26), ENT_XML1 | ENT_QUOTES, 'UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 800"><rect width="600" height="800" fill="#F8F6F3"/>
<g fill="none" stroke="#C6A46A" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" transform="translate(210 250) scale(7)"><path d="M12 7a2.5 2.5 0 1 0-2.5-2.5"/><path d="M12 7v2L3 15.5a1.5 1.5 0 0 0 .9 2.7h16.2a1.5 1.5 0 0 0 .9-2.7L12 9"/></g>
<text x="300" y="500" text-anchor="middle" font-family="Georgia,serif" font-size="30" fill="#171717"><?= $x($brand) ?></text>
<text x="300" y="545" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" letter-spacing="4" fill="#777"><?= $x($s) ?></text>
<text x="300" y="740" text-anchor="middle" font-family="Arial,sans-serif" font-size="15" letter-spacing="3" fill="#bbb">RT FASHION HUB</text></svg>
