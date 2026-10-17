<?php
// DB connection only. Walay queries diri.
// WALAY hardcoded credentials dinhi — gikan sa environment variables
// (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT) o sa config/db.local.php.
// Priority: env vars > db.local.php. Kung walay config, null ang $pdo ug
// mo-fallback gracefully ang site (fallback content, dili ma-crash).

function db_connect(): ?PDO {
  static $pdo = null;
  static $done = false;
  if ($done) return $pdo;
  $done = true;

  $cfg = [];
  $local = __DIR__ . '/../config/db.local.php';
  if (is_file($local)) {
    $c = require $local;
    if (is_array($c)) $cfg = $c;
  }
  foreach (['DB_HOST' => 'host', 'DB_NAME' => 'name', 'DB_USER' => 'user', 'DB_PASS' => 'pass', 'DB_PORT' => 'port'] as $env => $key) {
    $v = getenv($env);
    if ($v !== false && $v !== '') $cfg[$key] = $v;
  }
  if (empty($cfg['host']) || empty($cfg['name'])) return null;

  try {
    $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4";
    if (!empty($cfg['port'])) $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $cfg['user'] ?? '', $cfg['pass'] ?? '', [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_TIMEOUT => 5 // kung mo-hang ang DB host, dili mag-stall ang page
    ]);
  } catch (Exception $ex) {
    $pdo = null; // graceful: site mogana gihapon via fallback data
  }
  return $pdo;
}
