<section id="guestbook"><div class="wrap">
  <?=eyebrow(token("guest.kicker"))?>
  <h2 class="title rv"><?=split_title(token('guest.title'), 1)?></h2>
  <form method="POST" style="max-width:560px">
    <?=csrf_field()?>
    <div class="row2"><div><label><?=e(token('guest.form_name'))?></label><input name="gname" required></div><div><label><?=e(token('guest.form_msg'))?></label><input name="gmsg" required></div></div>
    <button class="btn" name="gbook" style="margin-top:12px"><?=e(token('guest.form_btn'))?></button>
  </form>
  <?php foreach ($guest_rows as $gi => $g): ?>
    <div class="guest rv-pop" data-rvd="<?= e(number_format($gi * 0.02, 2, '.', '')) ?>" tabindex="0"><b><?=e($g['name'])?></b> <small style="color:var(--mut)"><?=e($g['created_at'])?></small><br><?=e($g['message'])?></div>
  <?php endforeach; ?>
</div></section>
