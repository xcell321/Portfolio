<section id="guestbook"><div class="wrap">
  <div class="kicker"><?=token('guest.kicker')?></div>
  <h2 class="title rv"><?=split_title(token('guest.title'), 1)?></h2>
  <form method="POST" style="max-width:560px">
    <div class="row2"><div><label>Name</label><input name="gname" required></div><div><label>Message</label><input name="gmsg" required></div></div>
    <button class="btn" name="gbook" style="margin-top:12px">Sign guestbook</button>
  </form>
  <?php foreach ($guest_rows as $g): ?>
    <div class="guest"><b><?=e($g['name'])?></b> <small style="color:var(--mut)"><?=e($g['created_at'])?></small><br><?=e($g['message'])?></div>
  <?php endforeach; ?>
</div></section>
