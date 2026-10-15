<footer><div class="wrap">
  <div class="foot"><span>Navigate: About · Work · Journey · Contact</span><span>© 2026 <?=e(token('owner_name'))?>. All rights reserved.</span></div>
  <div class="big tickle" id="bigName" aria-label="<?=e(strtoupper(token('owner_name')))?>"><span>E</span><span>D</span><span>S</span><span>I</span><span>E</span><span>L</span><span>&nbsp;</span><span>U</span><span>R</span><span>S</span><span>A</span><span>L</span></div>
  <p style="color:var(--mut);font-size:12px;margin-top:6px">psst, the letters are ticklish.</p></div></footer>

<a class="chat-fab" href="#contact" title="Chat" aria-label="Chat"><?=icon('chat',20)?></a>
<button class="btn" id="toTop" onclick="toTop()">↑ Top</button>
<div id="popToast" class="pop-toast"></div>
<div id="pal"><div class="pal-ov" onclick="palClose()"></div><div class="pal-box" role="dialog" aria-modal="true" aria-label="Command menu"><div class="pal-search"><?=icon('search',16)?><input id="palInput" placeholder="Search sections, projects, actions…" role="combobox" aria-expanded="true" aria-controls="palList" autocomplete="off" oninput="palFilter(this.value)" onkeydown="palKey(event)"><kbd>ESC</kbd></div><div id="palList" role="listbox" data-lenis-prevent><p class="pal-group">Navigate</p><a href="#about" role="option" data-t="about"><?=icon('hash',16)?><span>About</span><?=icon('corner',14)?></a><a href="#projects" role="option" data-t="work projects"><?=icon('hash',16)?><span>Work</span><?=icon('corner',14)?></a><a href="#timeline" role="option" data-t="journey timeline"><?=icon('hash',16)?><span>Journey</span><?=icon('corner',14)?></a><a href="#certs" role="option" data-t="credentials certificates"><?=icon('hash',16)?><span>Credentials</span><?=icon('corner',14)?></a><a href="#guestbook" role="option" data-t="guestbook"><?=icon('hash',16)?><span>Guestbook</span><?=icon('corner',14)?></a><a href="#contact" role="option" data-t="contact"><?=icon('hash',16)?><span>Contact</span><?=icon('corner',14)?></a><p class="pal-group">Projects</p><?php foreach ($projects as $p): ?><a href="#projects" role="option" data-t="<?=e(strtolower(trim(($p['title'] ?? '').' '.($p['tags'] ?? ''))))?>"><?=icon('filetext',16)?><span><?=e($p['title'] ?? 'Project')?></span><em><?=e($p['year'] ?? '')?></em><?=icon('corner',14)?></a><?php endforeach; ?></div><div class="pal-foot"><span><kbd>↑↓</kbd> navigate</span><span><kbd>↵</kbd> select</span><span class="pal-tip">Tip: press / anywhere</span></div></div></div>
<div id="toast"></div>
<script>
const services=<?=json_encode(token('services', []))?>;
document.getElementById('mq').innerHTML=(services.map(s=>`<li><span class="t">${s}</span><span class="sep">✦</span></li>`).join('')).repeat(4);
</script>
<script src="assets/js/main.js?v=20261015y"></script>
</body></html>
