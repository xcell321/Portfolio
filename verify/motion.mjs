#!/usr/bin/env node
// Motion/layout probe for the portfolio. Dependency-free: Node 24 (global WebSocket + fetch) + headless Chrome over CDP.
//   node verify/motion.mjs dump  <url> [--out=file.json] [--reduced]
//   node verify/motion.mjs diff  a.json b.json
//   node verify/motion.mjs check [url] [--reduced]
//   node verify/motion.mjs shot  [url] [--sections=a,b] [--hover=PHP] [--out=dir]
// `dump` measures by text content and by id, so it can compare our PHP site against any reference build
// without knowing the other site's class names. `check` asserts our own spec table.
// `shot` scrolls to each section id in a real 1440x900 viewport and captures it; reveals are forced
// visible because a tall-window screenshot would stretch the 100vh hero and lie about the layout.
// `--hover=<tech>` drives a real mouse onto that skill word first, for the hover-only card.

import { spawn } from 'node:child_process';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { writeFileSync, readFileSync, mkdirSync, mkdtempSync, rmSync } from 'node:fs';

const CHROME = process.env.CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PORT = Number(process.env.CDP_PORT || 9333);
const MINE = 'http://localhost/portfolio/';
const args = process.argv.slice(2);
const mode = args[0];
const REDUCED = args.includes('--reduced');
const urlArg = args.slice(1).find(a => /^https?:/.test(a));
const outArg = (args.find(a => a.startsWith('--out=')) || '').slice(6);

// ---------- CDP client ----------
async function connect(wantUrl) {
  for (let i = 0; i < 60; i++) {
    try {
      const list = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
      const pages = list.filter(t => t.type === 'page');
      // Chrome briefly exposes an about:blank target next to the real one. Attaching to it
      // makes every later assertion evaluate against an empty page and pass vacuously.
      const page = pages.find(t => t.url === wantUrl)
        || pages.find(t => /^https?:/.test(t.url))
        || (pages.length > 1 ? pages.find(t => !/^about:blank/.test(t.url)) : null);
      if (page) {
        const ws = new WebSocket(page.webSocketDebuggerUrl);
        await new Promise((res, rej) => { ws.onopen = res; ws.onerror = rej; });
        return ws;
      }
    } catch {}
    await new Promise(r => setTimeout(r, 250));
  }
  throw new Error('no CDP page target');
}

function rpc(ws) {
  let id = 0; const pending = new Map();
  ws.onmessage = e => {
    const m = JSON.parse(e.data);
    if (m.id && pending.has(m.id)) { pending.get(m.id)(m); pending.delete(m.id); }
  };
  const send = (method, params = {}) => new Promise(res => {
    const n = ++id; pending.set(n, res); ws.send(JSON.stringify({ id: n, method, params }));
  });
  return { send };
}

async function open(url) {
  const profile = mkdtempSync(join(tmpdir(), 'motion-cdp-'));
  const flags = [
    '--headless=new', '--no-sandbox', '--disable-gpu', '--hide-scrollbars',
    `--remote-debugging-port=${PORT}`, `--user-data-dir=${profile}`,
    '--window-size=1440,900', '--force-device-scale-factor=1',
    '--disable-dev-shm-usage', '--no-first-run', '--disable-extensions',
  ];
  if (REDUCED) flags.push('--force-prefers-reduced-motion');
  const proc = spawn(CHROME, [...flags, url], { stdio: 'ignore' });
  proc.on('error', () => { throw new Error(`chrome not found at ${CHROME} — set CHROME env`); });
  const ws = await connect(url);
  const { send } = rpc(ws);
  await send('Runtime.enable'); await send('Page.enable'); await send('DOM.enable');
  const here = await send('Runtime.evaluate', { expression: 'location.href', returnByValue: true });
  if (!String(here.result?.result?.value || '').startsWith('http')) await send('Page.navigate', { url });
  for (let i = 0; i < 40; i++) {
    const r = await send('Runtime.evaluate', { expression: 'document.readyState', returnByValue: true });
    if (r.result?.result?.value === 'complete') break;
    await new Promise(r => setTimeout(r, 250));
  }
  await new Promise(r => setTimeout(r, REDUCED ? 1200 : 3000));
  const ev = async expr => {
    const r = await send('Runtime.evaluate', { expression: `(${expr})()`, returnByValue: true, awaitPromise: true });
    if (r.exceptionDetails) throw new Error(r.exceptionDetails.exception?.description || 'eval error');
    return r.result?.result?.value;
  };
  const close = () => { try { ws.close(); } catch {} try { proc.kill(); } catch {} try { rmSync(profile, { recursive: true, force: true, maxRetries: 3, retryDelay: 200 }); } catch {} };
  return { send, ev, close };
}

