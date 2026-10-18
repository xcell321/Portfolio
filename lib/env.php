<?php
// Minimal .env loader (no dependencies). Real environment variables win.

function env_load(?string $path = null): void {
  static $done = false;
  if ($done) return;
  $done = true;
  $path = $path ?: dirname(__DIR__) . '/.env';
  if (!is_file($path)) return;
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if (!is_array($lines)) return;
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#' || $line[0] === ';') continue;
    $pos = strpos($line, '=');
    if ($pos === false) continue;
    $key = trim(substr($line, 0, $pos));
    $val = trim(substr($line, $pos + 1));
    if ($key === '') continue;
    $len = strlen($val);
    if ($len >= 2 && ($val[0] === '"' || $val[0] === "'") && $val[$len - 1] === $val[0]) {
      $val = substr($val, 1, -1);
    }
    if (getenv($key) !== false && getenv($key) !== '') continue;
    putenv($key . '=' . $val);
    $_ENV[$key] = $val;
    $_SERVER[$key] = $val;
  }
}

function env_get(string $key, $default = '') {
  env_load();
  $v = getenv($key);
  if ($v === false || $v === '') return $default;
  return $v;
}
