<?php
// Reaction bar API. Both actions are POSTed as JSON.
//   {action:'react', emoji, session} -> record one reaction, return its event id
//   {action:'poll',  since, session} -> refresh presence, return new events + totals
require __DIR__ . '/lib/database.php';
require __DIR__ . '/lib/content.php';
header('Content-Type: application/json');

$pdo = db_connect();

$raw = file_get_contents('php://input');
$in  = $raw ? (json_decode($raw, true) ?: []) : [];
if (!$in && !empty($_POST)) { $in = $_POST; }

$action  = (string)($in['action'] ?? 'poll');
$session = clean_session_id((string)($in['session'] ?? ''));

if ($action === 'react') {
  $r = react_send($pdo, (string)($in['emoji'] ?? ''), $session);
  if (empty($r['ok'])) { http_response_code(400); echo json_encode(['ok' => false]); exit; }
  echo json_encode($r);
  exit;
}

echo json_encode(react_poll($pdo, max(0, (int)($in['since'] ?? 0)), $session));