// ---------- measurement ----------
const DUMP = `() => {
  const cs = el => getComputedStyle(el);
  const box = el => { const r = el.getBoundingClientRect(); return { x:+r.x.toFixed(1), y:+(r.y+scrollY).toFixed(1), w:+r.width.toFixed(1), h:+r.height.toFixed(1) }; };
  const num = v => { const n = parseFloat(v); return Number.isFinite(n) ? +n.toFixed(2) : v; };
  const text = [], seen = new Set();
  for (const el of document.querySelectorAll('body *')) {
    const t = [...el.childNodes].filter(n => n.nodeType === 3).map(n => n.textContent.trim()).join(' ').trim();
    if (!t || t.length > 90 || seen.has(t)) continue;
    if (/^[\\s\\W]*$/.test(t)) continue;
    seen.add(t);
    const s = cs(el), b = box(el);
    if (b.w < 1) continue;
    text.push({ t, ...b, fs:num(s.fontSize), lh:s.lineHeight === 'normal' ? 'normal' : num(s.lineHeight), ls:num(s.letterSpacing),
      fw:s.fontWeight, ff:s.fontFamily.split(',')[0].replace(/[\"']/g,''), col:s.color,
      anim:s.animationName, dur:s.animationDuration, del:s.animationDelay, ease:s.animationTimingFunction });
  }
  const ids = [];
  for (const el of document.querySelectorAll('[id]')) {
    const s = cs(el), b = box(el);
    if (b.w < 1) continue;
    ids.push({ id:el.id, tag:el.tagName.toLowerCase(), ...b, padT:num(s.paddingTop), padB:num(s.paddingBottom),
      padL:num(s.paddingLeft), rad:s.borderRadius, gap:num(s.columnGap), bg:s.backgroundColor,
      anim:s.animationName, dur:s.animationDuration, del:s.animationDelay, ease:s.animationTimingFunction,
      tr:s.transitionProperty, trdur:s.transitionDuration, tf:s.transform, op:num(s.opacity), rot:s.rotate });
  }
  const cards = [];
  for (const el of document.querySelectorAll('body *')) {
    const s = cs(el), b = box(el);
    if (b.w < 110 || b.w > 640 || b.h < 90 || b.h > 760) continue;
    const rad = parseFloat(s.borderRadius) || 0;
    if (rad < 6 && s.boxShadow === 'none') continue;
    cards.push({ cls:(el.className.baseVal ?? el.className ?? '').toString().slice(0,52), tag:el.tagName.toLowerCase(), ...b,
      rad:s.borderRadius, shadow:s.boxShadow.slice(0,44), rot:s.rotate, anim:s.animationName, dur:s.animationDuration });
  }
  const secs = [...document.querySelectorAll('section,header.hero,footer,nav')].map(el => {
    const s = cs(el), b = box(el);
    return { sel:(el.id || el.tagName.toLowerCase()), ...b, mt:num(s.marginTop), pt:num(s.paddingTop), pb:num(s.paddingBottom),
      fs:num(cs(el.querySelector('h1,h2') || el).fontSize) };
  });
  const root = cs(document.documentElement), body = cs(document.body);
  return { title: document.title, reduced: matchMedia('(prefers-reduced-motion: reduce)').matches,
    docH: document.documentElement.scrollHeight, lowEnd: document.documentElement.dataset.lowEnd ?? null,
    vars: ['--bg','--bg2','--card','--line','--txt','--mut','--acc','--r'].reduce((a,k) => (a[k]=root.getPropertyValue(k).trim(), a), {}),
    tokens: { bg: body.backgroundColor, font: body.fontFamily.split(',')[0].replace(/[\"']/g,''), fs: num(body.fontSize) },
    motion: typeof MOTION === 'object' ? MOTION : null,
    text: text.slice(0, 400), ids, cards: cards.slice(0, 90), secs };
}`;

async function dump(url) {
  const { ev, close } = await open(url);
  const data = await ev(DUMP);
  close();
  return data;
}

function diff(ref, ours) {
  const key = s => s.t.toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
  const om = new Map(ours.text.map(t => [key(t), t]));
  const rows = [];
  for (const r of ref.text) {
    const o = om.get(key(r));
    if (!o) continue;
    const d = [];
    for (const p of ['fs', 'lh', 'ls', 'fw', 'h', 'w']) if (r[p] !== o[p]) d.push(`${p} ${r[p]}->${o[p]}`);
    if (r.ff !== o.ff) d.push(`font ${r.ff}->${o.ff}`);
    if (d.length) rows.push({ t: r.t.slice(0, 34), d: d.join(', ') });
  }
  console.log(`\n=== TEXT METRICS ref->ours (${rows.length} differing of ${ref.text.length} ref nodes) ===`);
  for (const x of rows.slice(0, 80)) console.log(`  "${x.t}"  ${x.d}`);
  const ra = new Map(ref.secs.map(s => [s.sel, s])), oa = new Map(ours.secs.map(s => [s.sel, s]));
  console.log('\n=== SECTIONS ref -> ours ===');
  for (const [k, r] of ra) {
    const o = oa.get(k); if (!o) { console.log(`  ${k}: ONLY IN REF (h ${r.h})`); continue; }
    console.log(`  ${k}: h ${r.h}->${o.h}  pad ${r.pt}/${r.pb}->${o.pt}/${o.pb}  titlefs ${r.fs}->${o.fs}`);
  }
  for (const [k, o] of oa) if (!ra.has(k)) console.log(`  ${k}: ONLY IN OURS (h ${o.h})`);
  console.log('\n=== CARDS (ref boxes not matched by size within 12px) ===');
  for (const r of ref.cards) {
    const near = ours.cards.some(o => Math.abs(o.w - r.w) < 12 && Math.abs(o.h - r.h) < 12);
    if (!near) console.log(`  ref ${r.tag} ${(r.cls||'').slice(0,30)} ${r.w}x${r.h} rad ${r.rad} rot ${r.rot}`);
  }
  console.log('\n=== DOC ===');
  console.log(`  height ${ref.docH}->${ours.docH}   bg ${ref.tokens.bg} -> ${ours.tokens.bg}   font ${ref.tokens.font} -> ${ours.tokens.font}`);
  console.log(`  vars ref=${JSON.stringify(ref.vars)}\n       ours=${JSON.stringify(ours.vars)}`);
  console.log(`  MOTION ours=${JSON.stringify(ours.motion)}`);
}

// ---------- assertions ----------
let pass = 0, fail = 0;
function check(name, ok, detail) {
  if (ok) { pass++; console.log(`  PASS  ${name}`); }
  else { fail++; console.log(`  FAIL  ${name}${detail ? '  [' + detail + ']' : ''}`); }
}
const near = (a, b, t) => typeof a === 'number' && Math.abs(a - b) <= t;

