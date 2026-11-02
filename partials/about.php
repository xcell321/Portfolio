<?php
// About — intro, feature cards, skills, toolkit and a small terminal.
$tip   = (string)token('about.skills_tip');

// Normalise skill rows: [name, "a / b / c", description, highlighted word]
$skRows = [];
foreach ((array)token('skills', []) as $r) {
  if (!is_array($r) || !isset($r[0], $r[1])) continue;
  $skRows[] = [
    'name'  => (string)$r[0],
    'words' => array_values(array_filter(array_map('trim', explode('/', (string)$r[1])))),
    'desc'  => (string)($r[2] ?? ''),
    'tip'   => (string)($r[3] ?? ''),
  ];
}

// Terminal content is built here so it always matches the tokens and the DB.
$projLines = [];
foreach ($projects as $i => $p) {
  $projLines[] = '  ' . sprintf('%02d', $i + 1) . '  ' . ($p['title'] ?? '') . '  (' . ($p['year'] ?? '') . ')';
}
$skillLines = array_map(fn($r) => '  ' . $r['name'] . ' — ' . implode(', ', $r['words']), $skRows);
$term = [
  'label'  => (string)token('about.term_label'),
  'prompt' => (string)token('about.term_prompt'),
  'intro'  => array_values((array)token('about.term_intro', [])),
  'cmds'   => array_values((array)token('about.term_cmds', [])),
  'out'    => [
    'help' => [
      'Available commands:',
      '  whoami     who I am',
      '  skills     what I work with',
      '  projects   things I have shipped',
      '  contact    how to reach me',
      '  status     current availability',
      '  sudo       (just try it)',
      '  clear      clear the screen',
    ],
    'whoami' => [
      token('owner_name') . ' — ' . token('role'),
      'Based in ' . token('location') . '. I take products from the first sketch to the shipped release.',
    ],
    'skills' => array_merge(['What I work with:'], $skillLines),
    'projects' => array_merge(['Things I have shipped:'], $projLines),
    'contact' => [
      'email     ' . token('email'),
      'github    ' . token('social.github'),
      'linkedin  ' . token('social.linkedin'),
    ],
    'status' => [
      token('hero.badge'),
      'Remote friendly · replies within one business day.',
    ],
    'sudo' => ['nice try — permission denied.'],
  ],
];
$termJson = json_encode($term, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<section id="about"><div class="wrap">

  <div class="about-head rv">
    <div class="about-head-main">
      <?=eyebrow(token('about.kicker'))?>
      <h2 class="title"><?=split_title(token('about.title'), 3)?></h2>
      <p class="about-lead"><?=e(token('about.lead'))?></p>
    </div>
    <ul class="about-socials">
      <li><a <?=ext(token('social.github'))?>>GitHub<?=icon('arrow',14)?></a></li>
      <li><a <?=ext(token('social.linkedin'))?>>LinkedIn<?=icon('arrow',14)?></a></li>
      <li><a <?=ext(token('social.facebook'))?>>Facebook<?=icon('arrow',14)?></a></li>
    </ul>
  </div>

  <div class="cards3 about-cards">
    <div class="rv" data-rvd="0"><div class="card spot"><span class="spot-glow" aria-hidden="true"></span><span class="spot-ring" aria-hidden="true"></span><div class="spot-in"><div class="card-ic"><?=icon('layers',24)?></div><h3><?=e(token('about.card1_title'))?></h3><p><?=e(token('about.card1_desc'))?></p></div></div></div>
    <div class="rv" data-rvd="0.06"><div class="card spot"><span class="spot-glow" aria-hidden="true"></span><span class="spot-ring" aria-hidden="true"></span><div class="spot-in"><div class="card-ic"><?=icon('smartphone',24)?></div><h3><?=e(token('about.card2_title'))?></h3><p><?=e(token('about.card2_desc'))?></p></div></div></div>
    <div class="rv" data-rvd="0.12"><div class="card spot"><span class="spot-glow" aria-hidden="true"></span><span class="spot-ring" aria-hidden="true"></span><div class="spot-in"><div class="card-ic"><?=icon('palette',24)?></div><h3><?=e(token('about.card3_title'))?></h3><p><?=e(token('about.card3_desc'))?></p></div></div></div>
  </div>

  <div class="sk rv">
    <div class="sk-head">
      <div><p class="eyebrow"><?=e(token('about.skills_title'))?></p><p class="sk-note"><?=e(token('about.skills_note'))?></p></div>
      <p class="script sk-updated"><?=e(token('about.skills_updated'))?></p>
    </div>
    <div class="sk-rows">
      <?php foreach ($skRows as $i => $r): $words = $r['words']; ?>
      <div class="sk-row<?=$i === 0 ? ' open' : ''?>">
        <h3><button class="sk-head-btn" type="button" aria-expanded="<?=$i === 0 ? 'true' : 'false'?>">
          <span class="sk-label"><span class="mono acc"><?=sprintf('%02d', $i + 1)?></span><span class="sk-slash">/</span><?=e($r['name'])?></span>
          <span class="sk-sum" aria-hidden="true"><?=e(implode(' / ', $words))?></span>
          <span class="sk-meta"><span class="mono sk-count"><?=count($words)?></span><span class="sk-toggle" aria-hidden="true"><?=icon('plus',14)?></span></span>
        </button></h3>
        <div class="sk-body" role="region" aria-label="<?=e($r['name'])?> skills">
          <div class="sk-body-in">
            <?php if ($r['desc']): ?><p class="sk-desc"><?=e($r['desc'])?></p><?php endif; ?>
            <div class="sk-words">
              <?php foreach ($words as $wi => $w): ?>
                <?php if ($wi): ?><span class="sk-sep" aria-hidden="true">/</span><?php endif; ?>
                <button class="sk-word<?=($r['tip'] !== '' && $w === $r['tip']) ? ' has-tip' : ''?>" type="button" aria-expanded="false"><?=e($w)?><?php if ($r['tip'] !== '' && $w === $r['tip']): ?><span class="sk-tip" aria-hidden="true"><svg viewBox="0 0 24 20" fill="none"><path d="M22 3C14 3 6 7 4 17m0 0-3-4m3 4 4-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg><?=e($tip)?></span><?php endif; ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="kit-grid">
    <div class="kit-card kit-tools rv">
      <div class="kit-head">
        <div><p class="eyebrow"><?=e(token('about.toolkit_title'))?></p><p class="kit-note"><?=e(token('about.toolkit_note'))?> <span class="dim"><?=e(token('about.toolkit_sub'))?></span></p></div>
        <button class="kit-tidy" type="button" onclick="tidyUp()" hidden><?=icon('rotate',14)?> <?=e(token('about.toolkit_tidy'))?></button>
      </div>
      <div class="kit-arena" id="toolkit">
        <ul class="kit-chips">
          <?php
          $slugs = ['PHP'=>'php','MySQL'=>'mysql','JavaScript'=>'javascript','Tailwind'=>'tailwindcss','Laravel'=>'laravel','Flutter'=>'flutter','Firebase'=>'firebase','Git'=>'git','Figma'=>'figma','XAMPP'=>'apache','CSS3'=>'css','HTML5'=>'html5'];
          foreach (token('toolkit', []) as $ti => $t): $slug = $slugs[$t] ?? ''; ?>
          <li class="tool" style="--i:<?=$ti?>"><?php if ($slug): ?><img src="https://cdn.simpleicons.org/<?=$slug?>/a8a29e" alt="" width="14" height="14" loading="lazy" onerror="this.remove()"> <?php endif; ?><?=e($t)?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="kit-card rv" data-rvd="0.06">
      <div class="term" id="term" data-term="<?=e($termJson)?>">
        <div class="term-head">
          <span class="term-dot"></span><span class="term-dot"></span><span class="term-dot"></span>
          <span class="mono term-label"><?=e(token('about.term_label'))?></span>
        </div>
        <div class="term-body" id="termBody"></div>
        <div class="term-bar">
          <?php foreach ((array)token('about.term_cmds', []) as $c): ?>
          <button class="term-btn mono" type="button" data-cmd="<?=e($c)?>"><?=e($c)?></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div></section>
