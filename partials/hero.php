<header class="hero" id="top">
  <canvas id="heroDots" aria-hidden="true"></canvas>
  <div class="orb orb-1" aria-hidden="true"></div>
  <div class="orb orb-2" aria-hidden="true"></div>
  <div class="hero-vignette" aria-hidden="true"></div>
  <div class="hero-fade" aria-hidden="true"></div>
  <div class="wrap hero-inner">
  <!-- top meta row parehas sa screenshot -->
  <div class="hero-meta">
    <div class="pills">
      <a class="pill" href="#contact"><span class="dot"></span><?=e(token('hero.badge'))?> <?=icon('arrow',14)?></a>
      <button class="pill like<?=!empty($liked_now)?' liked':''?>" id="likePillTop" onclick="like(event)" data-sound="none" title="Like" aria-label="<?=!empty($liked_now)?'Unlike':'Like'?> this portfolio" aria-pressed="<?=!empty($liked_now)?'true':'false'?>"><?=icon('heart',14)?> <span id="likeLabelTop"><?=!empty($liked_now)?'You liked this':'Like this portfolio'?></span> <span class="like-num" id="likeNumWrap"><span id="likeNumTop"><?=$like_total?></span></span></button>
    </div>
    <div class="meta-mono"><?=e(strtoupper(token('location')))?> &nbsp;·&nbsp; <span id="clock">--:--</span> &nbsp;·&nbsp; BUILDING THINGS <?=icon('spark',13)?></div>
  </div>

  <div class="hero-grid">
    <div class="hero-left">
      <div class="script"><?=e(token('hero.welcome', 'welcome back! still ' . token('first_name')))?><svg class="squiggle-svg" viewBox="0 0 64 24" fill="none" aria-hidden="true"><path d="M2 12c8-8 14 6 22-2s12-6 18 0 12 4 18-2m0 0-6-4m6 4-3 6" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
      <h1 class="mega"><span class="rise-line" style="--rise-i:0"><span>I design <span class="amp">&amp;</span> build</span></span><span class="rise-line" style="--rise-i:1"><span>digital products</span></span><span class="rise-line" style="--rise-i:2"><span>that <span class="rot-holder scale" id="rotHolder"><span class="rot-ghost" aria-hidden="true">scale.</span><span class="rot-view" aria-hidden="true"><span class="rot-word" id="rotWord"><?=e(token('hero.line3word', 'ship.'))?></span></span><span class="sr-only" id="rotSr"><?=e(token('hero.line3word', 'ship.'))?></span></span></span></span></h1>
    </div>

    <div class="hero-right">
      <div class="photo-wrap">
        <div class="spin-badge" id="stickerBadge" onclick="popSticker(event)" title="Pssst… click me" style="cursor:pointer" aria-hidden="true">
          <svg viewBox="0 0 120 120"><defs><path id="cir" d="M60,60 m-44,0 a44,44 0 1,1 88,0 a44,44 0 1,1 -88,0"/></defs>
          <text><textPath href="#cir">OPEN TO WORK • FREELANCING • COLLABS • </textPath></text></svg>
          <span class="wave"><?=icon('hand',30)?></span>
        </div>
        <div class="photo-card" id="photoCard" title="hover me">
          <div class="photo"><img class="layer top" draggable="false" src="<?=e(token('photo.duty'))?>" style="object-position:<?=e(token('photo.duty_pos'))?>;--z:1.5" alt="<?=e(token('owner_name'))?> on duty" onerror="this.remove()"><img class="layer bot" draggable="false" src="<?=e(token('photo.off'))?>" style="object-position:<?=e(token('photo.off_pos'))?>;--z:1.1" alt="<?=e(token('owner_name'))?> off duty" onerror="this.remove()"><div class="photo-glow" id="photoGlow" aria-hidden="true"></div><div class="photo-foot"><div><b><?=e(token('owner_name'))?></b><small><?=e(token('role'))?></small></div><span class="onduty" id="dutyBadge">ON DUTY</span></div></div>
        </div>
        <div class="hoverme"><svg width="34" height="26" viewBox="0 0 34 26" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M28 22 C 18 22, 9 17, 8 6 M8 6 l-5 7 M8 6 l7 4"/></svg><span>hover me</span></div>
      </div>
    </div>
  </div>

  <div class="hero-split">
    <div class="hero-split-l">
      <p class="hero-sub"><b><?=e(token('role'))?> & Designer</b> from the Philippines. <?=e(token('hero.desc'))?></p>
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
