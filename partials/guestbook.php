<?php
// Guestbook — paper note form + visitor wall, ported from the mikedev reference.
$papers     = (array)token('guest.papers');
$paperNames = (array)token('guest.paper_names');
$firstKey   = (string)array_key_first($papers);
$page       = max(1, (int)token('guest.page', 12));
$maxNote    = (int)token('guest.max_note', 160);
$maxName    = (int)token('guest.max_name', 24);
$likeOn     = (string)token('like.on', 'You liked this');
$likeOff    = (string)token('like.off', 'Like this portfolio');
$noteRot    = static function ($id): string {
    $h = 0;
    foreach (str_split((string)$id) as $ch) $h = (31 * $h + ord($ch)) | 0;
    return number_format((abs($h) % 7 - 3) * 0.9, 2, '.', '') . 'deg';
};
?>
<section id="guestbook" aria-label="Guestbook"><div class="wrap">
  <div class="gb-head rv">
    <?=eyebrow(token('guest.kicker'))?>
    <div class="gb-head-row">
      <div class="gb-title-block">
        <h2 class="title"><?=split_title(token('guest.title'), 3)?></h2>
        <p class="gb-lead"><?=e(token('guest.lead'))?></p>
      </div>
      <div class="gb-aside">
        <button class="pill like gb-like<?=!empty($liked_now)?' liked':''?>" id="likePillGb" onclick="like(event)" data-sound="none" data-on="<?=e($likeOn)?>" data-off="<?=e($likeOff)?>" title="Like" aria-label="<?=!empty($liked_now)?'Unlike':e($likeOff)?>" aria-pressed="<?=!empty($liked_now)?'true':'false'?>"><?=icon('heart',16)?> <span id="likeLabelGb"><?=!empty($liked_now)?e($likeOn):e($likeOff)?></span> <span class="like-num" id="likeNumWrapGb"><span id="likeNumGb"><?=$like_total?></span></span></button>
      </div>
    </div>
  </div>

  <div class="gb-grid">
    <form method="POST" class="gb-form" aria-label="Leave a note on the wall" style="--paper:<?=e($papers[$firstKey])?>">
      <?=csrf_field()?>
      <input type="hidden" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" class="gb-honey">
      <span class="gb-pin-ic" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17v5"/><path d="M9 10.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24V16a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V7a1 1 0 0 1 1-1 2 2 0 0 0 0-4H8a2 2 0 0 0 0 4 1 1 0 0 1 1 1z"/></svg></span>

      <label for="gb-note" class="gb-note-label"><?=e(token('guest.form_head'))?></label>
      <textarea id="gb-note" name="gmsg" rows="4" maxlength="<?=$maxNote?>" required placeholder="<?=e(token('guest.form_ph'))?>"></textarea>
      <div class="gb-count">0/<?=$maxNote?></div>

      <div class="gb-row">
        <input id="gb-name" class="gb-name" type="text" name="gname" maxlength="<?=$maxName?>" autocomplete="nickname" placeholder="<?=e(token('guest.form_name'))?>">
        <div class="note-papers" role="radiogroup" aria-label="<?=e(token('guest.form_color'))?>">
          <?php $first = true; foreach ($papers as $key => $hex): ?>
            <button type="button" class="gb-chip" role="radio" aria-checked="<?=$first?'true':'false'?>" aria-label="<?=e($paperNames[$key] ?? $key)?>" data-color="<?=e($key)?>" data-bg="<?=e($hex)?>" style="--chip:<?=e($hex)?>"></button>
          <?php $first = false; endforeach; ?>
        </div>
      </div>

      <input type="hidden" name="gcolor" value="<?=e($firstKey)?>">
      <button type="submit" class="gb-pin" name="gbook" data-busy="<?=e(token('guest.form_pinning'))?>"><?=e(token('guest.form_btn'))?></button>
      <p class="gb-hint"><?=e(token('guest.hint'))?></p>
    </form>

    <div class="gb-wall-wrap">
      <ul class="gb-wall" data-page="<?=$page?>" aria-label="Notes from visitors">
        <?php foreach ($guest_rows as $gi => $g): ?>
          <?php
            $key = (string)($g['color'] ?? $firstKey);
            if (!isset($papers[$key])) $key = $firstKey;
            $hidden = $gi >= $page;
            $dt = strtotime((string)$g['created_at']);
            $noteName = trim((string)$g['name']);
            if ($noteName === '') $noteName = (string)token('guest.anon', 'Anonymous');
          ?>
          <li class="guest rv-pop gb-note" data-paper="<?=e($key)?>" data-rvd="<?=e(number_format($gi * 0.02, 2, '.', ''))?>" style="--note-bg:<?=e($papers[$key])?>;--note-rot:<?=e($noteRot($g['id']))?>;background:<?=e($papers[$key])?>"<?=$hidden?' hidden':''?> tabindex="0">
            <span class="gb-tape" aria-hidden="true"></span>
            <p class="gb-note-text"><?=nl2br(e($g['message']))?></p>
            <div class="gb-note-meta">
              <span class="gb-note-name">— <?=e($noteName)?></span>
              <time datetime="<?=e($dt === false ? '' : date('c', $dt))?>"><?=e(time_ago((string)$g['created_at']))?></time>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>

      <?php if (!$guest_rows): ?>
        <div class="gb-empty">
          <p class="gb-empty-script"><?=e(token('guest.empty'))?></p>
          <p class="gb-empty-sub"><?=e(token('guest.empty_sub'))?></p>
        </div>
      <?php elseif (count($guest_rows) > $page): ?>
        <div class="gb-more-wrap">
          <button type="button" class="gb-more"><?=e(token('guest.more'))?> (<?=count($guest_rows) - $page?>)</button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div></section>
