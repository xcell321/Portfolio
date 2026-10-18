<header class="hero" id="top">
  <canvas id="heroDots" aria-hidden="true"></canvas>
  <div class="orb orb-1" aria-hidden="true"></div>
  <div class="orb orb-2" aria-hidden="true"></div>
  <div class="hero-vignette" aria-hidden="true"></div>
  <div class="hero-fade" aria-hidden="true"></div>
  <div class="wrap hero-inner">
  <!-- top meta row -->
  <div class="hero-meta">
    <div class="pills">
      <a class="pill" href="#contact"><span class="dot"></span><?=e(token('hero.badge'))?> <?=icon('arrow',14)?></a>
      <button class="pill like<?=!empty($liked_now)?' liked':''?>" id="likePillTop" onclick="like(event)" data-sound="none" data-on="<?=e(token('like.on','You liked this'))?>" data-off="<?=e(token('like.off','Like this portfolio'))?>" title="Like" aria-label="<?=!empty($liked_now)?'Unlike':e(token('like.off','Like this portfolio'))?>" aria-pressed="<?=!empty($liked_now)?'true':'false'?>"><?=icon('heart',14)?> <span id="likeLabelTop"><?=!empty($liked_now)?e(token('like.on','You liked this')):e(token('like.off','Like this portfolio'))?></span> <span class="like-num" id="likeNumWrap"><span id="likeNumTop"><?=$like_total?></span></span></button>
    </div>
    <div class="meta-mono"><?=e(strtoupper(token('location')))?> &nbsp;·&nbsp; <span id="clock">--:--</span> &nbsp;·&nbsp; <span id="statusLine"><?=e(token('hero.status_line'))?></span></div>
  </div>

  <div class="hero-grid">
    <div class="hero-left">
      <div class="script"><?=e(token('hero.welcome', 'welcome back! still ' . token('first_name')))?><svg class="squiggle-svg" viewBox="0 0 64 24" fill="none" aria-hidden="true"><path pathLength="1" d="M2 12c8-8 14 6 22-2s12-6 18 0 12 4 18-2m0 0-6-4m6 4-3 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <?php
      $rot = array_values((array)token('hero.rot', ['Scale.', 'Inspire.', 'Evolve.']));
      $rot0 = $rot[0] ?? 'Inspire.';
      // ghost = tallest word — it sets the holder width;
      // if the ghost is too short, taller words get clipped (overflow:hidden)
      $rotLong = (string)$rot0;
      foreach ($rot as $w) { if (strlen((string)$w) > strlen($rotLong)) $rotLong = (string)$w; }
      ?>
      <h1 class="mega"><span class="rise-line" style="--rise-i:0"><span><?=str_replace('&amp;', '<span class="amp">&amp;</span>', e(token('hero.line1', 'I design & build')))?></span></span><span class="rise-line" style="--rise-i:1"><span><?=e(token('hero.line2', 'digital products'))?></span></span><span class="rise-line" style="--rise-i:2"><span><?=e(token('hero.line3', 'that'))?> <span class="rot-holder scale" id="rotHolder"><span class="rot-ghost" aria-hidden="true"><?=e($rotLong)?></span><span class="rot-view" aria-hidden="true"><span class="rot-word in" id="rotWord" data-words="<?=e(json_encode($rot))?>"><?=e($rot0)?></span></span><span class="sr-only" id="rotSr"><?=e($rot0)?></span></span></span></span></h1>
    </div>

    <div class="hero-right">
      <div class="photo-wrap">
        <div class="spin-badge" id="stickerBadge" onclick="popSticker(event)" title="Pssst… click me" style="cursor:pointer" aria-hidden="true">
          <svg viewBox="0 0 120 120"><defs><path id="cir" d="M60,60 m-44,0 a44,44 0 1,1 88,0 a44,44 0 1,1 -88,0"/></defs>
          <text><textPath href="#cir"><?=e(token('hero.sticker'))?></textPath></text></svg>
          <span class="wave"><?=icon('hand',30)?></span>
        </div>
        <div class="photo-card" id="photoCard" title="<?=e(token('hero.hover'))?>">
          <div class="photo"><img class="layer top" draggable="false" src="<?=e(token('photo.duty'))?>" style="object-position:<?=e(token('photo.duty_pos'))?>;--z:1.5" alt="<?=e(token('owner_name'))?> on duty" onerror="this.remove()"><img class="layer bot" draggable="false" src="<?=e(token('photo.off'))?>" style="object-position:<?=e(token('photo.off_pos'))?>;--z:1.1" alt="<?=e(token('owner_name'))?> off duty" onerror="this.remove()"><div class="photo-glow" id="photoGlow" aria-hidden="true"></div><div class="photo-foot"><div><b><?=e(token('owner_name'))?></b><small><?=e(token('role'))?></small></div><span class="onduty" id="dutyBadge" data-on="<?=e(token('hero.duty_on'))?>" data-off="<?=e(token('hero.duty_off'))?>"><?=e(token('hero.duty_on'))?></span></div></div>
        </div>
        <p class="hoverme" aria-hidden="true"><svg viewBox="0 0 40 30" fill="none" aria-hidden="true"><path d="M38 25C24 27 10 21 8 4m0 0-5 6m5-6 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><?=e(token('hero.hover'))?></p>
      </div>
    </div>
  </div>

  <div class="hero-split">
    <div class="hero-split-l">
      <p class="hero-sub"><b><?=e(token('hero.sub_role'))?></b> <?=e(token('hero.sub_place'))?> <?=e(token('hero.desc'))?></p>
      <div class="cta-row">
        <a class="btn talk cta-work" href="#projects"><?=e(preg_replace('/\s*↓\s*$/', '', token('hero.cta_work')))?> <span class="cta-arr"><?=icon('down',14)?></span></a>
        <button type="button" class="copy-email" id="copyEmail" data-email="<?=e(token('email'))?>" aria-label="Copy email address <?=e(token('email'))?>">
          <span class="copy-ic">
            <span class="copy-ic-copy"><?=icon('copy',15)?></span>
            <span class="copy-ic-ok"><?=icon('check',15)?></span>
          </span>
          <span class="copy-txt"><?=e(token('email'))?></span>
        </button>
      </div>
    </div>
    <div class="hero-split-r">
      <?php foreach (token('stats', []) as $s): ?>
        <div class="stat"><h3><?=e($s['num'])?></h3><p><?=e($s['label'])?></p></div>
      <?php endforeach; ?>
    </div>
  </div>
  </div><!-- /hero-inner -->
</header>

<div class="marquee"><div class="mq" id="mq"></div></div>
