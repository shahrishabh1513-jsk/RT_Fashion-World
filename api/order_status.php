<?php
require __DIR__ . '/../includes/bootstrap.php';
$o = order_load((string)($_GET['no'] ?? '')); if (!$o || !order_can_view($o, $_GET['email'] ?? null)) json_out(['ok' => false], 404);
json_out(['ok' => true, 'status' => $o['status'], 'step' => order_step($o['status']), 'tracking' => $o['tracking'], 'updated' => $o['updated_at'],
  'history' => array_map(fn($h) => ['status' => $h['status'], 'note' => $h['note'], 'at' => date('j M, g:i a', strtotime($h['created_at']))], $o['history'])]);