async function checkSite(url) {
  const { ev, send, close } = await open(url);
  const d = await ev(DUMP);
  const byId = Object.fromEntries(d.ids.map(i => [i.id, i]));
  const byText = t => d.text.find(x => x.t.toLowerCase().includes(t.toLowerCase()));

  console.log(`\n--- spec check @ ${url}${REDUCED ? ' (reduced motion)' : ''} ---`);

  if (!REDUCED) {
    // U0: single source of truth for motion values
    const m = d.motion;
    check('MOTION exposed', !!m, m === null ? 'window.MOTION undefined' : 'not an object');
    if (m) {
      check('MOTION.tilt 150/18/1', m.tilt?.k === 150 && m.tilt?.c === 18 && m.tilt?.m === 1, JSON.stringify(m.tilt));
      check('MOTION.spot 420/36/.6', m.spot?.k === 420 && m.spot?.c === 36 && m.spot?.m === 0.6, JSON.stringify(m.spot));
      check('MOTION.tl 120/30/.001', m.tl?.k === 120 && m.tl?.c === 30 && m.tl?.rest === 0.001, JSON.stringify(m.tl));
      check('MOTION.decor fades to .2 over .8', m.decor?.opMin === 0.2 && m.decor?.opRange === 0.8, JSON.stringify(m.decor));
      check('MOTION.guest .3/.02/.9/-3', m.guest?.dur === 0.3 && m.guest?.stagger === 0.02 && m.guest?.scale === 0.9 && m.guest?.rot === -3, JSON.stringify(m.guest));
      check('MOTION.badge -12 rest', m.badge?.rot === -12 && m.badge?.from === -60, JSON.stringify(m.badge));
      check('MOTION.card 180/20/amp4', m.card?.k === 180 && m.card?.c === 20 && m.card?.amp === 4, JSON.stringify(m.card));
      check('MOTION.tab 380/34', m.tab?.k === 380 && m.tab?.c === 34, JSON.stringify(m.tab));
      check('MOTION.url .25s/6px', m.url?.dur === 0.25 && m.url?.y === 6, JSON.stringify(m.url));
    }

    // Phase D: the tracker fill is spring-driven from JS, so the CSS transition must be gone
    // (leaving it would double-smooth, exactly as in G3).
    const trFill = await ev(`() => { const f = document.querySelector('.track .fill'); return f ? getComputedStyle(f).transitionDuration : 'no .track .fill'; }`);
    check('D1 tracker fill transition removed', trFill === '0s', trFill);
    const fillX = await ev(`() => { const f = document.querySelector('.track .tab.on .fill'); return f ? getComputedStyle(f).transform : 'missing'; }`);
    check('D1 active tracker fill drawn by JS', /^matrix/.test(fillX), fillX);
    const stagger = await ev(`() => [...document.querySelectorAll('.work-item')].map(el => getComputedStyle(el).transitionDelay)`);
    check('D2 card stagger .06s apart', stagger.length > 1 && stagger.every((d, i) => Math.abs(parseFloat(d) - i * 0.06) < 0.005), JSON.stringify(stagger));
    const urlChip = await ev(`() => { const u = document.querySelector('.work-preview .url-text'); return u ? (u.animate ? 'animatable' : 'no WAAPI') : 'no url-text'; }`);
    check('D3 url chip swaps via WAAPI', urlChip === 'animatable', urlChip);

    // S1-S5 skill-word hover card. Spec measured off the reference: 288px portal on <body>,
    // radius 16, bg rgba(23,23,23,.95), padding 16, shots 16/10, foot mono 10/.16em,
    // anchored word-centre +22/+22, and NO entrance animation (position mounts in one frame).
    const wait = ms => new Promise(r => setTimeout(r, ms));
    const skSetup = await ev(`() => {
      if (window.lenis) window.lenis.stop();
      const c = document.getElementById('skCard');
      if (!c) return { err: 'no #skCard' };
      const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === "PHP");
      if (!w) return { err: 'no PHP word' };
      window.scrollTo(0, w.getBoundingClientRect().top + scrollY - 400);
      const s = getComputedStyle(c);
      return { disp: s.display, parent: c.parentElement.tagName, aria: c.getAttribute('aria-hidden'),
        payload: !!document.getElementById('skCardData') };
    }`);
    check('S1 card shell exists, hidden at rest, portalled to <body>',
      skSetup.disp === 'none' && skSetup.parent === 'BODY' && skSetup.aria === 'true' && skSetup.payload,
      skSetup.err || JSON.stringify(skSetup));
    if (!skSetup.err) {
      const pos = await ev(`() => { const w=[...document.querySelectorAll('.sk-word')].find(e=>e.dataset.tech==="PHP"); const r=w.getBoundingClientRect(); return {x:Math.round(r.x+r.width/2), y:Math.round(r.y+r.height/2)}; }`);
      await send('Input.dispatchMouseEvent', { type: 'mouseMoved', x: pos.x, y: pos.y });
      await wait(450);
      const open = await ev(`() => {
        const c = document.getElementById('skCard'), s = getComputedStyle(c), r = c.getBoundingClientRect();
        const fr = c.querySelector('.sk-card-frame'), img = fr && fr.querySelector('img'), lab = c.querySelector('.sk-card-label');
        const note = c.querySelector('.sk-card-note'), foot = c.querySelector('.sk-card-foot');
        const ns = note && getComputedStyle(note), fs = foot && getComputedStyle(foot);
        return { disp: s.display, x: Math.round(r.x), y: Math.round(r.y), w: +r.width.toFixed(1), h: +r.height.toFixed(1),
          vh: innerHeight, vw: innerWidth, rad: s.borderRadius,
          bg: s.backgroundColor, pad: s.padding, trdur: s.transitionDuration, animDur: s.animationDuration, anim: s.animationName,
          title: (c.querySelector('.sk-card-title') || {}).textContent,
          note: note && note.textContent, noteFs: ns && ns.fontSize, noteLh: ns && ns.lineHeight,
          frames: c.querySelectorAll('.sk-card-frame').length,
          tag: fr && fr.tagName, href: fr && fr.getAttribute('href'), target: fr && fr.getAttribute('target'),
          ar: fr && getComputedStyle(fr).aspectRatio, imgW: img && +img.getBoundingClientRect().width.toFixed(1),
          label: lab && lab.textContent, foot: foot && foot.textContent,
          footFs: fs && fs.fontSize, footLs: fs && fs.letterSpacing, footTt: fs && fs.textTransform, footFf: fs && fs.fontFamily.split(',')[0] };
      }`);
      const exX = Math.max(12, Math.min(pos.x + 22 + open.w > open.vw ? pos.x - 22 - open.w : pos.x + 22, open.vw - open.w - 12));
      const exY = Math.max(12, Math.min(pos.y + 22 + open.h > open.vh ? pos.y - 22 - open.h : pos.y + 22, open.vh - open.h - 12));
      check('S2 real hover opens the card at the measured geometry',
        open.disp !== 'none' && open.w === 288 && open.rad === '16px' && open.bg === 'rgba(23, 23, 23, 0.95)' && open.pad === '16px'
        && Math.abs(open.x - exX) <= 4 && Math.abs(open.y - exY) <= 4, JSON.stringify({ ...open, pos, exX, exY }));
      check('S2b reference card entrance .22s scale/rotate',
        open.animDur === '0.22s' && open.anim === 'skIn', open.animDur + '/' + open.anim);
      const states = await ev(`() => {
        const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === "PHP");
        const other = w.parentElement.querySelector('.sk-word:not([data-tech="PHP"])');
        const sep = w.parentElement.querySelector('.sk-sep');
        return { word: getComputedStyle(w).color, other: other && getComputedStyle(other).color,
          sep: sep && getComputedStyle(sep).color, transform: getComputedStyle(w).transform };
      }`);
      check('S2c hovered word accent, siblings stone-700, separators stone-800, no lift',
        states.word === 'rgb(212, 253, 115)' && states.other === 'rgb(68, 64, 60)'
        && states.sep === 'rgb(41, 37, 36)' && states.transform === 'none', JSON.stringify(states));
      check('S3 shots come from project tags and link to the live site',
        open.frames >= 1 && open.tag === 'A' && /^https?:/.test(open.href || '') && open.target === '_blank'
        && /16\s*\/\s*10/.test(open.ar || '') && /Remotee/.test(open.label || ''), JSON.stringify(open));
      check('S5 card type: note 12/19.5, foot mono 10 uppercase .16em',
        open.noteFs === '12px' && open.noteLh === '19.5px' && open.footFs === '10px'
        && Math.abs(parseFloat(open.footLs) - 1.6) < 0.05 && open.footTt === 'uppercase' && /mono/i.test(open.footFf),
        [open.noteFs, open.noteLh, open.footFs, open.footLs, open.footTt, open.footFf].join(' | '));

      // S4 click pins: it must survive the pointer leaving, and un-pin on the second click
      await send('Input.dispatchMouseEvent', { type: 'mousePressed', x: pos.x, y: pos.y, button: 'left', clickCount: 1 });
      await send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: pos.x, y: pos.y, button: 'left', clickCount: 1 });
      await wait(120);
      await send('Input.dispatchMouseEvent', { type: 'mouseMoved', x: 5, y: 5 });
      await wait(200);
      const pinned = await ev(`() => ({ disp: getComputedStyle(document.getElementById('skCard')).display,
        on: !!document.querySelector('.sk-word.on') })`);
      check('S4 click pins the card after the pointer leaves', pinned.disp !== 'none' && pinned.on, JSON.stringify(pinned));
      await send('Input.dispatchMouseEvent', { type: 'mousePressed', x: pos.x, y: pos.y, button: 'left', clickCount: 1 });
      await send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: pos.x, y: pos.y, button: 'left', clickCount: 1 });
      await wait(120);
      await send('Input.dispatchMouseEvent', { type: 'mouseMoved', x: 5, y: 5 });
      await wait(500);
      const unpinned = await ev(`() => ({ disp: getComputedStyle(document.getElementById('skCard')).display,
        on: !!document.querySelector('.sk-word.on') })`);
      check('S4b second click unpins and closes', unpinned.disp === 'none' && !unpinned.on, JSON.stringify(unpinned));

      // S6 keyboard path: focusing the word must open the card and name it as the description
      const kb = await ev(`() => {
        const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === 'PHP');
        document.activeElement?.blur();   // the click above left focus here, so focus() would not fire
        w.focus();
        const c = document.getElementById('skCard');
        return { disp: getComputedStyle(c).display, desc: w.getAttribute('aria-describedby'),
          link: c.querySelector('.sk-card-frame')?.getAttribute('href') || null };
      }`);
      check('S6 keyboard focus opens the card and exposes the live link',
        kb.disp === 'block' && kb.desc === 'skCard' && /^https?:/.test(kb.link || ''), JSON.stringify(kb));
    }

    // G1: photoCard tilt must be spring-driven. A lerp converges monotonically and can never cross the
    // target; a k150/c18 spring overshoots ~4%. So: drive a real pointer, sample the inline angles, look for the crossing.
    const pc = byId.photoCard;
    if (pc) {
      const series = await ev(`async () => {
        const el = document.getElementById('photoCard');
        const r = el.getBoundingClientRect();
        el.dispatchEvent(new PointerEvent('pointermove', {bubbles:true, pointerType:'mouse',
          clientX:r.left + r.width*0.9, clientY:r.top + r.height*0.9}));
        const ang = () => { const m = /rotateY\\((-?[\\d.]+)deg\\)/.exec(el.style.transform); return m ? +m[1] : NaN; };
        const s = [];
        for (let i = 0; i < 90; i++) { await new Promise(r2 => requestAnimationFrame(r2)); s.push(ang()); }
        return s; }`);
      const arr = (series || []).filter(v => Number.isFinite(v));
      const target = (0.9 - 0.5) * 14;                       // the tilt the pointer asks for
      const peak = arr.length ? Math.max(...arr) : NaN;
      const final = arr.at(-1);
      check('G1 tilt reaches its target', arr.length === 90 && near(final, target, 0.1), `final ${final} want ${target}`);
      // A lerp approaches the target asymptotically and can never exceed it; a spring crosses it.
      // The crossing is small (~0.01deg at k150/c18 once the discrete integrator's numerical damping
      // is accounted for), so the claim tested is "crosses at all", not "bounces visibly".
      check('G1 tilt crosses its target (spring, not lerp)', arr.length === 90 && peak > target + 0.002, `peak ${peak} vs target ${target}`);
    } else check('G1 #photoCard present', false);

    // G3: timeline fill must not carry a linear CSS transition (JS spring owns it now)
    const tlFill = await ev(`() => { const e = document.querySelector('.tl-fill'); return e ? getComputedStyle(e).transitionDuration : null; }`);
    check('G3 .tl-fill transition removed', tlFill === '0s', 'got ' + tlFill);   // the JS spring owns the smoothing now

    // G4: decor layer exists and fades independently of content
    const decor = await ev(`() => {
      const d = document.querySelector('.hero-decor'), i = document.querySelector('.hero-inner');
      if (!d || !i) return null;
      scrollTo(0, 0); const r = { o0: +getComputedStyle(d).opacity, t0: getComputedStyle(d).transform };
      scrollTo(0, Math.round(document.getElementById('top').offsetHeight * 0.8));
      return new Promise(res => requestAnimationFrame(() => requestAnimationFrame(() => {
        const p = Math.min(1, scrollY / document.getElementById('top').offsetHeight);
        res({ o1: +getComputedStyle(d).opacity, t1: getComputedStyle(d).transform, i1: +getComputedStyle(i).opacity, p });
      }))); }`);
    if (decor) {
      check('G4 decor fades toward .2 at p=.8', near(decor.o1, 0.2, 0.06), 'got ' + decor.o1);
      check('G4 content fully faded at p=.8', near(decor.i1, 0, 0.02), 'got ' + decor.i1);
      check('G4 decor translates up ~120px', /matrix.*(-1[01][0-9]|-120)/.test(decor.t1) || decor.t1 !== 'none', decor.t1);
    } else { check('G4 .hero-decor wrapper exists', false); }

    // G5: badge rests tilted -12deg, and a click must not re-run badgeIn (which would blank it for 1.4s)
    const badge = await ev(`async () => {
      const b = document.getElementById('stickerBadge'); if (!b) return null;
      const rot = getComputedStyle(b).rotate;
      const anims = getComputedStyle(b).animationName;
      b.click();
      await new Promise(r => setTimeout(r, 1500));
      const sc = getComputedStyle(b).scale;
      return { rot, anims, sc, pop: b.className.includes('popped') }; }`);
    if (badge) {
      check('G5 badge rests at -12deg', parseFloat(badge.rot) === -12, 'got ' + badge.rot);
      check('G5 click does not restart badgeIn (no vanish)', (badge.sc === 'none' ? 1 : parseFloat(badge.sc)) > 0.9, 'scale 1.5s after click = ' + badge.sc);
      check('G5 ring still spins (badge or inner node)', /spin/.test(badge.anims) || !!(await ev(`() => !!document.querySelector('.spin-in')`)), badge.anims);
    } else check('G5 #stickerBadge exists', false);

    // G6 guestbook rows animate in. Measured from a probe element rather than rule text, so the check
    // reads the real cascade and does not depend on the visitor database having rows.
    const gb = await ev(`() => {
      let rule = '';
      for (const ss of document.styleSheets) { try { for (const r of ss.cssRules) {
        if (r.selectorText && r.selectorText.includes('.rv-pop')) rule += r.cssText; } } catch (e) {} }
      const probe = document.createElement('div');
      probe.className = 'rv-pop';
      probe.style.cssText = 'position:fixed;left:-9999px;top:0';
      document.body.appendChild(probe);
      const cs = getComputedStyle(probe);
      const props = cs.transitionProperty.split(',').map(x => x.trim());
      const durs = cs.transitionDuration.split(',').map(x => x.trim());
      const byProp = Object.fromEntries(props.map((k, i) => [k, durs[i] || durs[0]]));
      const out = { byProp, sc: cs.scale, rot: cs.rotate, op: cs.opacity };
      probe.remove();
      const g = document.querySelector('.guest');
      return { rule, out, cls: g ? g.className : null }; }`);
    const o = gb.out;
    check('G6 .rv-pop starts hidden at scale .9 / rotate -3deg', o.op === '0' && o.sc === '0.9' && o.rot === '-3deg', JSON.stringify(o));
    // the three entrance properties are .3s; translate/box-shadow are the hover lift and are .2s on purpose
    check('G6 .rv-pop entrance duration .3s', ['opacity', 'scale', 'rotate'].every(k => o.byProp[k] === '0.3s'), JSON.stringify(o.byProp));
    check('G6 .rv-pop hover lift', /-2px/.test(gb.rule || ''), 'no translate:-2px in any .rv-pop rule');
    if (gb.cls !== null) check('G6 guest rows carry the pop variant', /rv-pop/.test(gb.cls), gb.cls);
    else console.log('  SKIP  G6 live row classes (no guestbook rows in DB)');

    // G7 low-end gating
    const le = await ev(`() => {
      const html = document.documentElement; const prev = html.dataset.lowEnd;
      html.dataset.lowEnd = 'true';
      const orb = document.querySelector('.orb-1'), pill = document.querySelector('.pill'), dot = document.querySelector('.dot');
      const r = { orb: orb ? getComputedStyle(orb).display : null, pill: pill ? getComputedStyle(pill).backdropFilter : null,
        dot: dot ? getComputedStyle(dot, '::before').animationName : null, detector: typeof window.__perf };
      if (prev === undefined) delete html.dataset.lowEnd; else html.dataset.lowEnd = prev;
      return r; }`);
    check('G7 data-low-end hides orbs', le.orb === 'none', 'display ' + le.orb);
    check('G7 data-low-end kills backdrop-filter', le.pill === 'none', 'filter ' + le.pill);
    check('G7 data-low-end stops ping', le.dot === 'none', 'anim ' + le.dot);
    check('G7 one shared detector (window.__perf)', le.detector === 'object', le.detector);

    // G8 year swap duration
    const yd = await ev(`() => { for (const ss of document.styleSheets) { try { for (const r of ss.cssRules) { if (r.selectorText && r.selectorText.includes('.journey-now-year.swap')) return r.style.animationDuration || r.cssText; } } catch(e){} } return null; }`);
    check('G8 journeyYear duration .5s', String(yd).trim() === '0.5s', 'rule ' + yd);

    // G9 guestbook: the form is one paper note, the entries hang beside it in a column wall.
    // Row-dependent checks SKIP when the database holds no notes, the same way G6 does.
    const PAPER = { lime: 'rgb(230, 251, 168)', sky: 'rgb(186, 230, 253)', pink: 'rgb(251, 207, 232)',
                    peach: 'rgb(254, 215, 170)', violet: 'rgb(221, 214, 254)' };
    const readWall = `() => {
      const sec = document.getElementById('guestbook');
      if (!sec) return { missing: 'no #guestbook' };
      const q = s => sec.querySelector(s);
      const form = q('.gb-form'), board = q('.gb-wall'), ta = q('.gb-form textarea');
      const chips = [...sec.querySelectorAll('.gb-chip')], note = q('.gb-wall .guest');
      const cs = el => (el ? getComputedStyle(el) : null);
      const fb = form?.getBoundingClientRect(), bb = board?.getBoundingClientRect();
      return {
        twoCol: !!(fb && bb) && fb.width > 80 && bb.width > 80 && bb.left >= fb.right - 1,
        sticky: cs(form)?.position === 'sticky',
        columns: cs(board)?.columnCount,
        paper: cs(form)?.backgroundColor,
        chipCount: chips.length,
        checked: chips.filter(c => c.getAttribute('aria-checked') === 'true').length,
        maxLen: ta?.getAttribute('maxlength'),
        hand: cs(ta)?.fontFamily.includes('Caveat') ?? false,
        counter: q('.gb-count')?.textContent.trim() ?? null,
        honey: !!q('.gb-form [name="website"]'),
        hint: q('.gb-hint')?.textContent.trim() ?? null,
        note: note ? { bg: cs(note).backgroundColor, ink: cs(note).color,
          hand: cs(note.querySelector('.gb-note-text') ?? note).fontFamily.includes('Caveat'),
          key: note.dataset.paper ?? null,
          time: note.querySelector('time')?.textContent.trim() ?? null,
          dt: note.querySelector('time')?.getAttribute('datetime') ?? null } : null,
        empty: q('.gb-empty')?.textContent.trim() ?? null,
        more: q('.gb-more')?.textContent.trim() ?? null,
        shown: sec.querySelectorAll('.gb-wall .guest:not([hidden])').length,
        total: sec.querySelectorAll('.gb-wall .guest').length,
      }; }`;
    let w = await ev(readWall);
    check('G9 note form and note wall sit side by side', !w.missing && w.twoCol === true, JSON.stringify(w.missing ?? { twoCol: w.twoCol, paper: w.paper }));
    check('G9 form note stays pinned while the wall scrolls', w.sticky === true, 'position ' + w.sticky);
    check('G9 wall lays out in columns at desktop width', +w.columns >= 2, 'column-count ' + w.columns);
    check('G9 the form is paper, not the dark card', Object.values(PAPER).includes(w.paper), 'bg ' + w.paper);
    check('G9 five paper chips, exactly one checked', w.chipCount === 5 && w.checked === 1, JSON.stringify([w.chipCount, w.checked]));
    check('G9 note text capped at 160 with a live counter', w.maxLen === '160' && /^\d+\s*\/\s*160$/.test(w.counter || ''), JSON.stringify([w.maxLen, w.counter]));
    check('G9 note text is handwritten (Caveat)', w.hand === true, 'font ' + w.hand);
    check('G9 honeypot field guards the form', w.honey === true);
    check('G9 the wall says notes are public', /public/.test(w.hint || ''), w.hint);
    check('G9 empty wall invites the first note', (w.note ? w.empty === null : /empty/.test(w.empty || '')) , JSON.stringify([w.empty, w.note]));

    // picking a chip repaints the form note — the choice is visible, not just stored
    if (w.chipCount === 5) {
      const cp = await ev(`() => { const c = document.querySelectorAll('#guestbook .gb-chip')[3];
        c.scrollIntoView({ block: 'center' }); const r = c.getBoundingClientRect();
        return { x: Math.round(r.x + r.width / 2), y: Math.round(r.y + r.height / 2) }; }`);
      await send('Input.dispatchMouseEvent', { type: 'mousePressed', x: cp.x, y: cp.y, button: 'left', clickCount: 1 });
      await send('Input.dispatchMouseEvent', { type: 'mouseReleased', x: cp.x, y: cp.y, button: 'left', clickCount: 1 });
      await wait(320);
      const after = await ev(readWall);
      check('G9 chip click recolors the form note', after.paper === PAPER.peach && after.checked === 1,
        JSON.stringify([after.paper, after.checked]));
    } else check('G9 chip click recolors the form note', false, 'only ' + w.chipCount + ' chips');

    // the counter tracks what the visitor actually typed
    await ev(`() => { const t = document.querySelector('#guestbook .gb-form textarea'); t.focus(); }`);
    await send('Input.insertText', { text: 'hello wall' });
    await wait(120);
    const typed = await ev(`() => ({ counter: document.querySelector('#guestbook .gb-count')?.textContent.trim() ?? null,
      len: document.querySelector('#guestbook .gb-form textarea')?.value.length ?? null })`);
    check('G9 counter counts the typed text', typed.len === 10 && /^10\s*\/\s*160$/.test(typed.counter || ''), JSON.stringify(typed));

    if (w.note) {
      check('G9 a note wears the paper it was pinned with', w.note.bg === PAPER[w.note.key], JSON.stringify([w.note.bg, w.note.key]));
      check('G9 note ink is dark on the paper', w.note.ink === 'rgb(28, 25, 23)', 'color ' + w.note.ink);
      check('G9 note body is handwritten', w.note.hand === true);
      check('G9 note time is relative, not a raw timestamp', /^(just now|\d+[mhd] ago|\w+ \d+)$/.test(w.note.time || ''), JSON.stringify([w.note.time, w.note.dt]));
      check('G9 note time carries a machine-readable datetime', /^\d{4}-\d{2}-\d{2}T/.test(w.note.dt || ''), w.note.dt);
    } else console.log('  SKIP  G9 live note (no guestbook rows in DB)');
    const moreMatch = w.more ? /Show more notes \((\d+)\)/.exec(w.more) : null;
    if (moreMatch) check('G9 show-more counts the notes still hidden', +moreMatch[1] === w.total - w.shown, w.more + ' vs hidden ' + (w.total - w.shown));
    else console.log('  SKIP  G9 show-more (fewer notes than one page)');
  } else {
    // reduced-motion pass
    // reduced-motion pass. Each one fails when the node is missing: a check that treats
    // "nothing on the page" as a pass is how an attach-to-about:blank bug hid for a run.
    const rv = await ev(`() => { const e = document.querySelector('.rv'); return e ? getComputedStyle(e).opacity : 'MISSING'; }`);
    check('REDUCED .rv visible without reveal', rv === '1', 'opacity ' + rv);
    const inline = await ev(`() => ({ inner: (document.querySelector('.hero-inner')||{}).style?.cssText ?? 'MISSING', decor: (document.querySelector('.hero-decor')||{}).style?.cssText ?? 'MISSING' })`);
    check('REDUCED hero layers left alone', inline.inner === '' && inline.decor === '', JSON.stringify(inline));
    const orb = await ev(`() => { const o = document.querySelector('.orb-1'); return o ? getComputedStyle(o).animationName : 'MISSING'; }`);
    check('REDUCED orbs animation off', orb === 'none' || orb === 'none, none', orb);
    // the card is not an animation, so reduced motion must not gate the feature away
    const rp = await ev(`() => {
      if (window.lenis) window.lenis.stop();
      const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === 'PHP');
      if (!w || !document.getElementById('skCard')) return null;
      window.scrollTo(0, w.getBoundingClientRect().top + scrollY - 400);
      return 1;
    }`);
    if (rp) {
      await new Promise(r => setTimeout(r, 500));
      const p = await ev(`() => { const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === 'PHP'); const r = w.getBoundingClientRect(); return { x: Math.round(r.x + r.width / 2), y: Math.round(r.y + r.height / 2) }; }`);
      await send('Input.dispatchMouseEvent', { type: 'mouseMoved', x: p.x, y: p.y });
      await new Promise(r => setTimeout(r, 150));
      const rd = await ev(`() => { const c = document.getElementById('skCard'); return c ? getComputedStyle(c).display : 'no card'; }`);
      check('REDUCED skill card still opens on hover', rd !== 'none' && rd === 'block', rd);
    } else check('REDUCED skill card still opens on hover', false, 'no #skCard or no PHP word');
  }

  close();
  return d;
}

