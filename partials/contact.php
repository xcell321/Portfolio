<section id="contact" aria-label="Contact"><div class="wrap">
  <div class="contact-cols">
    <div class="contact-left rv">
      <?=eyebrow(token("contact.kicker"))?>
      <h2 class="title"><?=split_title(token('contact.title'), 1)?></h2>
      <p class="contact-lead"><?=e(token('contact.lead'))?></p>
      <?php if ($msg_ok): ?><p class="contact-flash" role="status"><?=e($msg_ok)?></p><?php endif; ?>
      <p class="contact-avail">
        <span class="contact-live" aria-hidden="true"></span>
        <span><?=e(token('contact.avail'))?></span>
        <span class="contact-avail-sep" aria-hidden="true">&middot;</span>
        <span class="contact-now"><span id="contactClock">--:--</span><span><?=e(token('contact.now'))?><?=icon('rocket',13)?></span></span>
      </p>
      <ul class="contact-list">
        <li class="contact-row">
          <a href="mailto:<?=e(token('email'))?>">
            <?=icon('mail',16)?>
            <span class="contact-row-copy">
              <span class="contact-row-label">Email</span>
              <span class="contact-row-val"><?=e(token('email'))?></span>
            </span>
          </a>
          <button type="button" class="contact-copy" aria-label="Copy email" data-copy="<?=e(token('email'))?>">
            <span class="contact-copy-ico"><?=icon('copy',14)?></span>
            <span class="contact-copy-check"><?=icon('check',14)?></span>
          </button>
        </li>
        <li class="contact-row">
          <a href="tel:<?=e(preg_replace('/[^\d+]/', '', token('phone')))?>">
            <?=icon('phone',16)?>
            <span class="contact-row-copy">
              <span class="contact-row-label">Phone</span>
              <span class="contact-row-val"><?=e(token('phone'))?></span>
            </span>
          </a>
          <button type="button" class="contact-copy" aria-label="Copy phone" data-copy="<?=e(token('phone'))?>">
            <span class="contact-copy-ico"><?=icon('copy',14)?></span>
            <span class="contact-copy-check"><?=icon('check',14)?></span>
          </button>
        </li>
        <li class="contact-row">
          <div class="contact-row-static">
            <?=icon('pin',16)?>
            <span class="contact-row-copy">
              <span class="contact-row-label">Based in</span>
              <span class="contact-row-val"><?=e(token('location'))?> &middot; <?=e(token('contact.remote'))?></span>
            </span>
          </div>
        </li>
      </ul>
    </div>
    <div class="contact-card rv" data-rvd="0.08">
      <form id="contactForm" class="contact-form" method="POST" data-mailto="<?=e(token('email'))?>">
        <?=csrf_field()?>
        <p class="contact-form-intro" aria-live="polite"><?=e(token('contact.form_intro'))?></p>
        <div class="row2">
          <div class="contact-field">
            <label for="name"><?=e(token('contact.f_name'))?></label>
            <input id="name" name="name" type="text" autocomplete="name" placeholder="Alex Rivera" required>
          </div>
          <div class="contact-field">
            <label for="email"><?=e(token('contact.f_email'))?></label>
            <input id="email" name="email" type="email" autocomplete="email" placeholder="alex@studio.co" required>
          </div>
        </div>
        <div class="contact-field">
          <label for="company"><?=e(token('contact.f_company'))?><span class="contact-opt">(optional)</span></label>
          <input id="company" name="company" type="text" autocomplete="organization" placeholder="Studio North">
        </div>
        <fieldset class="contact-fieldset">
          <legend><?=e(token('contact.f_need'))?><span class="contact-opt">(pick any)</span></legend>
          <div class="contact-chips" data-chips="need">
            <?php foreach ((array)token('contact.need_chips', []) as $chip): ?>
              <button type="button" class="chip" aria-pressed="false" data-value="<?=e($chip)?>"><?=icon('check',14)?><span><?=e($chip)?></span></button>
            <?php endforeach; ?>
          </div>
        </fieldset>
        <input type="hidden" name="need" id="need">
        <div class="contact-field">
          <p id="budget-label" class="contact-label"><?=e(token('contact.f_budget'))?><span class="contact-opt">(optional)</span></p>
          <div class="contact-chips" data-chips="budget" data-single role="radiogroup" aria-labelledby="budget-label">
            <?php foreach ((array)token('contact.budget_chips', []) as $chip): ?>
              <button type="button" class="chip" role="radio" aria-checked="false" data-value="<?=e($chip)?>"><?=icon('check',14)?><span><?=e($chip)?></span></button>
            <?php endforeach; ?>
          </div>
        </div>
        <input type="hidden" name="budget" id="budget">
        <div class="contact-field contact-message">
          <label for="message"><?=e(token('contact.f_msg'))?></label>
          <textarea id="message" name="message" rows="4" placeholder="Goals, timeline, anything useful." required></textarea>
        </div>
        <button class="btn contact-submit" type="submit" name="contact"><?=e(token('contact.f_send'))?> <?=icon('arrow_right',16)?></button>
        <p class="contact-note"><?=e(token('contact.f_note'))?></p>
      </form>
    </div>
  </div>
</div></section>
