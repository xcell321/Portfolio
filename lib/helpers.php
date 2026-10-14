<?php
// Small helpers. Walay DB diri, walay HTML.

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

/** External link attrs — always new tab para dili matabunan portfolio. */
function ext(string $url): string {
  return 'href="' . e($url) . '" target="_blank" rel="noopener"';
}
