<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(token('owner_name'))?> | <?=e(token('role'))?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&family=Caveat:wght@700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css?v=20261015o">
<script>try{var _a=(localStorage.getItem('acc')||'#d4fd73').toLowerCase();var _ok=['#d4fd73','#6ee7b7','#7dd3fc','#c4b5fd','#f9a8d4','#fdba74','#fde047'].indexOf(_a)>-1;document.documentElement.style.setProperty('--acc',_ok?_a:'#d4fd73');if(!_ok){try{localStorage.setItem('acc','#d4fd73')}catch(_e){}}}catch(e){}</script>
</head>
<body>
<header class="site-header"><div class="header-pt">
<nav class="nav-in" aria-label="Primary">
  <a class="logo" href="#top" aria-label="Edsiel Ursal — back to top"><img src="assets/img/logo.svg" alt="" class="logo-img"><span class="logo-txt"><?=e(token('owner_name'))?></span></a>
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
        <div class="theme-title">MAKE IT YOURS</div>
        <div class="swatches" id="swatches"></div>
        <div class="theme-note"><b id="themeName">Lime</b> · saved for your next visit</div>
      </div>
    </div>
    <button class="icon-btn icon-square" id="soundBtn" onclick="toggleSound(event)" title="Sound off" aria-label="Turn sound effects on" aria-pressed="false" data-sound="none"><?=icon('mute',16)?></button>
    <button class="icon-btn cmd-btn" onclick="document.getElementById('pal').classList.add('open')" title="Command menu" aria-label="Open command menu"><?=icon('command',16)?> <kbd>Ctrl K</kbd></button>
    <button class="icon-btn icon-square hand-btn" title="hi" aria-label="Say hi"><?=icon('hand',16)?></button>
    <a href="#contact" class="btn talk"><?=token('nav.cta')?></a>
    <button class="burger" onclick="toggleNav()" aria-label="Open menu" aria-expanded="false"><?=icon('menu',20)?></button>
  </div>
</nav>
</div></header>
