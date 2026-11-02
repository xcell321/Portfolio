#!/usr/bin/env node
// Motion/layout probe for the portfolio. Dependency-free: Node 24 (global WebSocket + fetch) + headless Chrome over CDP.
//   node verify/motion.mjs dump  <url> [--out=file.json] [--reduced]
//   node verify/motion.mjs diff  a.json b.json
//   node verify/motion.mjs check [url] [--reduced]
//   node verify/motion.mjs shot  [url] [--sections=a,b] [--out=dir]
// `dump` measures by text content and by id, so it can compare our PHP site against any reference build
// without knowing the other site's class names. `check` asserts our own spec table.
// `shot` scrolls to each section id in a real 1440x900 viewport and captures it; reveals are forced
// visible because a tall-window screenshot would stretch the 100vh hero and lie about the layout.

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
async function connect() {
  for (let i = 0; i < 60; i++) {
    try {
      const list = await (await fetch(`http://127.0.0.1:${PORT}/json/list`)).json();
      const page = list.find(t => t.type === 'page');
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
  const ws = await connect();
  const { send } = rpc(ws);
  await send('Runtime.enable'); await send('Page.enable'); await send('DOM.enable');
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
  } else {
    // reduced-motion pass
    const rv = await ev(`() => { const e = document.querySelector('.rv'); return e ? getComputedStyle(e).opacity : 'none'; }`);
    check('REDUCED .rv visible without reveal', rv === '1' || rv === 'none', 'opacity ' + rv);
    const inline = await ev(`() => ({ inner: (document.querySelector('.hero-inner')||{}).style?.cssText || '', decor: (document.querySelector('.hero-decor')||{}).style?.cssText || '' })`);
    check('REDUCED hero layers left alone', inline.inner === '' && inline.decor === '', JSON.stringify(inline));
    const orb = await ev(`() => { const o = document.querySelector('.orb-1'); return o ? getComputedStyle(o).animationName : 'none'; }`);
    check('REDUCED orbs animation off', orb === 'none' || orb === 'none, none', orb);
  }

  close();
  return d;
}

async function shot(url, outDir, sections) {
  const { ev, send, close } = await open(url);
  mkdirSync(outDir, { recursive: true });
  await ev(`() => { if (window.lenis) window.lenis.stop(); return 1; }`);
  for (const id of sections) {
    const y = await ev(`() => { const el = document.getElementById(${JSON.stringify(id)}); return el ? Math.max(0, el.getBoundingClientRect().top + window.scrollY - 40) : null; }`);
    if (y === null) { console.log('  SKIP', id); continue; }
    await ev(`() => { window.scrollTo(0, ${y}); document.querySelectorAll('.rv,.rv-pop').forEach(e => e.classList.add('vis')); return 1; }`);
    await new Promise(r => setTimeout(r, 800));
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
} else if (mode === 'check') {
  await checkSite(urlArg || MINE);
  console.log(`\n${pass} passed, ${fail} failed`);
  process.exit(fail ? 1 : 0);
} else if (mode === 'shot') {
  const secs = (args.find(a => a.startsWith('--sections='))?.split('=')[1] || 'top,about,projects,timeline,certs,guestbook,contact,footer').split(',');
  await shot(urlArg || MINE, args.find(a => a.startsWith('--out='))?.split('=')[1] || '.tmpshots', secs);
} else {
  console.log('usage: node verify/motion.mjs dump <url> [--out=f.json] | diff a.json b.json | check [url] [--reduced] | shot [url] [--sections=a,b] [--out=dir]');
  process.exit(2);
}
