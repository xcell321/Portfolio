<?php
// Emoji reaction bar + presence badge.
// Markup mirrors the reference build's reaction bar; the floats layer, panel,
// per-button stagger and toggle chevron are driven by main.js.
$reactSet  = reaction_set();
$reactBtn  = reaction_toggle_emoji();
$emojiDir  = 'assets/img/emoji/';
$present   = (int)($present_now ?? 1);
?>
<div class="react-bar" id="reactBar" aria-label="Reaction bar">
  <div class="react-floats" id="reactFloats" aria-hidden="true"></div>
  <div class="react-stack">
    <div class="react-panel" id="reactPanel" role="group" aria-label="Send a reaction">
      <?php foreach ($reactSet as $i => $r): ?>
      <button type="button" class="react-btn" style="--i:<?=$i?>" data-emoji="<?=e($r['emoji'])?>" aria-label="React with <?=e($r['label'])?>">
        <span class="react-btn-box"><img src="<?=$emojiDir . e($r['file'])?>.svg" alt="<?=e($r['emoji'])?>" width="28" height="28" draggable="false"></span>
        <span class="react-btn-label"><?=e($r['label'])?></span>
      </button>
      <?php endforeach; ?>
    </div>
    <button type="button" class="react-toggle" id="reactToggle" aria-expanded="false" aria-controls="reactPanel" aria-label="Send a reaction">
      <img src="<?=$emojiDir . e($reactBtn['file'])?>.svg" alt="<?=e($reactBtn['emoji'])?>" width="16" height="16" draggable="false">
      <span class="react-toggle-txt" id="reactToggleTxt"><?=e($reactBtn['label'])?></span>
      <span class="react-chev" id="reactChev" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 15-6-6-6 6"/></svg></span>
    </button>
  </div>
</div>
<div class="presence-badge" id="presenceBadge" role="status">
  <span class="presence-dot" aria-hidden="true"></span><span id="presenceTxt"><?=$present <= 1 ? 'Just you here' : $present . ' people here'?></span>
</div>
