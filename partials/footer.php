<footer><div class="wrap">
  <div class="foot"><span>Navigate: About · Work · Journey · Contact</span><span>© 2026 <?=e(token('owner_name'))?>. All rights reserved.</span></div>
  <div class="big tickle" id="bigName" aria-label="<?=e(strtoupper(token('owner_name')))?>"><span>E</span><span>D</span><span>S</span><span>I</span><span>E</span><span>L</span><span>&nbsp;</span><span>U</span><span>R</span><span>S</span><span>A</span><span>L</span></div>
  <p style="color:var(--mut);font-size:12px;margin-top:6px">psst, the letters are ticklish.</p></div></footer>

<a class="chat-fab" href="#contact" title="Chat" aria-label="Chat"><?=icon('chat',20)?></a>
<button class="btn" id="toTop" onclick="toTop()">↑ Top</button>
<div id="popToast" class="pop-toast"></div>
<div id="pal"><div class="pal-box"><input id="palInput" placeholder="Type a command… (Esc to close)" onkeydown="if(event.key==='Escape')document.getElementById('pal').classList.remove('open')" oninput="palFilter(this.value)"><div id="palList"><a href="#about" data-t="about">→ Go to About</a><a href="#projects" data-t="work projects">→ Go to Work</a><a href="#timeline" data-t="journey timeline">→ Go to Journey</a><a href="#certs" data-t="credentials certificates">→ Go to Credentials</a><a href="#guestbook" data-t="guestbook">→ Go to Guestbook</a><a href="#contact" data-t="contact">→ Go to Contact</a></div></div></div>
<div id="toast"></div>
<script>
const services=<?=json_encode(token('services', []))?>;
document.getElementById('mq').innerHTML=(services.map(s=>`<li><span class="t">${s}</span><span class="sep">✦</span></li>`).join('')).repeat(4);
</script>
<script src="assets/js/main.js?v=20261015g"></script>
</body></html>