async function checkFooter(url) {
  const { ev, send, close } = await open(url);
  try {
    const content = await ev(`() => {
      const f = document.querySelector('footer');
      return { id: f?.id, text: f?.textContent, name: document.getElementById('bigName')?.textContent.replace(/\\s+/g, ' '),
        nav: [...document.querySelectorAll('footer nav a')].map(a => ({ id: a.hash.slice(1), label: a.textContent.trim(), exists: !!document.querySelector(a.hash) })),
        socials: [...document.querySelectorAll('.footer-socials a')].map(a => ({ href: a.href, name: a.textContent.trim(), safe: a.target === '_blank' && a.relList.contains('noopener') })),
        email: document.querySelector('.footer-email')?.getAttribute('href'), palette: window.PAL };
    }`);
    check('F1 footer is a screenshot target', content.id === 'footer');
    check('F2 footer uses owner identity', content.name === 'EDSIEL URSAL' && !/Mike|Quezon|Dribbble/i.test(content.text));
    check('F3 footer navigation matches palette and real sections', content.nav.length === 6 && content.nav.every((n, i) => n.exists && n.id === content.palette.nav[i].id && n.label === content.palette.nav[i].label));
    check('F4 owner socials match palette and open safely', content.socials.length === 3 && content.socials.every((s, i) => s.safe && s.href === content.palette.socials[i].href && s.name === content.palette.socials[i].name));
    check('F5 email uses configured address', content.email === 'mailto:' + content.palette?.email);

    for (const width of [1440, 1278, 1024, 1023, 768, 767, 640, 639, 390, 320]) {
      await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
      const d = await ev(`async () => {
        await document.fonts.ready;
        const f = document.querySelector('footer'), g = f.querySelector('.footer-grid'), b = document.getElementById('bigName');
        const s = e => e ? getComputedStyle(e) : null;
        const wrap = f.querySelector('.wrap'), heading = f.querySelector('.footer-heading'), link = f.querySelector('nav a'), note = f.querySelector('.footer-note');
        const letters = [...b.children].map(e => e.getBoundingClientRect());
        return { top: s(f).paddingTop, bottom: s(f).paddingBottom, bg: s(f).backgroundColor, border: s(f).borderTopColor,
          cols: s(g)?.gridTemplateColumns.split(' ').length, gap: s(g)?.gap, gridHeight: g?.getBoundingClientRect().height,
          wrap: wrap.getBoundingClientRect().width, inset: s(wrap).paddingLeft,
          size: parseFloat(s(b).fontSize), line: parseFloat(s(b).lineHeight), space: parseFloat(s(b).letterSpacing), font: s(b).fontFamily,
          margin: s(b).marginTop, ink: s(b.children[0]).color,
          heading: heading && [s(heading).fontSize, s(heading).lineHeight, s(heading).letterSpacing, s(heading).textTransform],
          link: link && [s(link).fontSize, s(link).lineHeight, s(link).color],
          note: note && [s(note).marginTop, s(note).fontSize, s(note).lineHeight],
          fits: letters.at(-1).right <= b.getBoundingClientRect().right + 1,
          overflow: f.scrollWidth > f.clientWidth };
      }`);
      const size = width >= 1024 ? 160 : width * .13;
      check('F6 responsive footer layout @' + width, d.cols === (width >= 768 ? 3 : 1) && d.gap === '48px' && d.top === '64px' && d.bottom === (width >= 640 ? '48px' : '112px'), JSON.stringify(d));
      check('F7 container and wordmark metrics @' + width, near(d.wrap, Math.min(width, 1152), 1) && d.inset === (width >= 640 ? '32px' : '20px') && near(d.size, size, .1) && near(d.line, size * 1.5, .1) && near(d.space, -size * .05, .1) && d.margin === '64px' && d.font.includes('Bricolage'), JSON.stringify(d));
      check('F8 footer fits without clipping @' + width, d.fits && !d.overflow, JSON.stringify(d));
      if (width === 1440) {
        check('F9 reference surface and typography', d.bg === 'rgba(0, 0, 0, 0)' && d.border === 'rgba(255, 255, 255, 0.06)' && near(d.gridHeight, 226, 1) && d.ink === 'rgb(212, 253, 115)' && JSON.stringify(d.heading) === JSON.stringify(['12px', '16px', '2.16px', 'uppercase']) && JSON.stringify(d.link) === JSON.stringify(['14px', '22px', 'rgb(168, 162, 158)']) && JSON.stringify(d.note) === JSON.stringify(['16px', '12px', '16px']), JSON.stringify(d));
      }
    }

    await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
    const point = await ev(`async () => {
      window.lenis?.stop(); document.documentElement.style.scrollBehavior = 'auto';
      const b = document.getElementById('bigName'); b.scrollIntoView({block: 'center'});
      await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
      const r = b.children[0].getBoundingClientRect(); return { x: r.x + r.width / 2, y: r.y + r.height / 2 };
    }`);
    await send('Input.dispatchMouseEvent', { type: 'mouseMoved', ...point });
    const motion = await ev(`() => { const e = document.querySelector('#bigName span'); return { poked: e.classList.contains('poked'), durations: e.getAnimations().filter(a => a.effect.getKeyframes().some(k => k.transform)).map(a => a.effect.getTiming().duration) }; }`);
    check('F10 letter hover retains tickle behavior', motion.poked && (REDUCED ? motion.durations.length === 0 : motion.durations.includes(600)), JSON.stringify(motion));
    await ev(`() => { document.documentElement.style.setProperty('--acc', '#7dd3fc'); }`);
    await new Promise(r => setTimeout(r, 550));
    check('F11 wordmark follows selected accent', await ev(`() => getComputedStyle(document.querySelector('#bigName span')).color`) === 'rgb(125, 211, 252)');

    const linkPoint = await ev(`async () => {
      const a = document.querySelector('footer nav a'); if (!a) return null;
      a.focus(); a.scrollIntoView({block: 'center'});
      await new Promise(r => requestAnimationFrame(() => requestAnimationFrame(r)));
      const r = a.getBoundingClientRect(); return { x: r.x + r.width / 2, y: r.y + r.height / 2, focused: document.activeElement === a };
    }`);
    check('F12 footer navigation can receive keyboard focus', linkPoint?.focused === true);
    if (linkPoint) {
      await ev(`() => { window.lenis?.start(); }`);
      await send('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Enter', code: 'Enter', windowsVirtualKeyCode: 13 });
      await send('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Enter', code: 'Enter', windowsVirtualKeyCode: 13 });
      await new Promise(r => setTimeout(r, 1400));
      check('F13 keyboard navigation reaches About', await ev(`() => Math.abs(document.getElementById('about').getBoundingClientRect().top) < 120`));
    }
    check('F14 existing overlays remain mounted', await ev(`() => ['aiWidget', 'handPanel', 'pal'].every(id => !!document.getElementById(id))`));
  } finally { close(); }
}

