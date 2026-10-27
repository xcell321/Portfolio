<?php
// Thin controller. No HTML or SQL here — compose only.
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/icons.php';
require __DIR__ . '/lib/database.php';
require __DIR__ . '/lib/content.php';

csrf_start();

$pdo      = db_connect();
$projects = get_projects($pdo);
$timeline = get_timeline($pdo);
$certs    = get_certs($pdo);

// --- form actions ---
$msg_ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
  if (!csrf_ok()) {
    http_response_code(403);
    $msg_ok = token('contact.err');
  } elseif (($_SESSION['last_contact'] ?? 0) > time() - 30) {
    http_response_code(429);
    $msg_ok = token('contact.err');
  } else {
    $data = [
      'name'    => mb_substr(trim($_POST['name'] ?? ''), 0, 100),
      'email'   => mb_substr(trim($_POST['email'] ?? ''), 0, 150),
      'company' => mb_substr(trim($_POST['company'] ?? ''), 0, 150),
      'need'    => mb_substr(trim($_POST['need'] ?? ''), 0, 255),
      'budget'  => mb_substr(trim($_POST['budget'] ?? ''), 0, 50),
      'message' => mb_substr(trim($_POST['message'] ?? ''), 0, 5000),
    ];
    if ($data['name'] && $data['message'] && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
      save_message($pdo, $data);
      $_SESSION['last_contact'] = time();
      $msg_ok = sprintf(token('contact.ok'), $data['name']);
    } else {
      $msg_ok = token('contact.err');
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gbook'])) {
  if (!csrf_ok()) { http_response_code(403); exit('Forbidden'); }
  if (($_SESSION['last_gbook'] ?? 0) > time() - 30) { http_response_code(429); exit('Too many requests'); }
  $gname = mb_substr(trim($_POST['gname'] ?? ''), 0, 100);
  $gmsg  = mb_substr(trim($_POST['gmsg'] ?? ''), 0, 2000);
  if (save_guestbook($pdo, $gname, $gmsg)) $_SESSION['last_gbook'] = time();
  header('Location: #guestbook');
  exit;
}

$guest_rows = get_guestbook($pdo);
$like_total = get_likes($pdo);
$liked_now  = has_liked($pdo); // 1 per person — sets liked state on first render
$present_now = presence_count($pdo); // visitors in the last 20s — seeds the presence badge

// --- view ---
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/hero.php';
require __DIR__ . '/partials/about.php';
require __DIR__ . '/partials/work.php';
require __DIR__ . '/partials/journey.php';
require __DIR__ . '/partials/creds.php';
require __DIR__ . '/partials/guestbook.php';
require __DIR__ . '/partials/contact.php';
require __DIR__ . '/partials/reactions.php';
require __DIR__ . '/partials/footer.php';
