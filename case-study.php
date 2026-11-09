<?php
// Case study page. Thin controller: resolve the project, then compose only.
require __DIR__ . '/lib/helpers.php';
require __DIR__ . '/lib/icons.php';
require __DIR__ . '/lib/database.php';
require __DIR__ . '/lib/content.php';

$slug = static function (string $t): string {
  return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $t)), '-');
};

$pdo      = db_connect();
$projects = array_values(get_projects($pdo));
$wanted   = trim((string)($_GET['p'] ?? $_GET['project'] ?? ''));
$wantedId = trim((string)($_GET['id'] ?? ''));

$current = null;
$index   = 0;
foreach ($projects as $i => $p) {
  if (($wanted !== '' && $slug((string)$p['title']) === $slug($wanted))
      || ($wantedId !== '' && (string)($p['id'] ?? '') === $wantedId)) {
    $current = $p;
    $index   = $i;
    break;
  }
}

$home        = 'index.php';
$hero_active = false;
$show_header = false;
$present_now = presence_count($pdo);

if (!$current) {
  http_response_code(404);
  $page_title = token('case.not_found') . ' | ' . token('owner_name');
  $page_desc  = (string)token('case.not_found_lead');
  require __DIR__ . '/partials/head.php';
  ?>
  <main class="case-main"><div class="case-wrap">
    <a class="case-back" href="index.php#projects"><?=icon('arrow_left', 16)?><?=e(token('case.back'))?></a>
    <header class="case-head">
      <p class="case-kicker"><?=e(token('case.kicker'))?></p>
      <h1 class="case-title"><?=e(token('case.not_found'))?></h1>
      <p class="case-sub"><?=e(token('case.not_found_lead'))?></p>
    </header>
  </div></main>
  <?php
  require __DIR__ . '/partials/reactions.php';
  require __DIR__ . '/partials/footer.php';
  exit;
}

$next       = count($projects) > 1 ? $projects[($index + 1) % count($projects)] : null;
$copyAll    = token('case.copy', []);
$copy       = is_array($copyAll) ? (array)($copyAll[$slug((string)$current['title'])] ?? []) : [];
$tagline    = trim((string)($copy['tagline'] ?? ($current['desc'] ?? '')));
$overview   = trim((string)($copy['overview'] ?? ($current['desc'] ?? '')));
$page_title = (string)$current['title'] . ' | ' . token('owner_name');
$page_desc  = $tagline !== '' ? $tagline : (string)token('tagline');

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/case-study.php';
require __DIR__ . '/partials/reactions.php';
require __DIR__ . '/partials/footer.php';
