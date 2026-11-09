<?php
$home = $home ?? '';
$page_title = $page_title ?? (token('owner_name') . ' | ' . token('role'));
$page_desc = $page_desc ?? (string)token('tagline');
$hero_active = $hero_active ?? true;
$show_header = $show_header ?? true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e($page_title)?></title>
<meta name="description" content="<?=e($page_desc)?>">
<!-- Self-hosted fonts (assets/css/fonts.css + assets/fonts/*.woff2) — no Google Fonts
          requests, so the font stays stable across refreshes and offline -->
<link rel="preload" as="font" type="font/woff2" crossorigin href="assets/fonts/caveat-latin.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="assets/fonts/bricolage-grotesque-latin.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="assets/fonts/inter-latin.woff2">
<link rel="stylesheet" href="assets/css/fonts.css?v=20261018a">
<link rel="stylesheet" href="assets/css/style.css?v=20261107e">
<script>try{var _a=(localStorage.getItem('acc')||'#d4fd73').toLowerCase();var _ok=['#d4fd73','#6ee7b7','#7dd3fc','#c4b5fd','#f9a8d4','#cf8a4b','#3b6fe0'].indexOf(_a)>-1;document.documentElement.style.setProperty('--acc',_ok?_a:'#d4fd73');if(!_ok){try{localStorage.setItem('acc','#d4fd73')}catch(_e){}}}catch(e){}</script>
<script>/* one device-capability read, before first paint so the orb/blur gating costs no frame.
   lowEnd = skip smooth scroll and heavy decoration; veryLowEnd = additionally thin the dot field. */
try{var _c=navigator.connection||{},_h=navigator.hardwareConcurrency||0,_m=navigator.deviceMemory||0;
var _low=_c.saveData===true||(_h&&_h<=4)||(_m&&_m<=4);
var _vlow=_h<=2||/Android 4|iPhone OS 1[0-2]_/.test(navigator.userAgent);
window.__perf={lowEnd:!!_low,veryLowEnd:!!_vlow};
if(_low)document.documentElement.setAttribute('data-low-end','true');}catch(e){window.__perf={lowEnd:false,veryLowEnd:false}}</script>
</head>
<body<?=$hero_active ? ' data-hero-active' : ''?>>
<?php if ($show_header): ?>
<header class="site-header"><div class="header-pt">
<nav class="nav-in" aria-label="Primary">
  <a class="logo" href="<?=e($home)?>#top" aria-label="<?=e(token('owner_name'))?> — back to top"><img src="assets/img/logo.svg" alt="" class="logo-img"><span class="logo-txt"><?=e(token('owner_name'))?></span></a>
  <ul class="links" id="navLinks">
    <li><a href="<?=e($home)?>#about"><?=token('nav.about')?></a></li>
    <li><a href="<?=e($home)?>#projects"><?=token('nav.work')?></a></li>
    <li><a href="<?=e($home)?>#timeline"><?=token('nav.journey')?></a></li>
    <li><a href="<?=e($home)?>#certs"><?=token('nav.certs')?></a></li>
    <li><a href="<?=e($home)?>#guestbook"><?=token('nav.guestbook')?></a></li>
    <li><a href="<?=e($home)?>#contact"><?=token('nav.contact')?></a></li>
  </ul>
  <div class="nav-icons">
    <div class="theme-wrap">
      <button class="icon-btn icon-square" id="themeDot" onclick="toggleTheme(event)" title="Change accent" aria-label="Accent color"><span class="icon-dot"></span></button>
      <div class="theme-pop" id="themePop">
        <div class="theme-title"><?=e(token('theme.title'))?></div>
        <div class="swatches" id="swatches"></div>
        <div class="theme-note"><b id="themeName">Lime</b> · <?=e(token('theme.note'))?></div>
      </div>
    </div>
    <button class="icon-btn icon-square" id="soundBtn" onclick="toggleSound(event)" title="Sound off" aria-label="Turn sound effects on" aria-pressed="false" data-sound="none"><?=icon('mute',16)?></button>
    <button class="icon-btn cmd-btn" onclick="palOpen()" title="Command menu" aria-label="Open command menu"><?=icon('command',16)?> <kbd>Ctrl K</kbd></button>
    <button class="icon-btn icon-square hand-btn" id="handBtn" onclick="toggleHandTracking(event)" title="Hand tracking" aria-label="Toggle hand tracking" aria-pressed="false"><?=icon('hand',16)?></button>
    <a href="<?=e($home)?>#contact" class="btn talk"><?=token('nav.cta')?></a>
    <button class="burger" onclick="toggleNav()" aria-label="Open menu" aria-expanded="false"><?=icon('menu',20)?></button>
  </div>
</nav>
</div></header>
<?php endif; ?>
