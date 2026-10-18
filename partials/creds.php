<section id="certs"><div class="wrap">
  <div class="kicker"><?=token('certs.kicker')?></div>
  <h2 class="title rv"><?=split_title(token('certs.title'), 2)?></h2>
  <p style="color:var(--mut);max-width:640px;margin-bottom:14px"><?=e(token('certs.lead'))?></p>
  <div class="certs"><?php foreach ($certs as $i => $c):
    $ic = $c['icon'] ?? icon_for_emoji($c['emoji'] ?? null); ?>
    <div class="cert rv" data-rvd="<?= e(number_format(($i % 3) * 0.06, 2, '.', '')) ?>"><div class="e"><?=icon($ic, 40)?></div><b><?=e($c['title'])?></b><p style="color:var(--mut);font-size:13px"><?=e($c['org'] ?? '')?> · <?=e($c['date'] ?? '')?></p><a <?=ext($c['verify_url'] ?? '#')?> style="color:var(--acc);font-size:13px"><?=e(token('certs.verify_btn'))?> <?=icon('arrow',13)?></a></div>
  <?php endforeach; ?></div>
</div></section>
