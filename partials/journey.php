<section id="timeline"><div class="wrap">
  <div class="journey-grid">
    <div class="journey-side">
      <?=eyebrow(token('journey.kicker'), 'rv')?>
      <h2 class="title rv"><?=split_title(token('journey.title'), 2)?></h2>
      <p class="journey-lead rv" data-rvd="0.06"><?=e(token('journey.lead'))?></p>
      <div class="journey-now rv" data-rvd="0.12" aria-hidden="true">
        <div class="journey-now-label"><?=e(token('journey.now', 'You are reading'))?></div>
        <div class="journey-now-year" id="journeyNow"><?=e($timeline[0]['year'] ?? '')?></div>
        <div class="journey-dots" id="journeyDots"><?php foreach ($timeline as $i => $t): ?><span<?= $i === 0 ? ' class="on"' : '' ?>></span><?php endforeach; ?></div>
      </div>
    </div>
    <div class="journey-main">
      <div class="tl-wrap" id="tlWrap">
        <div class="tl-rail" aria-hidden="true"><span class="tl-fill" id="tlFill"></span><span class="tl-knob" id="tlKnob"></span></div>
        <div class="tl" id="tlList"><?php foreach ($timeline as $i => $t): ?>
          <div class="tl-item rv" data-tl-i="<?=$i?>" data-year="<?=e($t['year'])?>" data-rvd="<?=e(number_format($i * 0.04, 2, '.', ''))?>"><span><?=e($t['year'])?></span><h4><?=e($t['title'])?></h4><p style="color:var(--mut);font-size:14px"><?=e($t['desc'])?></p><?php if (!empty($t['tags'])): ?><div class="tags"><?php foreach (explode(',', $t['tags']) as $tg): if (trim($tg)): ?><span class="tag"><?=e(trim($tg))?></span><?php endif; endforeach; ?></div><?php endif; ?></div>
        <?php endforeach; ?></div>
      </div>
    </div>
  </div>
</div></section>
