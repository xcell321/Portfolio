<section id="contact"><div class="wrap">
  <div class="kicker"><?=token('contact.kicker')?></div>
  <h2 class="title rv"><?=split_title(token('contact.title'), 2)?></h2>
  <p style="color:var(--mut);max-width:640px;margin-bottom:16px"><?=e(token('contact.lead'))?></p>
  <?php if ($msg_ok): ?><p style="background:rgba(240,189,122,.12);border:1px solid var(--acc);padding:12px;border-radius:12px;margin-bottom:12px"><?=e($msg_ok)?></p><?php endif; ?>
  <div class="contact-grid rv" data-rvd="0.08">
    <div>
      <p style="color:var(--mut)"><?=e(token('contact.avail'))?><br><?=icon('mail',14)?> <?=e(token('email'))?><br><?=icon('phone',14)?> <?=e(token('phone'))?><br><?=icon('pin',14)?> <?=e(token('location'))?> · <?=e(token('contact.remote'))?></p>
      <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
        <a class="btn ghost" <?=ext(token('social.github'))?>><?=icon('github',15)?> GitHub</a>
        <a class="btn ghost" <?=ext(token('social.linkedin'))?>><?=icon('linkedin',15)?> LinkedIn</a>
        <a class="btn ghost" <?=ext(token('social.facebook'))?>><?=icon('facebook',15)?> Facebook</a>
      </div>
    </div>
    <form method="POST">
      <div class="row2"><div><label><?=e(token('contact.f_name'))?></label><input name="name" required></div><div><label><?=e(token('contact.f_email'))?></label><input name="email" type="email" required></div></div>
      <label><?=e(token('contact.f_company'))?></label><input name="company">
      <label><?=e(token('contact.f_need'))?></label>
      <div data-chips="need"><?php foreach ((array)token('contact.need_chips', []) as $chip): ?><span class="chip"><?=e($chip)?></span><?php endforeach; ?></div>
      <input type="hidden" name="need" id="need">
      <label><?=e(token('contact.f_budget'))?></label>
      <div data-chips="budget"><?php foreach ((array)token('contact.budget_chips', []) as $chip): ?><span class="chip"><?=e($chip)?></span><?php endforeach; ?></div>
      <input type="hidden" name="budget" id="budget">
      <label><?=e(token('contact.f_msg'))?></label><textarea name="message" rows="4" required></textarea>
      <button class="btn" name="contact" style="margin-top:14px"><?=e(token('contact.f_send'))?> <?=icon('send',14)?></button>
      <p style="color:var(--mut);font-size:12px;margin-top:10px"><?=e(token('contact.f_note'))?></p>
    </form>
  </div>
</div></section>
