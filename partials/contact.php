<section id="contact"><div class="wrap">
  <div class="kicker"><?=token('contact.kicker')?></div>
  <h2 class="title rv"><?=split_title(token('contact.title'), 2)?></h2>
  <p style="color:var(--mut);max-width:640px;margin-bottom:16px">Have a product in mind, or just want to compare notes? Send the details and I'll reply within one business day with a clear next step.</p>
  <?php if ($msg_ok): ?><p style="background:rgba(240,189,122,.12);border:1px solid var(--acc);padding:12px;border-radius:12px;margin-bottom:12px"><?=e($msg_ok)?></p><?php endif; ?>
  <div class="contact-grid rv" data-rvd="0.08">
    <div>
      <p style="color:var(--mut)">Available for new work<br><?=icon('mail',14)?> <?=e(token('email'))?><br><?=icon('phone',14)?> <?=e(token('phone'))?><br><?=icon('pin',14)?> <?=e(token('location'))?> · Remote friendly</p>
      <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap">
        <a class="btn ghost" <?=ext(token('social.github'))?>><?=icon('github',15)?> GitHub</a>
        <a class="btn ghost" <?=ext(token('social.linkedin'))?>><?=icon('linkedin',15)?> LinkedIn</a>
        <a class="btn ghost" <?=ext(token('social.facebook'))?>><?=icon('facebook',15)?> Facebook</a>
      </div>
    </div>
    <form method="POST">
      <div class="row2"><div><label>Name</label><input name="name" required></div><div><label>Email</label><input name="email" type="email" required></div></div>
      <label>Company (optional)</label><input name="company">
      <label>I need help with (pick any)</label>
      <div data-chips="need"><span class="chip">Web app</span><span class="chip">Mobile app</span><span class="chip">UI/UX design</span><span class="chip">Landing page</span><span class="chip">Something else</span></div>
      <input type="hidden" name="need" id="need">
      <label>Budget (optional)</label>
      <div data-chips="budget"><span class="chip">&lt; $1k</span><span class="chip">$1k – 5k</span><span class="chip">$5k – 10k</span><span class="chip">$10k+</span><span class="chip">Not sure yet</span></div>
      <input type="hidden" name="budget" id="budget">
      <label>What are you looking to build?</label><textarea name="message" rows="4" required></textarea>
      <button class="btn" name="contact" style="margin-top:14px">Send inquiry <?=icon('send',14)?></button>
      <p style="color:var(--mut);font-size:12px;margin-top:10px">This opens your mail app. Nothing is stored on this site.</p>
    </form>
  </div>
</div></section>
