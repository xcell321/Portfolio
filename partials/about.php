<section id="about"><div class="wrap">
  <div class="kicker"><?=token('about.kicker')?></div>
  <h2 class="title rv"><?=token('about.title')?></h2>
  <p class="rv" style="color:var(--mut);max-width:640px;margin:0 0 18px">I move between product strategy, interface design, and the engineering that powers production apps. You get fewer handoffs and a product that holds together.</p>
  <div style="display:flex;gap:8px;margin:0 0 22px;flex-wrap:wrap">
    <a class="btn ghost" <?=ext(token('social.github'))?>><?=icon('github',15)?> GitHub</a>
    <a class="btn ghost" <?=ext(token('social.linkedin'))?>><?=icon('linkedin',15)?> LinkedIn</a>
    <a class="btn ghost" <?=ext(token('social.facebook'))?>><?=icon('facebook',15)?> Facebook</a>
  </div>
  <div class="cards3">
    <div class="card rv"><div class="card-ic"><?=icon('code',26)?></div><h3><?=token('about.card1_title')?></h3><p><?=token('about.card1_desc')?></p></div>
    <div class="card rv"><div class="card-ic"><?=icon('mobile',26)?></div><h3><?=token('about.card2_title')?></h3><p><?=token('about.card2_desc')?></p></div>
    <div class="card rv"><div class="card-ic"><?=icon('palette',26)?></div><h3><?=token('about.card3_title')?></h3><p><?=token('about.card3_desc')?></p></div>
  </div>
  <h3 style="margin:26px 0 6px">Skills <small style="color:var(--mut)">— Tap a word to see where I've used it.</small></h3>
  <p style="color:var(--mut);font-size:12px;margin:0 0 8px">always a work in progress · updated Oct 2026</p>
  <?php foreach (token('skills', []) as $i => $sk):
    $parts = array_map('trim', explode('/', $sk[1]));
    $cnt = count(array_filter($parts)); ?>
  <div class="acc rv <?= $i === 0 ? 'open' : '' ?>">
    <button class="acc-head"><?=sprintf('%02d', $i + 1) . ' / ' . e($sk[0])?> <span style="color:var(--mut);font-weight:400;font-size:13px"><?=e($sk[1])?> <b class="sk-count"><?=$cnt?></b></span> <span><?=icon('plus',14)?></span></button>
    <div class="acc-body"><div><?=e($sk[1])?><br>
      <?php foreach (explode('/', $sk[1]) as $c): ?><span class="chip"><?=e(trim($c))?></span><?php endforeach; ?>
    </div></div>
  </div>
  <?php endforeach; ?>
  <h3 style="margin:26px 0 6px">Toolkit <small style="color:var(--mut)">— What I reach for daily. Go ahead, drag them.</small> <button class="btn ghost" style="padding:6px 14px" onclick="tidyUp()">Tidy up</button></h3>
  <div class="toolkit" id="toolkit">
    <?php
    $slugs = ['PHP'=>'php','MySQL'=>'mysql','JavaScript'=>'javascript','Tailwind'=>'tailwindcss','Laravel'=>'laravel','Flutter'=>'flutter','Firebase'=>'firebase','Git'=>'git','Figma'=>'figma','XAMPP'=>'apachenetbeans','CSS3'=>'css3','HTML5'=>'html5'];
    foreach (token('toolkit', []) as $t):
      $slug = $slugs[$t] ?? '';
    ?><div class="tool"><?php if ($slug): ?><img src="https://cdn.simpleicons.org/<?=$slug?>/a8a29e" alt="" width="16" height="16" loading="lazy" onerror="this.remove()"> <?php endif; ?><?=e($t)?></div><?php endforeach; ?>
  </div>
</div></section>
