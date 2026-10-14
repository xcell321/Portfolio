<section id="timeline"><div class="wrap">
  <div class="kicker"><?=token('journey.kicker')?></div>
  <h2 class="title rv"><?=token('journey.title')?></h2>
  <p style="color:var(--mut);max-width:640px">From first HTML page to shipping full stack products, a short history of how I got here.</p>
  <div class="tl"><?php foreach ($timeline as $t): ?>
    <div class="tl-item rv"><span><?=e($t['year'])?></span><h4><?=e($t['title'])?></h4><p style="color:var(--mut);font-size:14px"><?=e($t['desc'])?></p><?php if (!empty($t['tags'])): ?><div class="tags"><?php foreach (explode(',', $t['tags']) as $tg): if (trim($tg)): ?><span class="tag"><?=e(trim($tg))?></span><?php endif; endforeach; ?></div><?php endif; ?></div>
  <?php endforeach; ?></div>
</div></section>
