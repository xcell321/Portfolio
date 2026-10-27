<?php
// Content fetchers + fallback. All SQL lives here, not in index.php.

function fallback_projects(): array {
  return [
    ['id'=>1,'num'=>'01','role'=>'Personal · Web App','year'=>'2026','title'=>'Remotee','desc'=>'A universal remote control for your home devices, built end to end with PHP, CSS and JavaScript.','tags'=>'PHP,MySQL,CSS,JavaScript','live_url'=>'https://remotee.infinityfreeapp.com/','case_url'=>'https://remotee.infinityfreeapp.com/','icon'=>'mobile','image'=>'assets/img/work/remotee.png','image_mobile'=>'assets/img/work/remotee-mobile.png'],
    ['id'=>2,'num'=>'02','role'=>'Freelance · Web App','year'=>'2025','title'=>'Project Beta','desc'=>'Sample project. Tell me what to add here.','tags'=>'PHP,JavaScript,CSS','live_url'=>'#','case_url'=>'#','icon'=>'chart'],
    ['id'=>3,'num'=>'03','role'=>'Personal · UI/UX','year'=>'2025','title'=>'Project Gamma','desc'=>'Sample project. Every description is yours to edit.','tags'=>'Figma,PHP,MySQL','live_url'=>'#','case_url'=>'#','icon'=>'note'],
    ['id'=>4,'num'=>'04','role'=>'Client Work · Website','year'=>'2024','title'=>'Project Delta','desc'=>'Sample project for a client portfolio showcase.','tags'=>'PHP,Bootstrap,MySQL','live_url'=>'#','case_url'=>'#','icon'=>'building'],
  ];
}

function get_projects(?PDO $pdo): array {
  if (!$pdo) return fallback_projects();
  try {
    $rows = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC, id ASC")->fetchAll();
    return $rows ?: fallback_projects();
  } catch (Exception $ex) { return fallback_projects(); }
}

function get_timeline(?PDO $pdo): array {
  $fb = [
    ['year'=>'Present','title'=>'Building the future','desc'=>'Continuing to learn and build digital products.'],
    ['year'=>'2024','title'=>'Full stack expertise','desc'=>'Sharpened skills in PHP, MySQL, JS, and UI/UX.'],
    ['year'=>'2023','title'=>'Professional growth','desc'=>'Joined a team; worked on large apps and databases.'],
    ['year'=>'2022','title'=>'Expanded skills','desc'=>'Learned modern web development and freelancing.'],
    ['year'=>'2021','title'=>'Started journey','desc'=>'Started with HTML, CSS, JavaScript.'],
  ];
  if (!$pdo) return $fb;
  try { $r = $pdo->query("SELECT * FROM timeline ORDER BY sort_order DESC")->fetchAll(); return $r ?: $fb; }
  catch (Exception $ex) { return $fb; }
}

function get_certs(?PDO $pdo): array {
  $fb = [
    ['title'=>'Credly Verified Badge','org'=>'Credly','date'=>'2026','verify_url'=>'https://www.credly.com/badges/f205f136-42c3-4dc8-94f6-979adb4d94c7/public_url','icon'=>'award'],
    ['title'=>'Sample Certificate 2','org'=>'IBM SkillsBuild','date'=>'Feb 2026','verify_url'=>'#','icon'=>'bot'],
    ['title'=>'Sample Certificate 3','org'=>'Cisco × IBM','date'=>'Feb 2026','verify_url'=>'#','icon'=>'brain'],
    ['title'=>'Sample Certificate 4','org'=>'Cisco Networking','date'=>'Jan 2026','verify_url'=>'#','icon'=>'chart'],
  ];
  if (!$pdo) return $fb;
  try { $r = $pdo->query("SELECT * FROM certificates ORDER BY id ASC")->fetchAll(); return $r ?: $fb; }
  catch (Exception $ex) { return $fb; }
}

function get_guestbook(?PDO $pdo, int $limit = 8): array {
  if (!$pdo) return [];
  try { return $pdo->query("SELECT * FROM guestbook ORDER BY id DESC LIMIT $limit")->fetchAll(); }
  catch (Exception $ex) { return []; }
}

function get_likes(?PDO $pdo): int {
  if (!$pdo) return 0;
  try { return (int)$pdo->query("SELECT total FROM site_likes WHERE id=1")->fetchColumn(); }
  catch (Exception $ex) { return 0; }
}

function save_message(?PDO $pdo, array $d): bool {
  if (!$pdo) return false;
  try {
    $pdo->prepare("INSERT INTO messages(name,email,company,need,budget,message) VALUES(?,?,?,?,?,?)")
        ->execute([$d['name'],$d['email'],$d['company'],$d['need'],$d['budget'],$d['message']]);
    return true;
  } catch (Exception $ex) { return false; }
}

function save_guestbook(?PDO $pdo, string $name, string $msg): bool {
  if (!$pdo || !$name || !$msg) return false;
  try { $pdo->prepare("INSERT INTO guestbook(name,message) VALUES(?,?)")->execute([$name,$msg]); return true; }
  catch (Exception $ex) { return false; }
}

