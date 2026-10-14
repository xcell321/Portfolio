<?php
// SVG icons only — walay emoji. Gamit: icon('heart') sa partials.

function icon(string $name, int $size = 18): string {
  $s = $size;
  $P = [
    'spark' => '<path d="M12 2l2.2 6.6L21 11l-6.8 2.4L12 20l-2.2-6.6L3 11l6.8-2.4z"/>',
    'heart' => '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
    'hand' => '<path d="M8 12V5.5a1.5 1.5 0 013 0V11m0-5.5v-1a1.5 1.5 0 013 0V11m0-4.5a1.5 1.5 0 013 0V12m0-2a1.5 1.5 0 013 0v4c0 4-2.5 7-7 7-2.5 0-4-1-5.5-3.5L3.6 14c-.6-1 .1-2.2 1.2-2.4.7-.1 1.4.2 1.8.8L8 14"/>',
    'mute' => '<path d="M11 5L6 9H3v6h3l5 4V5zm4 3l5 5m0-5l-5 5"/>',
    'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    'phone' => '<path d="M5 4h4l2 5-2.5 1.5c1 2.5 3 4.5 5.5 5.5L15.5 14l5 2v4c0 .6-.4 1-1 1C10.6 21 3 13.4 3 5c0-.6.4-1 1-1z"/>',
    'pin' => '<path d="M12 21s7-5.5 7-11a7 7 0 10-14 0c0 5.5 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
    'github' => '<path d="M12 3a9 9 0 00-2.8 17.5c.4.1.6-.2.6-.4v-1.5c-2.5.5-3-1-3-1-.4-1-1-1.3-1-1.3-.8-.6.1-.6.1-.6.9.1 1.4 1 1.4 1 .8 1.4 2.1 1 2.6.8.1-.6.3-1 .6-1.3-2-.2-4.1-1-4.1-4.5 0-1 .3-1.8 1-2.4-.1-.2-.4-1.1.1-2.4 0 0 .8-.3 2.7 1a9.4 9.4 0 015 0c1.9-1.3 2.7-1 2.7-1 .5 1.3.2 2.2.1 2.4.7.6 1 1.4 1 2.4 0 3.5-2.1 4.3-4.1 4.5.3.3.6.9.6 1.8v2.6c0 .2.2.5.6.4A9 9 0 0012 3z"/>',
    'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 11v5M8 8v.1M12 16v-3a2 2 0 014 0v3M12 11v1"/>',
    'facebook' => '<path d="M14 8h2V5h-2a3 3 0 00-3 3v2H9v3h2v6h3v-6h2l1-3h-3V8.5c0-.3.2-.5.5-.5z"/>',
    'arrow' => '<path d="M7 17L17 7M9 7h8v8"/>',
    'down'  => '<path d="M12 5v14m7-7-7 7-7-7"/>',
    'rocket' => '<path d="M12 15c5-4 7-9 7-12-3 0-8 2-12 7l-3 1 4 4 4 4 1-3z"/><circle cx="15" cy="9" r="1.5"/><path d="M9 12c-2 1-3 4-3 7 3 0 6-1 7-3"/>',
    'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M21 20H3"/>',
    'note' => '<path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h5"/>',
    'building' => '<rect x="5" y="3" width="14" height="18"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2M10 21v-3h4v3"/>',
    'shield' => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="M9.5 12l2 2 3.5-4"/>',
    'bot' => '<rect x="5" y="9" width="14" height="10" rx="2"/><path d="M12 5v4M9 5h6M9 14h.1M15 14h.1M9.5 17h5"/>',
    'brain' => '<path d="M9 4a3 3 0 00-3 3 3 3 0 00-2 5 3 3 0 002 5 3 3 0 006 1 3 3 0 006-1 3 3 0 002-5 3 3 0 00-2-5 3 3 0 00-3-3 3 3 0 00-3 2 3 3 0 00-3-2z"/>',
    'award' => '<circle cx="12" cy="9" r="5"/><path d="M8.5 13.5L7 21l5-2.5L17 21l-1.5-7.5"/>',
    'code' => '<path d="M8 6l-5 6 5 6M16 6l5 6-5 6"/>',
    'command' => '<path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/>',
    'palette' => '<circle cx="12" cy="12" r="9"/><circle cx="9" cy="10" r="1"/><circle cx="14" cy="9" r="1"/><circle cx="15" cy="14" r="1"/><path d="M12 21c-1 0-1-1-1-2h3c0 1 0 2-2 2z"/>',
    'mobile' => '<rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18.5h2"/>',
    'send' => '<path d="M21 3L10 14M21 3l-7 18-3-8-8-3z"/>',
    'moon' => '<path d="M20 14.5A8 8 0 019.5 4 8 8 0 0020 14.5z"/>',
    'chat' => '<path d="M21 12a8 8 0 01-8 8H4l2-3a8 8 0 1115-5z"/>',
    'plus' => '<path d="M12 5v14M5 12h14"/>',
    'check' => '<path d="M4 12.5l5 5L20 6.5"/>',
    'copy'  => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v"/>',
    'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-4 4-6 8-6s7 2 8 6"/>',
  ];
  $inner = $P[$name] ?? $P['spark'];
  return '<svg width="'.$s.'" height="'.$s.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$inner.'</svg>';
}

/** Daang emoji value -> icon name (para sa daan nga DB rows). */
function icon_for_emoji(?string $v): string {
  $map = ['🚀'=>'rocket','📊'=>'chart','📝'=>'note','🏗️'=>'building','🏗'=>'building','🛡️'=>'shield','🤖'=>'bot','🧠'=>'brain','📈'=>'chart','🏅'=>'award','👨🏻‍💻'=>'user','👨'=>'user'];
  return $map[$v] ?? 'spark';
}
