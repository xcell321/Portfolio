<?php
// AI chat endpoint — Google Gemini streaming (SSE). Key is read from .env.

require __DIR__ . '/lib/env.php';
require __DIR__ . '/lib/helpers.php';

header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('X-Accel-Buffering: no');
while (ob_get_level() > 0) { ob_end_flush(); }
@ini_set('zlib.output_compression', '0');
ob_implicit_flush(true);

function sse_data($payload): void {
  echo 'data: ' . (is_string($payload) ? $payload : json_encode($payload)) . "\n\n";
  @flush();
}
function sse_text(string $t): void { sse_data(['t' => $t]); }
function sse_error(string $m): void { sse_data(['error' => $m]); }
function sse_done(): void { echo "data: [DONE]\n\n"; @flush(); }

function ai_map_error(int $code, string $msg = ''): string {
  $m = strtolower($msg);
  if ($code === 429 || str_contains($m, 'quota') || str_contains($m, 'rate limit')) {
    return 'Rate limit exceeded. Please try again in a moment.';
  }
  if ($code === 400 || $code === 403 || str_contains($m, 'api key') || str_contains($m, 'permission')) {
    return 'The AI assistant is misconfigured. Please try again later.';
  }
  return 'The AI assistant is unavailable right now.';
}

function ai_system_prompt(): string {
  $name  = (string)token('owner_name', 'the site owner');
  $first = (string)token('first_name', $name);
  $role  = (string)token('role', 'Full Stack Developer');
  $loc   = (string)token('location', '');
  $email = (string)token('email', '');
  $tag   = (string)token('tagline', '');

  $skills = [];
  foreach ((array)token('skills', []) as $row) {
    if (is_array($row) && count($row) >= 2) $skills[] = $row[0] . ': ' . $row[1];
  }
  $services = implode(', ', array_map('strval', (array)token('services', [])));

  $s  = "You are the friendly AI assistant embedded in {$name}'s portfolio website, chatting with visitors.\n";
  $s .= "About {$first}: role {$role}";
  if ($loc !== '') $s .= ", based in {$loc}";
  $s .= ".\n";
  if ($tag !== '') $s .= "Tagline: {$tag}\n";
  if ($services !== '') $s .= "Services: {$services}\n";
  if ($skills) $s .= "Skills:\n- " . implode("\n- ", $skills) . "\n";
  if ($email !== '') $s .= "Contact email: {$email}\n";
  $s .= "Rules: be concise (a few sentences), warm and professional. Use markdown for lists and code. ";
  $s .= "Only answer questions about {$first}, his work, skills, projects and availability; politely steer unrelated questions back. ";
  $s .= "Never invent projects, prices or availability. If unsure, suggest emailing {$email}.";
  return $s;
}

function ai_model(string $key): string {
  $forced = trim((string)env_get('GEMINI_MODEL', ''));
  if ($forced !== '' && strtolower($forced) !== 'auto') {
    return preg_replace('#^models/#', '', $forced);
  }
  $base  = rtrim((string)env_get('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta'), '/');
  $cache = sys_get_temp_dir() . '/portfolio_ai_model.json';
  if (is_file($cache)) {
    $j = json_decode((string)@file_get_contents($cache), true);
    if (is_array($j) && !empty($j['model']) && (time() - (int)($j['ts'] ?? 0)) < 86400) {
      return (string)$j['model'];
    }
  }

  $best = '';
  $ch = curl_init($base . '/models?pageSize=200&key=' . urlencode($key));
  curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
  $res  = curl_exec($ch);
  $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
  curl_close($ch);

  if ($code === 200 && is_string($res)) {
    $j = json_decode($res, true);
    $list = is_array($j['models'] ?? null) ? $j['models'] : [];
    $rank = null;
    foreach ($list as $m) {
      $id = preg_replace('#^models/#', '', (string)($m['name'] ?? ''));
      $methods = (array)($m['supportedGenerationMethods'] ?? []);
      if (!in_array('streamGenerateContent', $methods, true)) continue;
      if (!preg_match('/flash/i', $id)) continue;
      if (preg_match('/image|tts|audio|embedding|aqa|learnlm|live|vision|thinking/i', $id)) continue;
      $score = (preg_match('/preview|exp/i', $id) ? 1000 : 0) + (preg_match('/lite/i', $id) ? 100 : 0);
      if ($rank === null || $score < $rank) { $rank = $score; $best = $id; }
    }
  }
  if ($best === '') $best = 'gemini-flash-latest';

  if ($best !== 'gemini-flash-latest') {
    @file_put_contents($cache, json_encode(['model' => $best, 'ts' => time()]));
  }
  return $best;
}

