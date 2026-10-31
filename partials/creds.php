<?php
$label = token('nav.certs');
?>
<section id="certs"><div class="wrap">
  <div class="kicker"><?=token('certs.kicker')?></div>
  <h2 class="title rv"><?=split_title(token('certs.title'), 2)?></h2>
  <p style="color:var(--mut);max-width:640px;margin-bottom:14px"><?=e(token('certs.lead'))?></p>
  <div class="certs"><?php foreach ($certs as $i => $c):
    $ic    = $c['icon'] ?? icon_for_emoji($c['emoji'] ?? null);
    $title = (string)($c['title'] ?? '');
    $org   = (string)($c['org'] ?? '');
    $date  = (string)($c['date'] ?? '');
    $url   = trim((string)($c['verify_url'] ?? '#'));
    if ($url === '') $url = '#';
  ?>
    <article class="cert rv" data-rvd="<?= e(number_format(($i % 3) * 0.06, 2, '.', '')) ?>">
      <button class="cert-thumb" type="button" data-cert-open data-cert-title="<?=e($title)?>" data-cert-org="<?=e($org)?>" data-cert-date="<?=e($date)?>" data-cert-url="<?=e($url)?>" aria-label="View <?=e($title)?> certificate" aria-haspopup="dialog" aria-controls="certModal">
        <span class="cert-paper" aria-hidden="true">
          <span class="cert-paper-top"><span class="cert-paper-brand"><?=e($label)?></span><span class="cert-paper-dot"></span></span>
          <span class="cert-paper-icon"><?=icon($ic, 30)?></span>
          <span class="cert-paper-title"><?=e($title)?></span>
          <span class="cert-paper-foot"><span><?=e($org)?></span><span><?=e($date)?></span></span>
        </span>
        <span class="cert-zoom" aria-hidden="true"><?=icon('plus', 14)?></span>
      </button>
      <div class="cert-body">
        <div class="cert-org"><span><?=e($org)?></span><span><?=e($date)?></span></div>
        <h3><?=e($title)?></h3>
        <a class="cert-link" <?=ext($url)?>><?=e(token('certs.verify_btn'))?> <?=icon('arrow', 13)?></a>
      </div>
    </article>
  <?php endforeach; ?></div>
</div>
<div class="cert-modal" id="certModal" aria-hidden="true">
  <div class="cert-modal-ov" data-cert-close></div>
  <div class="cert-modal-box" role="dialog" aria-modal="true" aria-labelledby="certModalTitle" tabindex="-1">
    <button class="cert-modal-x" type="button" data-cert-close aria-label="Close certificate preview"><?=icon('plus', 16)?></button>
    <div class="cert-modal-preview" id="certModalPreview" aria-hidden="true"></div>
    <div class="cert-modal-copy">
      <div class="cert-modal-meta"><span><?=e($label)?></span><span id="certModalDate"></span></div>
      <h3 id="certModalTitle"></h3>
      <p id="certModalOrg"></p>
      <a class="btn" id="certModalLink" <?=ext('#')?>><?=e(token('certs.verify_btn'))?> <?=icon('arrow', 14)?></a>
    </div>
  </div>
</div>
</section>
