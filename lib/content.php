<?php
// Content fetchers + fallback. Tanan SQL naa ra diri, dili sa index.php.

function fallback_projects(): array {
  return [
    ['id'=>1,'num'=>'01','role'=>'Founder · Full Stack','year'=>'2026','title'=>'Project Alpha','desc'=>'Sample project. Ingna lang ko ug unsay ilisan ani.','tags'=>'PHP,MySQL,Tailwind','live_url'=>'#','case_url'=>'#','icon'=>'rocket'],
    ['id'=>2,'num'=>'02','role'=>'Freelance · Web App','year'=>'2025','title'=>'Project Beta','desc'=>'Sample project. Ingna lang ko ug unsay idungag.','tags'=>'PHP,JavaScript,CSS','live_url'=>'#','case_url'=>'#','icon'=>'chart'],
    ['id'=>3,'num'=>'03','role'=>'Personal · UI/UX','year'=>'2025','title'=>'Project Gamma','desc'=>'Sample project. Tanan description imoha ra.','tags'=>'Figma,PHP,MySQL','live_url'=>'#','case_url'=>'#','icon'=>'note'],
    ['id'=>4,'num'=>'04','role'=>'Client Work · Website','year'=>'2024','title'=>'Project Delta','desc'=>'Sample project para sa client portfolio showcase.','tags'=>'PHP,Bootstrap,MySQL','live_url'=>'#','case_url'=>'#','icon'=>'building'],
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
    ['year'=>'Present','title'=>'Building the future','desc'=>'Nagpadayon ug learn ug build ug digital products.'],
    ['year'=>'2024','title'=>'Full stack expertise','desc'=>'Nahanas sa PHP, MySQL, JS ug UI/UX.'],
    ['year'=>'2023','title'=>'Professional growth','desc'=>'Ni-apil ug team, dagkong apps, databases.'],
    ['year'=>'2022','title'=>'Expanded skills','desc'=>'Nakat-on ug modern web + freelancing.'],
    ['year'=>'2021','title'=>'Started journey','desc'=>'Nagsugod sa HTML, CSS, JavaScript.'],
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
  // BC wrapper — karon 1 per person na. Default action = like once.
  $r = toggle_like($pdo, 'like');
  return $r['total'];
}

function like_voter_hash(): string {
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
  // kung naay proxy chain, kuhaa ang first IP
  $ip = trim(explode(',', (string)$ip)[0]);
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
      // i-sync ang cookie para dili na mu-query sunod
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
        // race: naunhan ug insert sa concurrent request, ayaw double-count
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

    // toggle (default sa button)
    return $liked ? toggle_like($pdo, 'unlike') : toggle_like($pdo, 'like');
  } catch (Exception $ex) { return ['total' => 0, 'liked' => false]; }
}
