<section id="projects"><div class="wrap">
  <div class="kicker"><?=token('work.kicker')?></div>
  <h2 class="title rv"><?=split_title(token('work.title'), 2)?></h2>
  <p style="color:var(--mut)"><?=e(token('work.lead'))?></p>
  <p style="color:var(--mut);font-size:13px;margin-top:4px"><?=count($projects)?> <?=e(token('work.count'))?></p>
  <?php foreach ($projects as $p):
    $ic = $p['icon'] ?? icon_for_emoji($p['emoji'] ?? null); ?>
  <div class="proj rv">
    <div class="preview"><?=icon($ic, 72)?><small><?=e(token('work.preview_note'))?></small></div>
    <div>
      <div class="meta"><?=e($p['num'] ?? '')?> · <?=e($p['role'] ?? '')?> · <?=e($p['year'] ?? '')?></div>
      <h3><?=e($p['title'])?></h3>
      <p><?=e($p['desc'])?></p>
      <div class="tags"><?php foreach (explode(',', $p['tags'] ?? '') as $tg): if (trim($tg)): ?><span class="tag"><?=e(trim($tg))?></span><?php endif; endforeach; ?></div>
      <div style="display:flex;gap:10px;margin-top:10px">
        <a class="btn ghost" <?=ext($p['case_url'] ?? '#')?>><?=e(token('work.case_btn'))?></a>
        <a class="btn" <?=ext($p['live_url'] ?? '#')?>><?=e(token('work.live_btn'))?> <?=icon('arrow',14)?></a>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div></section>
