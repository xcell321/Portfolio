<?php
// Thin controller only. Walay HTML, walay SQL diri — compose ra.
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/icons.php';
require __DIR__ . '/lib/database.php';
require __DIR__ . '/lib/content.php';

$pdo      = db_connect();
$projects = get_projects($pdo);
$timeline = get_timeline($pdo);
$certs    = get_certs($pdo);

// --- form actions ---
$msg_ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
  $data = [
    'name'    => trim($_POST['name'] ?? ''),
    'email'   => trim($_POST['email'] ?? ''),
    'company' => trim($_POST['company'] ?? ''),
    'need'    => trim($_POST['need'] ?? ''),
    'budget'  => trim($_POST['budget'] ?? ''),
    'message' => trim($_POST['message'] ?? ''),
  ];
  if ($data['name'] && $data['email'] && $data['message']) {
    save_message($pdo, $data);
    $msg_ok = sprintf(token('contact.ok'), $data['name']);
  } else {
    $msg_ok = token('contact.err');
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gbook'])) {
  save_guestbook($pdo, trim($_POST['gname'] ?? ''), trim($_POST['gmsg'] ?? ''));
  header('Location: #guestbook');
  exit;
}

$guest_rows = get_guestbook($pdo);
$like_total = get_likes($pdo);
$liked_now  = has_liked($pdo); // 1 per person — para liked state sa first render

// --- view ---
require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/hero.php';
require __DIR__ . '/partials/about.php';
require __DIR__ . '/partials/work.php';
require __DIR__ . '/partials/journey.php';
require __DIR__ . '/partials/creds.php';
require __DIR__ . '/partials/guestbook.php';
require __DIR__ . '/partials/contact.php';
require __DIR__ . '/partials/footer.php';