function bump_likes(?PDO $pdo): int {
  // BC wrapper — now 1 per person. Default action = like once.
  $r = toggle_like($pdo, 'like');
  return $r['total'];
}

function like_voter_hash(): string {
  $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  // Only trust X-Forwarded-For when the direct peer is a known proxy.
  $trusted = ['127.0.0.1', '::1'];
  if (in_array($ip, $trusted, true) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    // with a proxy chain, take the first IP
    $ip = trim(explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
  }
  $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200);
  return hash('sha256', $ip . '|' . $ua);
}

function ensure_like_tables(?PDO $pdo): void {
  if (!$pdo) return;
  $pdo->exec("CREATE TABLE IF NOT EXISTS site_likes (
    id INT PRIMARY KEY DEFAULT 1, total INT DEFAULT 0
  )");
  $pdo->exec("INSERT IGNORE INTO site_likes (id,total) VALUES (1,0)");
  $pdo->exec("CREATE TABLE IF NOT EXISTS site_like_voters (
    voter_hash CHAR(64) PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )");
}

function has_liked(?PDO $pdo): bool {
  if (!empty($_COOKIE['liked_portfolio'])) return true;
  if (!$pdo) return false;
  try {
    ensure_like_tables($pdo);
    $st = $pdo->prepare("SELECT 1 FROM site_like_voters WHERE voter_hash=? LIMIT 1");
    $st->execute([like_voter_hash()]);
    if ($st->fetchColumn()) {
      // sync the cookie so later checks skip the query
      set_like_cookie();
      return true;
    }
  } catch (Exception $ex) {}
  return false;
}

function set_like_cookie(): void {
  if (headers_sent() || PHP_SAPI === 'cli') return;
  setcookie('liked_portfolio', '1', time() + 365*24*3600, '/');
  $_COOKIE['liked_portfolio'] = '1';
}

function clear_like_cookie(): void {
  if (!headers_sent() && PHP_SAPI !== 'cli') {
    setcookie('liked_portfolio', '', time() - 3600, '/');
  }
  unset($_COOKIE['liked_portfolio']);
}

/**
 * 1 per person like logic.
 * $action: 'like' | 'unlike' | 'toggle' | 'status'
 * @return array{total:int, liked:bool}
 */
function toggle_like(?PDO $pdo, string $action = 'toggle'): array {
  if (!$pdo) return ['total' => 0, 'liked' => !empty($_COOKIE['liked_portfolio'])];
  try {
    ensure_like_tables($pdo);
    $total = (int)$pdo->query("SELECT total FROM site_likes WHERE id=1")->fetchColumn();
    $liked = has_liked($pdo);
    $hash = like_voter_hash();

    if ($action === 'status') return ['total' => $total, 'liked' => $liked];

    if ($action === 'like') {
      if ($liked) return ['total' => $total, 'liked' => true];
      $ins = $pdo->prepare("INSERT IGNORE INTO site_like_voters(voter_hash) VALUES(?)");
      $ins->execute([$hash]);
      if ($ins->rowCount() === 0) {
        // race: a concurrent insert won first — don't double-count
        set_like_cookie();
        return ['total' => $total, 'liked' => true];
      }
      $pdo->exec("INSERT INTO site_likes(id,total) VALUES(1,1) ON DUPLICATE KEY UPDATE total=total+1");
      set_like_cookie();
      $total = (int)$pdo->query("SELECT total FROM site_likes WHERE id=1")->fetchColumn();
      return ['total' => $total, 'liked' => true];
    }

    if ($action === 'unlike') {
      if (!$liked) return ['total' => $total, 'liked' => false];
      $pdo->prepare("DELETE FROM site_like_voters WHERE voter_hash=?")->execute([$hash]);
      $pdo->exec("UPDATE site_likes SET total=GREATEST(total-1,0) WHERE id=1");
      clear_like_cookie();
      $total = (int)$pdo->query("SELECT total FROM site_likes WHERE id=1")->fetchColumn();
      return ['total' => $total, 'liked' => false];
    }

    // toggle (default for the button)
    return $liked ? toggle_like($pdo, 'unlike') : toggle_like($pdo, 'like');
  } catch (Exception $ex) { return ['total' => 0, 'liked' => false]; }
}

// ---------------------------------------------------------------------------
// Emoji reaction bar + presence. Replaces the reference build's PartyKit socket
// with MySQL polling: reactions are broadcast through reaction_events, presence
// through the last_seen column. Emoji artwork is Twemoji (the same files the
// reference loads from jsDelivr), served locally from assets/img/emoji/.
// ---------------------------------------------------------------------------

/** The reaction palette. Emoji must stay byte-identical to what api_react.php accepts. */
function reaction_set(): array {
  return [
    ['emoji' => '👏', 'label' => 'Clap', 'file' => '1f44f'],
    ['emoji' => '🔥', 'label' => 'Fire', 'file' => '1f525'],
    ['emoji' => '🤍', 'label' => 'Love', 'file' => '1f90d'],
  ];
}

