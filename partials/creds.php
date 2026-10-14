<section id="certs"><div class="wrap">
  <div class="kicker"><?=token('certs.kicker')?></div>
  <h2 class="title rv"><?=token('certs.title')?></h2>
  <p style="color:var(--mut);max-width:640px;margin-bottom:14px">Certifications across security, AI, and data that keep my fundamentals sharp. Each one links to its Credly record.</p>
  <div class="certs"><?php foreach ($certs as $c):
    $ic = $c['icon'] ?? icon_for_emoji($c['emoji'] ?? null); ?>
    <div class="cert rv"><div class="e"><?=icon($ic, 40)?></div><b><?=e($c['title'])?></b><p style="color:var(--mut);font-size:13px"><?=e($c['org'] ?? '')?> · <?=e($c['date'] ?? '')?></p><a <?=ext($c['verify_url'] ?? '#')?> style="color:var(--acc);font-size:13px">Verify on Credly <?=icon('arrow',13)?></a></div>
  <?php endforeach; ?></div>
</div></section>
