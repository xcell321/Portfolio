<section id="projects"><div class="wrap">
  <div class="kicker"><?=token('work.kicker')?></div>
  <h2 class="title rv"><?=token('work.title')?></h2>
  <p style="color:var(--mut)">A handful of products taken from a blank file to production, end to end.</p>
  <p style="color:var(--mut);font-size:13px;margin-top:4px"><?=count($projects)?> projects</p>
  <?php foreach ($projects as $p):
    $ic = $p['icon'] ?? icon_for_emoji($p['emoji'] ?? null); ?>
  <div class="proj rv">
    <div class="preview"><?=icon($ic, 72)?><small>hover tilt · click effects</small></div>
    <div>
      <div class="meta"><?=e($p['num'] ?? '')?> · <?=e($p['role'] ?? '')?> · <?=e($p['year'] ?? '')?></div>
      <h3><?=e($p['title'])?></h3>
      <p><?=e($p['desc'])?></p>
      <div class="tags"><?php foreach (explode(',', $p['tags'] ?? '') as $tg): if (trim($tg)): ?><span class="tag"><?=e(trim($tg))?></span><?php endif; endforeach; ?></div>
      <div style="display:flex;gap:10px;margin-top:10px">
        <a class="btn ghost" <?=ext($p['case_url'] ?? '#')?>>Read case study</a>
        <a class="btn" <?=ext($p['live_url'] ?? '#')?>>Visit live <?=icon('arrow',14)?></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div></section>
