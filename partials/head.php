<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(token('owner_name'))?> | <?=e(token('role'))?></title>
<!-- Self-hosted fonts (assets/css/fonts.css + assets/fonts/*.woff2) — walay Google Fonts
     requests, so dili na mo-usab ang font bisan spam refresh o offline -->
<link rel="preload" as="font" type="font/woff2" crossorigin href="assets/fonts/caveat-latin.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="assets/fonts/bricolage-grotesque-latin.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="assets/fonts/inter-latin.woff2">
<link rel="stylesheet" href="assets/css/fonts.css?v=20261018a">
<link rel="stylesheet" href="assets/css/style.css?v=20261018h">
<script>try{var _a=(localStorage.getItem('acc')||'#d4fd73').toLowerCase();var _ok=['#d4fd73','#6ee7b7','#7dd3fc','#c4b5fd','#f9a8d4','#cf8a4b','#3b6fe0'].indexOf(_a)>-1;document.documentElement.style.setProperty('--acc',_ok?_a:'#d4fd73');if(!_ok){try{localStorage.setItem('acc','#d4fd73')}catch(_e){}}}catch(e){}</script>
</head>
<body>
<header class="site-header"><div class="header-pt">
<nav class="nav-in" aria-label="Primary">
  <a class="logo" href="#top" aria-label="<?=e(token('owner_name'))?> — back to top"><img src="assets/img/logo.svg" alt="" class="logo-img"><span class="logo-txt"><?=e(token('owner_name'))?></span></a>
  <ul class="links" id="navLinks">
    <li><a href="#about"><?=token('nav.about')?></a></li>
    <li><a href="#projects"><?=token('nav.work')?></a></li>
    <li><a href="#timeline"><?=token('nav.journey')?></a></li>
    <li><a href="#certs"><?=token('nav.certs')?></a></li>
    <li><a href="#guestbook"><?=token('nav.guestbook')?></a></li>
    <li><a href="#contact"><?=token('nav.contact')?></a></li>
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
    <a href="#contact" class="btn talk"><?=token('nav.cta')?></a>
    <button class="burger" onclick="toggleNav()" aria-label="Open menu" aria-expanded="false"><?=icon('menu',20)?></button>
  </div>
</nav>
</div></header>
