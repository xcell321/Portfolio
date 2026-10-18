<footer><div class="wrap">
  <div class="foot"><span><?=e(token('foot.nav_label'))?> <?=e(token('nav.about'))?> · <?=e(token('nav.work'))?> · <?=e(token('nav.journey'))?> · <?=e(token('nav.contact'))?></span><span>© <?=date('Y')?> <?=e(token('owner_name'))?>. <?=e(token('foot.rights'))?></span></div>
  <?php
  $big_name = '';
  foreach (preg_split('//u', strtoupper(token('owner_name')), -1, PREG_SPLIT_NO_EMPTY) as $ch) {
    $big_name .= '<span>' . ($ch === ' ' ? '&nbsp;' : e($ch)) . '</span>';
  }
  ?>
  <div class="big tickle" id="bigName" aria-label="<?=e(strtoupper(token('owner_name')))?>"><?=$big_name?></div>
  <p style="color:var(--mut);font-size:12px;margin-top:6px"><?=e(token('foot.tickle'))?></p></div></footer>

<div id="handPanel" class="hand-panel">
  <div class="hand-vid"><video id="handVideo" playsinline muted></video><canvas id="handOverlay"></canvas><div id="handLoading" class="hand-loading"><span class="hand-load-box"><span class="hand-spin"></span><span class="hand-msg" id="handLoadMsg">Loading hand model…</span></span></div><div class="hand-bar"><div class="hand-left"><span class="hand-dot"></span>Hand Tracking</div><button class="hand-close" onclick="stopHandTracking()" aria-label="Disable hand tracking"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg></button></div></div>
</div>
<div id="handCursor" aria-hidden="true"></div>
<?php
// AI chat icons: MessageCircleQuestion (inline peek input), MessageCircle (fab open + avatar), Send, X.
$aiIcons = [
  'open'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>',
  'circle'=> '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',
  'send'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>',
  'close' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
];
?>
<div class="ai-widget" id="aiWidget" data-owner="<?=e(token('first_name'))?>">
  <div class="ai-fab" id="aiFab">
    <div class="ai-inline" id="aiInline" aria-hidden="true"><span class="ai-inline-ic" id="aiInlineIc"><?=$aiIcons['open']?></span><input type="text" id="aiInlineInput" placeholder="Ask me anything..." autocomplete="off"></div>
    <button class="ai-fab-btn" id="aiFabBtn" aria-label="Toggle AI chat"><span class="ai-fab-ic" id="aiFabIc"><?=$aiIcons['circle']?></span></button>
  </div>
  <div class="ai-ov" id="aiOv"></div>
  <div class="ai-panel" id="aiPanel" role="dialog" aria-modal="true" aria-label="AI chat">
    <div class="ai-head">
      <div class="ai-head-info"><div class="ai-avatar" id="aiAvatar"><?=$aiIcons['circle']?></div><div><h3><?=e(token('first_name'))?>'s AI Assistant</h3><p>Ask anything...</p></div></div>
      <button class="ai-close" id="aiClose" aria-label="Close chat"><?=$aiIcons['close']?></button>
    </div>
    <div class="ai-msgs" id="aiMsgs"></div>
    <div class="ai-foot">
      <div class="ai-input-row"><textarea id="aiInput" rows="1" placeholder="Ask me anything about <?=e(token('first_name'))?>..." autocomplete="off"></textarea><button class="ai-send" id="aiSend" aria-label="Send message" disabled><?=$aiIcons['send']?></button></div>
      <p class="ai-note">AI responses may not always be accurate</p>
    </div>
  </div>
</div>
<button class="btn" id="toTop" onclick="toTop()">↑ Top</button>
<div id="popToast" class="pop-toast"></div>
<div id="pal"><div class="pal-ov" onclick="palClose()"></div><div class="pal-box" role="dialog" aria-modal="true" aria-label="Command menu"><div class="pal-search"><?=icon('search',16)?><input id="palInput" placeholder="Search sections, projects, actions…" role="combobox" aria-expanded="true" aria-controls="palList" autocomplete="off" oninput="palFilter(this.value)" onkeydown="palKey(event)"><kbd>ESC</kbd></div><div id="palList" role="listbox" data-lenis-prevent></div><div class="pal-foot"><span><kbd>↑↓</kbd> navigate</span><span><kbd>↵</kbd> select</span><span class="pal-tip">Tip: press / anywhere</span></div></div></div>
<div id="palToast" role="status"></div>
<div id="toast"></div>
<script>
const services=<?=json_encode(token('services', []))?>;
document.getElementById('mq').innerHTML=(services.map(s=>`<li><span class="t">${s}</span><span class="sep">✦</span></li>`).join('')).repeat(4);
</script>
<?php
$palNav = [
  ['id'=>'about','label'=>token('nav.about'),'keys'=>'about'],
  ['id'=>'projects','label'=>token('nav.work'),'keys'=>'work projects'],
  ['id'=>'timeline','label'=>token('nav.journey'),'keys'=>'journey timeline'],
  ['id'=>'certs','label'=>token('nav.certs'),'keys'=>'credentials certificates'],
  ['id'=>'guestbook','label'=>token('nav.guestbook'),'keys'=>'guestbook'],
  ['id'=>'contact','label'=>token('nav.contact'),'keys'=>'contact'],
];
$palProjects = [];
foreach ($projects as $p) {
  $palProjects[] = ['title'=>(string)($p['title'] ?? 'Project'),'year'=>(string)($p['year'] ?? ''),'tags'=>(string)($p['tags'] ?? '')];
}
$palSocials = [
  ['name'=>'GitHub','href'=>(string)token('social.github'),'icon'=>'github'],
  ['name'=>'LinkedIn','href'=>(string)token('social.linkedin'),'icon'=>'linkedin'],
  ['name'=>'Facebook','href'=>(string)token('social.facebook'),'icon'=>'facebook'],
];
$palData = [
  'email'    => (string)token('email'),
  'nav'      => $palNav,
  'projects' => $palProjects,
  'socials'  => $palSocials,
  'secrets'  => ['sticker','konami','name','sudo','wordmark'],
];
$palIcons = [];
foreach (['hash','filetext','copy','send','volume','spark','award','palette','github','linkedin','facebook'] as $n) { $palIcons[$n] = icon($n,16); }
$palIcons['corner'] = icon('corner',14);
?>
<script>
window.PAL=<?=json_encode($palData, JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
window.PAL_ICONS=<?=json_encode($palIcons, JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
window.AI_ICONS={open:<?=json_encode($aiIcons['circle'])?>,send:<?=json_encode($aiIcons['send'])?>,close:<?=json_encode($aiIcons['close'])?>};
</script>
<script src="assets/js/main.js?v=20261018l"></script>
<script>
// stub so the nav hand button doesn't ReferenceError while the ES module loads
window.toggleHandTracking = window.toggleHandTracking || function () {
  var t = document.getElementById('toast');
  if (t) { t.textContent = 'Loading hand model… try again in 2s.'; t.classList.add('show'); clearTimeout(t._h); t._h = setTimeout(function(){t.classList.remove('show')}, 2000); }
};
window.stopHandTracking = window.stopHandTracking || function () {
  document.getElementById('handPanel')?.classList.remove('show');
};
</script>
<script type="module" src="assets/js/hand-tracking.js?v=20261018h"></script>
</body></html>
