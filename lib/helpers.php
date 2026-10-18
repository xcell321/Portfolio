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
