<?php
// Projects — sticky scrollytelling layout (ported from the "Selected work" design).
$slug   = function (string $t): string { return trim(strtolower(preg_replace('/[^a-z0-9]+/i', '-', $t)), '-'); };
$domain = (string)token('work.preview_domain', 'example.com');

// Walkthrough pan: --pan is a % of the image height, duration follows its rendered height.
$pan = function (string $rel): array {
    $abs = __DIR__ . '/../' . $rel;
    if ($rel === '' || !is_file($abs)) return ['', ''];
    $sz = @getimagesize($abs);
    if (!$sz || empty($sz[0]) || empty($sz[1])) return ['', ''];
    $p = -(1 - 0.625 * $sz[0] / $sz[1]) * 100;
    $d = min(34, max(10, (720 * $sz[1] / $sz[0]) / 173));
    return [round($p, 3) . '%', round($d, 2) . 's'];
};

// Placeholder walkthrough for projects without a screenshot yet.
$mock = function (array $p, string $ic, int $dur): string {
    ob_start(); ?>
      <div class="pan" style="--pan-dur:<?=$dur?>s">
        <div class="mock-nav"><span class="mono"><?=e($p['title'])?></span><span class="mock-pill"></span></div>
        <div class="mock-hero">
          <span class="mock-ic"><?=icon($ic, 22)?></span>
          <h4 class="mock-h"><?=e($p['title'])?></h4>
          <span class="mock-btn"></span>
        </div>
        <div class="mock-cards"><span></span><span></span><span></span></div>
        <div class="mock-lines">
          <span class="mock-bar" style="width:88%"></span><span class="mock-bar" style="width:70%"></span>
          <span class="mock-bar" style="width:94%"></span><span class="mock-bar" style="width:60%"></span>
        </div>
        <div class="mock-cards"><span></span><span></span></div>
      </div>
    <?php return ob_get_clean();
};
?>
<section id="projects" class="work-sec"><div class="wrap">

  <div class="work-head rv">
    <?=eyebrow(token('work.kicker'))?>
    <div class="work-head-row">
      <div class="work-head-main">
        <h2 class="title"><?=split_title(token('work.title'), 2)?></h2>
        <p class="work-lead"><?=e(token('work.lead'))?></p>
      </div>
      <div class="work-count"><span class="mono"><?=sprintf('%02d', count($projects))?></span> <?=e(token('work.count'))?></div>
    </div>
  </div>

  <div class="work-grid">
    <div class="work-list">
      <?php foreach ($projects as $i => $p):
        $ic   = $p['icon'] ?? icon_for_emoji($p['emoji'] ?? null);
        $num  = $p['num'] ?? sprintf('%02d', $i + 1);
        $sl   = $slug((string)$p['title']);
        $img  = trim((string)($p['image'] ?? ''));
        $has  = $img !== '' && is_file(__DIR__ . '/../' . $img);
        $mob  = trim((string)($p['image_mobile'] ?? ''));
        $hm   = $mob !== '' && is_file(__DIR__ . '/../' . $mob);
        $dur  = 12 + $i * 3; ?>
      <article class="work-item<?=$i === 0 ? ' on' : ''?>" id="project-<?=e($sl)?>" data-index="<?=$i?>">

        <div class="work-inline" aria-hidden="true">
          <div class="il-frame">
            <div class="il-bar"></div>
            <div class="il-screen">
              <?php if ($has): ?>
              <img class="il-img" src="<?=e($img)?>" alt="<?=e($p['title'])?> preview" loading="lazy" decoding="async">
              <?php else: ?><?=$mock($p, $ic, $dur)?><?php endif; ?>
            </div>
          </div>
          <div class="il-phone">
            <?php if ($hm): ?><img src="<?=e($mob)?>" alt="<?=e($p['title'])?> mobile preview" loading="lazy" decoding="async"><?php else: ?><?=icon($ic, 28)?><?php endif; ?>
          </div>
        </div>

        <div class="work-meta">
          <span class="mono acc"><?=e($num)?></span><span class="rule"></span>
          <span><?=e($p['role'] ?? '')?></span><span class="slash">/</span><span class="mono"><?=e($p['year'] ?? '')?></span>
        </div>
        <h3 class="work-title"><?=e($p['title'])?></h3>
        <p class="work-sum"><?=e($p['summary'] ?? $p['desc'] ?? '')?></p>
        <?php if (!empty($p['long_desc'])): ?><p class="work-desc"><?=e($p['long_desc'])?></p><?php endif; ?>
        <ul class="work-tags" aria-label="Tech stack">
          <?php foreach (explode(',', $p['tags'] ?? '') as $tg): if (trim($tg)): ?><li class="tag"><?=e(trim($tg))?></li><?php endif; endforeach; ?>
        </ul>
        <div class="work-actions">
          <a class="btn" href="case-study.php?p=<?=e($sl)?>"><?=e(token('work.case_btn'))?> <?=icon('arrow_right', 16)?></a>
          <a class="btn ghost" <?=ext($p['live_url'] ?: '#')?>><?=e(token('work.live_btn'))?> <?=icon('arrow', 16)?></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <div class="work-preview-wrap">
      <div class="work-sticky">
        <div class="work-preview">
          <div class="glow"></div>
          <div class="stage">
            <div class="browser">
              <div class="chrome">
                <div class="url"><span class="url-text"><?=e($slug((string)($projects[0]['title'] ?? '')).'.'.$domain)?></span></div>
              </div>
              <div class="screen">
                <?php foreach ($projects as $i => $p):
                  $ic  = $p['icon'] ?? icon_for_emoji($p['emoji'] ?? null);
                  $img = trim((string)($p['image'] ?? ''));
                  $has = $img !== '' && is_file(__DIR__ . '/../' . $img);
                  [$panVal, $panDur] = $has ? $pan($img) : ['', '']; ?>
                <div class="shot<?=$i === 0 ? ' on' : ''?><?=$has ? ' shot--img' : ''?>" data-index="<?=$i?>" data-domain="<?=e($slug((string)$p['title']).'.'.$domain)?>">
                  <?php if ($has): ?>
                  <img class="shot-img" src="<?=e($img)?>" alt="<?=e($p['title'])?> — full page preview" loading="lazy" decoding="async"<?=($panVal !== '' ? ' style="--pan:'.$panVal.';--pan-duration:'.$panDur.'"' : '')?>>
                  <?php else: ?><?=$mock($p, $ic, 12 + $i * 3)?><?php endif; ?>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="phone">
              <?php foreach ($projects as $i => $p):
                $ic  = $p['icon'] ?? icon_for_emoji($p['emoji'] ?? null);
                $mob = trim((string)($p['image_mobile'] ?? ''));
                $hm  = $mob !== '' && is_file(__DIR__ . '/../' . $mob); ?>
              <div class="phone-shot<?=$i === 0 ? ' on' : ''?>" data-index="<?=$i?>">
                <?php if ($hm): ?><img class="phone-img" src="<?=e($mob)?>" alt="<?=e($p['title'])?> — mobile screenshot" loading="lazy" decoding="async"><?php else: ?><?=icon($ic, 40)?><?php endif; ?>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="track">
            <?php foreach ($projects as $i => $p):
              $num = $p['num'] ?? sprintf('%02d', $i + 1); ?>
            <button class="tab<?=$i === 0 ? ' on' : ''?>" type="button" data-index="<?=$i?>" aria-current="<?=$i === 0 ? 'true' : 'false'?>" aria-label="Show <?=e($p['title'])?>">
              <span class="bar"><span class="fill"></span></span>
              <span class="lbl"><span class="mono"><?=e($num)?></span><?=e($p['title'])?></span>
            </button>
            <?php endforeach; ?>
          </div>
          <p class="note"><?=e(token('work.preview_note'))?></p>
        </div>
      </div>
    </div>
  </div>
</div></section>
