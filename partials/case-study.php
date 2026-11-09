<?php
// Case study view. Data comes from case-study.php; this file only renders.
$img    = trim((string)($current['image'] ?? ''));
$hasImg = $img !== '' && is_file(__DIR__ . '/../' . $img);
$dims   = $hasImg ? @getimagesize(__DIR__ . '/../' . $img) : null;
$ratio  = ($dims && !empty($dims[0]) && !empty($dims[1])) ? $dims[0] / $dims[1] : 16 / 10;
// Full-page walkthroughs are very tall; keep the inline frame sane and let the
// lightbox scroll through the whole screenshot.
$frameRatio = max($ratio, 0.8);
$live   = trim((string)($current['live_url'] ?? ''));
$hasLive = $live !== '' && $live !== '#';
$tags   = [];
foreach (explode(',', (string)($current['tags'] ?? '')) as $tag) {
  $tag = trim($tag);
  if ($tag !== '') $tags[] = $tag;
}
$stack = $tags ? implode(', ', $tags) : '—';
$nextSlug = $next ? $slug((string)$next['title']) : '';

$techIcon = static function (string $tag): string {
  static $map = [
    'html' => 'html5/html5-original.svg',
    'css' => 'css3/css3-original.svg',
    'javascript' => 'javascript/javascript-original.svg',
    'php' => 'php/php-original.svg',
    'mysql' => 'mysql/mysql-original.svg',
    'figma' => 'figma/figma-original.svg',
    'bootstrap' => 'bootstrap/bootstrap-original.svg',
    'react' => 'react/react-original.svg',
    'react native' => 'react/react-original.svg',
    'next.js' => 'nextjs/nextjs-original.svg',
    'typescript' => 'typescript/typescript-original.svg',
    'node.js' => 'nodejs/nodejs-original.svg',
    'tailwind css' => 'tailwindcss/tailwindcss-original.svg',
    'laravel' => 'laravel/laravel-original.svg',
    'flutter' => 'flutter/flutter-original.svg',
    'python' => 'python/python-original.svg',
    'git' => 'git/git-original.svg',
    'github' => 'github/github-original.svg',
    'java' => 'java/java-original.svg',
    'c#' => 'csharp/csharp-original.svg',
  ];
  $key = strtolower(trim($tag));
  if (!isset($map[$key])) return icon('code', 16);
  return '<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/' . $map[$key] . '" alt="" width="16" height="16" loading="lazy" decoding="async">';
};
?>
<main class="case-main"><div class="case-wrap">

  <a class="case-back" href="index.php#projects"><?=icon('arrow_left', 16)?><?=e(token('case.back'))?></a>

  <header class="case-head">
    <p class="case-kicker"><?=e(token('case.kicker'))?></p>
    <h1 class="case-title"><?=e($current['title'])?></h1>
    <?php if ($tagline !== ''): ?><p class="case-sub"><?=e($tagline)?></p><?php endif; ?>
    <?php if ($hasLive): ?>
    <a class="btn case-visit" <?=ext($live)?>><?=e(token('case.visit'))?> <?=icon('arrow_up_right', 16)?></a>
    <?php endif; ?>
  </header>

  <dl class="case-meta">
    <div><dt><?=e(token('case.year'))?></dt><dd><?=e($current['year'] ?? '')?></dd></div>
    <div><dt><?=e(token('case.role'))?></dt><dd><?=e($current['role'] ?? '')?></dd></div>
    <div><dt><?=e(token('case.stack'))?></dt><dd><?=e($stack)?></dd></div>
  </dl>

  <section class="case-section" aria-labelledby="caseOverview">
    <h2 id="caseOverview"><?=e(token('case.overview'))?></h2>
    <p class="case-overview"><?=e($overview !== '' ? $overview : $tagline)?></p>
  </section>

  <?php if ($tags): ?>
  <section class="case-section case-stack" aria-labelledby="caseStack">
    <h2 id="caseStack"><?=e(token('case.built'))?></h2>
    <ul class="case-tags">
      <?php foreach ($tags as $tag): ?>
      <li class="case-tag"><?=$techIcon($tag)?><?=e($tag)?></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endif; ?>

  <section class="case-walk" aria-labelledby="caseWalkthrough">
    <h2 id="caseWalkthrough"><?=e(token('case.walkthrough'))?></h2>
    <?php if ($hasImg): ?>
    <button class="case-shot-btn" type="button" data-case-shot aria-label="<?=e(token('case.shot_label'))?>">
      <span class="case-shot-frame" style="aspect-ratio:<?=round($frameRatio, 6)?>">
        <img class="case-shot-img" src="<?=e($img)?>" alt="<?=e($current['title'])?> walkthrough" loading="lazy" decoding="async">
        <span class="case-shot-label"><span><?=e(token('case.expand'))?></span></span>
      </span>
    </button>
    <?php else: ?>
    <div class="case-shot-frame case-shot-empty" style="aspect-ratio:16/10">
      <span class="case-shot-mark"><?=icon('layers', 42)?></span>
      <span class="case-shot-empty-title"><?=e($current['title'])?></span>
      <span class="case-shot-empty-sub"><?=e($stack)?></span>
    </div>
    <?php endif; ?>
  </section>

  <?php if ($next): ?>
  <a class="case-next" href="case-study.php?p=<?=e($nextSlug)?>">
    <span>
      <span class="case-next-kicker"><?=e(token('case.next'))?></span>
      <span class="case-next-title"><?=e($next['title'])?></span>
    </span>
    <?=icon('arrow_right', 24)?>
  </a>
  <?php endif; ?>

</div></main>

<?php if ($hasImg): ?>
<div class="case-lightbox" id="caseShotModal" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?=e($current['title'])?> screenshot">
  <div class="case-lb-bar">
    <span class="case-lb-title"><?=e($current['title'])?></span>
    <div class="case-lb-actions">
      <?php if ($hasLive): ?>
      <a class="case-lb-live" <?=ext($live)?>><?=e(token('case.view_live'))?><?=icon('arrow_up_right', 16)?></a>
      <?php endif; ?>
      <button class="case-lb-close" type="button" data-case-close aria-label="<?=e(token('case.close'))?>"><?=e(token('case.close_btn'))?><?=icon('x', 16)?></button>
    </div>
  </div>
  <div class="case-lb-scroll" data-lenis-prevent>
    <div class="case-lb-inner">
      <img src="<?=e($img)?>" alt="<?=e($current['title'])?> full screenshot" width="<?=e($dims[0] ?? 1440)?>" height="<?=e($dims[1] ?? 2560)?>" decoding="async">
    </div>
  </div>
</div>
<script src="assets/js/case-study.js?v=20261104a"></script>
<?php endif; ?>