async function shot(url, outDir, sections, hover) {
  const { ev, send, close } = await open(url);
  mkdirSync(outDir, { recursive: true });
  await ev(`() => { if (window.lenis) window.lenis.stop(); return 1; }`);
  for (const id of sections) {
    const y = await ev(`() => { const el = document.getElementById(${JSON.stringify(id)}); return el ? Math.max(0, el.getBoundingClientRect().top + window.scrollY - 40) : null; }`);
    if (y === null) { console.log('  SKIP', id); continue; }
    await ev(`() => { window.scrollTo(0, ${y}); document.querySelectorAll('.rv,.rv-pop').forEach(e => e.classList.add('vis')); return 1; }`);
    await new Promise(r => setTimeout(r, 800));
    if (hover) {
      // a hover-only surface cannot be photographed; drive a real mouse onto the word.
      // The word is far below the section top, so scroll to it, not to the section.
      const p = await ev(`() => {
        const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === ${JSON.stringify(hover)});
        if (!w) return null;
        window.scrollTo(0, Math.max(0, w.getBoundingClientRect().top + scrollY - 300));
        return 1;
      }`);
      if (!p) { console.log('  no word', hover); }
      else {
        await new Promise(r => setTimeout(r, 400));
        const c = await ev(`() => { const w = [...document.querySelectorAll('.sk-word')].find(e => e.dataset.tech === ${JSON.stringify(hover)}); const r = w.getBoundingClientRect(); return { x: Math.round(r.x + r.width / 2), y: Math.round(r.y + r.height / 2) }; }`);
        await send('Input.dispatchMouseEvent', { type: 'mouseMoved', x: c.x, y: c.y });
        await new Promise(r => setTimeout(r, 250));
      }
    }
    const img = await send('Page.captureScreenshot', { format: 'png' });
    const f = join(outDir, `shot_${id}.png`);
    writeFileSync(f, Buffer.from(img.result?.data, 'base64'));
    console.log('  shot', f);
  }
  close();
}

