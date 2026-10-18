<?php
// Like API — 1 per person (cookie + IP+UA voter hash). Toggle supported.
require __DIR__ . '/lib/database.php';
require __DIR__ . '/lib/icons.php';
require __DIR__ . '/lib/content.php';
header('Content-Type: application/json');

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
  $r = toggle_like($pdo, 'status');
  echo json_encode($r);
  exit;
}

// POST: read the action (like|unlike|toggle), default toggle
$action = 'toggle';
$raw = file_get_contents('php://input');
if ($raw) {
  $j = json_decode($raw, true);
  if (!empty($j['action']) && in_array($j['action'], ['like', 'unlike', 'toggle'], true)) {
    $action = $j['action'];
  }
}
if (!empty($_POST['action']) && in_array($_POST['action'], ['like', 'unlike', 'toggle'], true)) {
  $action = $_POST['action'];
}

echo json_encode(toggle_like($pdo, $action));