$key = (string)env_get('GEMINI_API_KEY', env_get('AI_API_KEY', ''));
if ($key === '') {
  sse_error('The AI assistant is not configured yet.');
  sse_done();
  exit;
}

$in    = json_decode((string)file_get_contents('php://input'), true);
$msgs  = (is_array($in) && is_array($in['messages'] ?? null)) ? $in['messages'] : [];

$contents = [];
foreach ($msgs as $m) {
  if (!is_array($m)) continue;
  $role = (string)($m['role'] ?? 'user');
  if ($role === 'system' || $role === 'developer') continue;
  $text = trim((string)($m['content'] ?? ''));
  if ($text === '') continue;
  if (strlen($text) > 4000) $text = substr($text, 0, 4000);
  $contents[] = ['role' => $role === 'assistant' ? 'model' : 'user', 'parts' => [['text' => $text]]];
}
if (count($contents) > 20) $contents = array_slice($contents, -20);
if (!$contents) {
  sse_error('Please type a message first.');
  sse_done();
  exit;
}
if ($contents[0]['role'] !== 'user') {
  array_unshift($contents, ['role' => 'user', 'parts' => [['text' => 'Hello']]]);
}

$base  = rtrim((string)env_get('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta'), '/');
$model = ai_model($key);
$url   = $base . '/models/' . $model . ':streamGenerateContent?alt=sse&key=' . urlencode($key);
$body  = json_encode([
  'systemInstruction' => ['parts' => [['text' => ai_system_prompt()]]],
  'contents'          => $contents,
  'generationConfig'  => ['temperature' => 0.7, 'maxOutputTokens' => 2048],
]);

$buf = ''; $sawText = false; $errored = false;
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
  CURLOPT_POSTFIELDS     => $body,
  CURLOPT_TIMEOUT        => 90,
  CURLOPT_CONNECTTIMEOUT => 15,
  CURLOPT_WRITEFUNCTION  => function ($ch, $chunk) use (&$buf, &$sawText, &$errored) {
    $buf .= $chunk;
    while (($pos = strpos($buf, "\n")) !== false) {
      $line = trim(substr($buf, 0, $pos));
      $buf  = substr($buf, $pos + 1);
      if ($line === '' || strncmp($line, 'data:', 5) !== 0) continue;
      $data = trim(substr($line, 5));
      if ($data === '' || $data === '[DONE]') continue;
      $j = json_decode($data, true);
      if (!is_array($j)) continue;
      if (isset($j['error'])) {
        sse_error(ai_map_error(0, (string)($j['error']['message'] ?? '')));
        $errored = true;
        return -1;
      }
      $t = $j['candidates'][0]['content']['parts'][0]['text'] ?? '';
      if ($t !== '') { $sawText = true; sse_text((string)$t); }
    }
    return strlen($chunk);
  },
]);
$ok   = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
$cerr = curl_error($ch);
curl_close($ch);

if (!$errored) {
  if ($ok === false && $cerr !== '') {
    sse_error('The AI assistant is unavailable right now.');
  } elseif (!$sawText && $code >= 400) {
    sse_error(ai_map_error($code));
  } elseif (!$sawText && $code >= 200 && $code < 300) {
    sse_error('The AI assistant returned an empty response.');
  }
}
sse_done();
