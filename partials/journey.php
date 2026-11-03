<section id="timeline" aria-label="Journey"><div class="wrap">
  <div class="journey-grid">
    <div class="journey-side">
      <div class="journey-head rv">
        <?=eyebrow(token('journey.kicker'))?>
        <h2 class="title"><?=split_title(token('journey.title'), 2)?></h2>
        <p class="journey-lead"><?=e(token('journey.lead'))?></p>
      </div>
      <div class="journey-now" aria-hidden="true">
        <p class="eyebrow"><?=e(token('journey.now', 'You are reading'))?></p>
        <div class="journey-now-stage">
          <span class="journey-now-year" id="journeyNow"><?=e($timeline[0]['year'] ?? '')?></span>
        </div>
        <div class="journey-dots" id="journeyDots"><?php foreach ($timeline as $i => $t): ?><span<?= $i === 0 ? ' class="on"' : '' ?>></span><?php endforeach; ?></div>
      </div>
    </div>
    <div class="journey-main">
      <div class="tl-wrap" id="tlWrap">
        <div class="tl-rail" aria-hidden="true"><span class="tl-fill" id="tlFill"></span><span class="tl-knob" id="tlKnob"></span></div>
        <ol class="tl" id="tlList"><?php foreach ($timeline as $i => $t): ?>
          <li class="tl-item rv" data-rvd="<?=e(number_format($i * 0.04, 2, '.', ''))?>">
            <span class="tl-year" data-entry="<?=$i?>"><span class="tl-dot" aria-hidden="true"></span><?=e($t['year'])?></span>
            <div class="tl-body">
              <h3><?=e($t['title'])?></h3>
              <p><?=e($t['desc'])?></p>
              <?php if (!empty($t['tags'])): ?><ul class="tags" aria-label="Focus"><?php foreach (explode(',', $t['tags']) as $tg): if (trim($tg)): ?><li class="tag"><?=e(trim($tg))?></li><?php endif; endforeach; ?></ul><?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?></ol>
      </div>
    </div>
  </div>
</div></section>
