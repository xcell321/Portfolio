<?php
$label = token('nav.certs');
?>
<section id="certs"><div class="wrap">
  <?=eyebrow(token("certs.kicker"))?>
  <h2 class="title rv"><?=split_title(token('certs.title'), 2)?></h2>
  <p style="color:var(--mut);max-width:640px;margin-bottom:40px"><?=e(token('certs.lead'))?></p>
  <div class="certs"><?php foreach ($certs as $i => $c):
    $title = (string)($c['title'] ?? '');
    $org   = (string)($c['org'] ?? '');
    $date  = (string)($c['date'] ?? '');
    $url   = trim((string)($c['verify_url'] ?? '#'));
    $img   = $c['image'] ?? null;
    if ($url === '') $url = '#';
  ?>
    <article class="cert rv" data-rvd="<?= e(number_format(($i % 3) * 0.06, 2, '.', '')) ?>">
      <div class="cert-thumb">
        <?php if ($img): ?><img src="<?=e($img)?>" alt="<?=e($title)?>" loading="lazy"><?php else: ?>
        <div style="height:100%;display:flex;align-items:center;justify-content:center;background:#1c1c1c;color:var(--mut)"><?=icon('award', 40)?></div><?php endif; ?>
      </div>
      <div class="cert-body">
        <div class="cert-org"><?=e($org)?> · <?=e($date)?></div>
        <h3><?=e($title)?></h3>
        <a class="cert-link" <?=ext($url)?>><?=e(token('certs.verify_btn'))?> <?=icon('arrow', 13)?></a>
      </div>
    </article>
  <?php endforeach; ?></div>
</div>
</section>
