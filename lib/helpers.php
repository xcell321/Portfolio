<?php
// Small helpers. No DB, no HTML.

function app_tokens(): array {
  static $t = null;
  if ($t === null) $t = require __DIR__ . '/../config/tokens.php';
  return $t;
}

function token(string $key, $fallback = '') {
  $t = app_tokens();
  return $t[$key] ?? $fallback;
}

/** Escape output. */
function e($v): string {
  return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

/** Start the session once, before any output, with a safe cookie. */
function csrf_start(): void {
  if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli' && !headers_sent()) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/', 'secure' => $https]);
    session_start();
  }
}

/** Per-session CSRF token. */
function csrf_token(): string {
  csrf_start();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
  return $_SESSION['csrf'];
}

/** Hidden input for forms. */
function csrf_field(): string {
  return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** True when the POST carries the session's token. */
function csrf_ok(): bool {
  csrf_start();
  $sent = (string)($_POST['csrf'] ?? '');
  return $sent !== '' && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/**
 * Same-origin guard for JSON APIs. Blocks cross-site form/fetch POSTs.
 * Requests without browser headers (curl, CLI) pass — they cannot be CSRF'd.
 */
function same_origin_post(): bool {
  $site = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';
  if ($site !== '') return $site === 'same-origin' || $site === 'none';
  $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
  if ($origin === '') return true;
  $o = parse_url($origin);
  if (!$o || empty($o['host'])) return false;
  $oh = strtolower($o['host'] . (isset($o['port']) ? ':' . $o['port'] : ''));
  return $oh === strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
}

/**
 * File-based rate limit. Returns true while under $max hits per $window seconds.
 * Keyed by bucket + client IP and guarded with flock for concurrent requests.
 */
function rate_limit(string $bucket, int $max, int $window): bool {
  $dir = sys_get_temp_dir() . '/portfolio_rate';
  if (!is_dir($dir)) @mkdir($dir, 0700, true);
  $file = $dir . '/' . preg_replace('/[^a-z0-9_]/i', '', $bucket) . '_'
        . hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown')) . '.json';
  $fh = @fopen($file, 'c+');
  if (!$fh) return true;
  @flock($fh, LOCK_EX);
  $j = json_decode((string)stream_get_contents($fh), true);
  $now = time();
  $hits = [];
  foreach ((array)($j['hits'] ?? []) as $t) {
    if (is_int($t) && $t > $now - $window) $hits[] = $t;
  }
  $ok = count($hits) < $max;
  if ($ok) $hits[] = $now;
  ftruncate($fh, 0);
  rewind($fh);
  fwrite($fh, json_encode(['hits' => $hits]));
  @flock($fh, LOCK_UN);
  fclose($fh);
  return $ok;
}

/** External link attrs — always open in a new tab. */
function ext(string $url): string {
  return 'href="' . e($url) . '" target="_blank" rel="noopener"';
}

/**
 * Two-tone section title:
 * first words plain, last N words dim (stone-500).
 * e.g. split_title('One partner, from concept to shipped release.', 3)
 *   => '<span>One partner, from concept</span> <span class=dim>to shipped release.</span>'
 */
function split_title(string $text, int $words = 3): string {
  $parts = preg_split('/\s+/', trim($text));
  $n = count($parts);
  if ($n <= $words) return e($text);
  $head = implode(' ', array_slice($parts, 0, $n - $words));
  $tail = implode(' ', array_slice($parts, $n - $words));
  return e($head) . ' <span class="dim">' . e($tail) . '</span>';
}

/**
 * Section marker: "04 CREDENTIALS" -> accent number, short rule, muted label.
 * Every section eyebrow renders through this so the pattern lives in one place.
 */
function eyebrow(string $kicker, string $extra = ''): string {
  $parts = preg_split('/\s+/', trim($kicker), 2);
  $num = (count($parts) === 2 && preg_match('/^\d+$/', $parts[0] ?? '')) ? $parts[0] : '';
  $label = $num !== '' ? $parts[1] : trim($kicker);
  $cls = 'sec-eyebrow' . ($extra !== '' ? ' ' . $extra : '');
  $out = '<div class="' . $cls . '">';
  if ($num !== '') $out .= '<span class="mono acc">' . e($num) . '</span><span class="rule"></span>';
  return $out . '<span class="eyebrow">' . e($label) . '</span></div>';
}

/**
 * Which shipped projects used a given tech word — derived from project tags, never authored.
 * Matching is exact and case-insensitive: the tag list is the contract. Containment was the
 * first idea and it lied — "Tailwind CSS" matched a plain "CSS" tag and claimed a project
 * used Tailwind. A word with no matching tag still gets its note, just no shots.
 * Only rows with a real screenshot file come back.
 *
 * @return list<array{title:string, img:string, url:string}>
 */
function tech_usage(string $word, array $projects, int $max = 2): array {
  $w = strtolower(trim($word));
  if ($w === '') return [];
  $out = [];
  foreach ($projects as $p) {
    $tags = array_map(static fn($t) => strtolower(trim($t)), explode(',', (string)($p['tags'] ?? '')));
    if (!in_array($w, $tags, true)) continue;
    $img = trim((string)($p['image'] ?? ''));
    if ($img === '' || !is_file(__DIR__ . '/../' . $img)) continue;
    $out[] = ['title' => (string)($p['title'] ?? ''), 'img' => $img, 'url' => trim((string)($p['live_url'] ?? ''))];
    if (count($out) >= $max) break;
  }
  return $out;
}