/** Emoji shown on the bar's toggle button. */
function reaction_toggle_emoji(): array {
  return ['emoji' => '🎉', 'label' => 'React', 'file' => '1f389'];
}

function react_allowed(string $emoji): bool {
  foreach (reaction_set() as $r) { if ($r['emoji'] === $emoji) return true; }
  return false;
}

/** Session id is client-generated; keep it URL/paste-safe before it reaches SQL. */
function clean_session_id(string $s): string {
  $s = preg_replace('/[^A-Za-z0-9_-]/', '', $s);
  return substr((string)$s, 0, 64);
}

function ensure_reaction_tables(?PDO $pdo): void {
  if (!$pdo) return;
  // emoji columns are utf8mb4_bin: the default ci collation treats distinct emoji as equal
  $pdo->exec("CREATE TABLE IF NOT EXISTS reactions (
    emoji VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin PRIMARY KEY,
    total INT NOT NULL DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS reaction_events (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    emoji VARCHAR(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
    session_id VARCHAR(64) NOT NULL DEFAULT '',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $pdo->exec("CREATE TABLE IF NOT EXISTS presence (
    session_id VARCHAR(64) PRIMARY KEY,
    last_seen TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $ins = $pdo->prepare("INSERT IGNORE INTO reactions (emoji,total) VALUES (?,0)");
  foreach (reaction_set() as $r) { $ins->execute([$r['emoji']]); }
}

function touch_presence(?PDO $pdo, string $session): void {
  if (!$pdo || $session === '') return;
  try {
    $pdo->prepare("INSERT INTO presence (session_id,last_seen) VALUES (?,NOW())
                   ON DUPLICATE KEY UPDATE last_seen=NOW()")->execute([$session]);
  } catch (Exception $ex) {}
}

/** Visitors seen in the last 20 seconds (includes the current one). */
function presence_count(?PDO $pdo): int {
  if (!$pdo) return 1;
  try {
    ensure_reaction_tables($pdo);
    return max(1, (int)$pdo->query("SELECT COUNT(*) FROM presence WHERE last_seen >= NOW() - INTERVAL 20 SECOND")->fetchColumn());
  } catch (Exception $ex) { return 1; }
}

function react_counts(?PDO $pdo): array {
  if (!$pdo) return [];
  try {
    $out = [];
    foreach ($pdo->query("SELECT emoji,total FROM reactions") as $r) { $out[$r['emoji']] = (int)$r['total']; }
    return $out;
  } catch (Exception $ex) { return []; }
}

/** Record one reaction. Returns the new event id so the sender can skip its own echo. */
function react_send(?PDO $pdo, string $emoji, string $session): array {
  if (!$pdo || !react_allowed($emoji)) return ['ok' => false];
  try {
    ensure_reaction_tables($pdo);
    $pdo->prepare("INSERT INTO reactions (emoji,total) VALUES (?,1)
                   ON DUPLICATE KEY UPDATE total=total+1")->execute([$emoji]);
    $pdo->prepare("INSERT INTO reaction_events (emoji,session_id) VALUES (?,?)")->execute([$emoji, $session]);
    $id = (int)$pdo->lastInsertId(); // read before touch_presence, whose upsert resets it
    touch_presence($pdo, $session);
    return ['ok' => true, 'id' => $id];
  } catch (Exception $ex) { return ['ok' => false]; }
}

/** Poll: refresh presence, hand back new events, the cursor and the totals. */
function react_poll(?PDO $pdo, int $since, string $session): array {
  if (!$pdo) return ['ok' => false, 'events' => [], 'last' => $since, 'present' => 1, 'counts' => []];
  try {
    ensure_reaction_tables($pdo);
    touch_presence($pdo, $session);
    // prune rows from closed tabs occasionally instead of every poll
    if (random_int(1, 20) === 1) {
      $pdo->exec("DELETE FROM presence WHERE last_seen < NOW() - INTERVAL 2 MINUTE");
    }
    $last = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM reaction_events")->fetchColumn();
    // $since = 0 means "first poll" — sync the cursor without replaying history
    $events = [];
    if ($since > 0 && $last > $since) {
      $st = $pdo->prepare("SELECT id,emoji,session_id FROM reaction_events WHERE id > ? ORDER BY id ASC LIMIT 40");
      $st->execute([$since]);
      $events = $st->fetchAll();
    }
    return [
      'ok' => true,
      'events' => $events,
      'last' => $last,
      'present' => max(1, (int)$pdo->query("SELECT COUNT(*) FROM presence WHERE last_seen >= NOW() - INTERVAL 20 SECOND")->fetchColumn()),
      'counts' => react_counts($pdo),
    ];
  } catch (Exception $ex) {
    return ['ok' => false, 'events' => [], 'last' => $since, 'present' => 1, 'counts' => []];
  }
}