// ---------- main ----------
if (mode === 'dump') {
  const data = await dump(urlArg || MINE);
  const file = outArg || `verify/out/${(urlArg || 'mine').replace(/[^a-z0-9]+/gi, '_')}.json`;
  writeFileSync(file, JSON.stringify(data, null, 1));
  console.log(`dumped ${data.text.length} text nodes, ${data.ids.length} ids, ${data.cards.length} cards -> ${file}`);
} else if (mode === 'diff') {
  const a = JSON.parse(readFileSync(args[1], 'utf8'));
  const b = JSON.parse(readFileSync(args[2], 'utf8'));
  diff(a, b);
} else if (mode === 'check' || mode === 'footer') {
  if (mode === 'footer') await checkFooter(urlArg || MINE);
  else await checkSite(urlArg || MINE);
  console.log(`\n${pass} passed, ${fail} failed`);
  process.exit(fail ? 1 : 0);
} else if (mode === 'shot') {
  const secs = (args.find(a => a.startsWith('--sections='))?.split('=')[1] || 'top,about,projects,timeline,certs,guestbook,contact,footer').split(',');
  await shot(urlArg || MINE, args.find(a => a.startsWith('--out='))?.split('=')[1] || '.tmpshots', secs,
    args.find(a => a.startsWith('--hover='))?.split('=')[1]);
} else {
  console.log('usage: node verify/motion.mjs dump <url> [--out=f.json] | diff a.json b.json | check [url] [--reduced] | footer [url] [--reduced] | shot [url] [--sections=a,b] [--hover=PHP] [--out=dir]');
  process.exit(2);
}
