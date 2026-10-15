// ==========================================================================
// smooth scroll — mikedev.site exact Lenis config (app-layout module, de-minified):
// duration:.7, easing:t=>1-Math.pow(1-t,3) (easeOutCubic), orientation vertical,
// gestureOrientation vertical, smoothWheel:true, wheelMultiplier:1.2,
// touchMultiplier:1.5, infinite:false + rAF loop nga nag-pause sa hidden tab
// (visibilitychange) — same pause guard as Mike's u() loop
// ==========================================================================
(function(){
  if(matchMedia('(prefers-reduced-motion: reduce)').matches)return;
  if('scrollingElement' in document===false)return;
  const s=document.createElement('script');
  s.src='https://unpkg.com/lenis@1/dist/lenis.min.js';
  s.async=true;
  s.onload=function(){
    try{
      const Lenis=window.Lenis||window.lenis;
      if(!Lenis)return;
      let raf=0,hidden=false;
      const lenis=new Lenis({
        duration:.7,
        easing:function(t){return 1-Math.pow(1-t,3)},
        orientation:'vertical',
        gestureOrientation:'vertical',
        smoothWheel:true,
        wheelMultiplier:1.2,
        touchMultiplier:1.5,
        infinite:false
      });
      window.lenis=lenis;
      function loop(time){
        // Mike's exact guard: rAF keeps ticking but scrollTo/scroll aan pa sa hidden
        if(!document.hidden&&!hidden)lenis.raf(time);
        raf=requestAnimationFrame(loop);
      }
      raf=requestAnimationFrame(loop);
      document.addEventListener('visibilitychange',function(){hidden=document.hidden});
      addEventListener('pagehide',function(){cancelAnimationFrame(raf);lenis.destroy();window.lenis=null},{once:true});
    }catch(e){}
  };
  document.head.appendChild(s);
})();
// ==========================================================================
// anchor clicks → lenis.scrollTo — mikedev.site exact (module 44359 o() helper):
// 'top' → scrollTo(0,{duration:1.1}); targets → pos - 80px header offset, {duration:1.1}
// native jump fallback kung wala pa na-load ang Lenis
// ==========================================================================
document.addEventListener('click',function(e){
  const a=e.target.closest&&e.target.closest('a[href^="#"]');
  if(!a)return;
  const href=a.getAttribute('href');
  if(!href||href==='#')return;
  const target=document.getElementById(href.slice(1));
  if(!target)return;
  e.preventDefault();
  const l=window.lenis;
  if(l&&l.scrollTo){
    if(href==='#top')l.scrollTo(0,{duration:1.1});
    else l.scrollTo(target,{duration:1.1,offset:-80});
  }else{
    const y=href==='#top'?0:target.getBoundingClientRect().top+window.scrollY-80;
    window.scrollTo({top:y,behavior:'smooth'});
  }
});
// reveal on scroll — mikedev.site exact: viewport {once:true, margin:-60px};
// stagger delay gikan sa data-rvd attribute → paint as --rvd CSS var (transition-delay)
(function(){
  document.querySelectorAll('.rv[data-rvd]').forEach(el=>{el.style.setProperty('--rvd',el.dataset.rvd+'s')});
  const io=new IntersectionObserver(es=>{es.forEach(e=>{if(e.isIntersecting){e.target.classList.add('vis');io.unobserve(e.target)}})},{rootMargin:'-60px 0px -60px 0px'});
  document.querySelectorAll('.rv').forEach(el=>io.observe(el));
})();
// floating pill nav on scroll (mikedev.site style — transparent sa top, pill pag scroll)
addEventListener('scroll',()=>{
  // mikedev.site exact: nav starts fully transparent, becomes the pill only after scrolling >24px
  const sc=scrollY>24;
  const nav=document.querySelector('.nav-in');
  if(nav&&!nav.classList.contains('force-pill'))nav.classList.toggle('scrolled',sc);
  const hdr=document.querySelector('.site-header');
  if(hdr) hdr.classList.toggle('scrolled',scrollY>40);
  document.querySelector('.nav')?.classList.toggle('scrolled',scrollY>40);
  document.getElementById('toTop')?.classList.toggle('show',scrollY>600);
},{passive:true});
// apply scroll state on load too — mid-page reloads skip the scroll event, so the pill
// must render immediately instead of waiting for the first wheel move
(function(){const y=window.scrollY||window.pageYOffset||0;document.querySelector('.nav-in')?.classList.toggle('scrolled',y>24)})();
const ACCENTS=[['Lime','#d4fd73'],['Mint','#6ee7b7'],['Sky','#7dd3fc'],['Violet','#c4b5fd'],['Pink','#f9a8d4'],['Peach','#fdba74'],['Sun','#fde047']];
function paintSwatches(){
  const box=document.getElementById('swatches');if(!box||box.children.length)return;
  let cur=(localStorage.getItem('acc')||'#d4fd73').toLowerCase();
  if(!ACCENTS.some(a=>a[1].toLowerCase()===cur)){cur='#d4fd73';try{localStorage.setItem('acc',cur)}catch(e){}}
  ACCENTS.forEach(([n,h])=>{
    const b=document.createElement('button');
    b.className='sw'+(cur.toLowerCase()===h?' on':'');b.style.background=h;b.title=n;
    b.innerHTML=cur.toLowerCase()===h?'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#141205" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>':'';
    b.onclick=e=>{e.stopPropagation();setAccent(n,h)};
    box.appendChild(b);
  });
  const saved=ACCENTS.find(a=>a[1].toLowerCase()===cur);
  if(saved){document.getElementById('themeName').textContent=saved[0]}
}
function setAccent(n,h){
  h=(h||'#d4fd73').toLowerCase();
  document.documentElement.style.setProperty('--acc',h);
  try{localStorage.setItem('acc',h)}catch(e){}
  document.dispatchEvent(new CustomEvent('accentchange',{detail:h}));
  document.getElementById('themeName').textContent=n;
  paintSwatchesRefresh(h);
}
function paintSwatchesRefresh(h){
  h=(h||'').toLowerCase();
  document.querySelectorAll('#swatches .sw').forEach(s=>{
    const sh=s.style.background,match=sh.toLowerCase()===h||rgb2hex(sh).toLowerCase()===h;
    s.classList.toggle('on',match);
    s.innerHTML=match?'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#141205" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12.5l5 5L20 6.5"/></svg>':'';
  });
}
function rgb2hex(v){const m=v.match(/\d+/g);if(!m||m.length<3)return v;return'#'+m.slice(0,3).map(x=>(+x).toString(16).padStart(2,'0')).join('')}
function toggleTheme(e){if(e)e.stopPropagation();paintSwatches();document.getElementById('themePop').classList.toggle('open')}
document.addEventListener('click',e=>{const p=document.getElementById('themePop');if(p&&p.classList.contains('open')&&!e.target.closest('.theme-wrap'))p.classList.remove('open')});
// init sa load: kung unsay naka-select nga color, mao ra gyud nay mogawas inig refresh —
// validate + apply + update sa picker label dayon (dili na maghuwat ug open sa picker)
(function(){
  try{
    let h=(localStorage.getItem('acc')||'#d4fd73').toLowerCase();
    if(!ACCENTS.some(a=>a[1].toLowerCase()===h)){h='#d4fd73';try{localStorage.setItem('acc',h)}catch(e){}}
    document.documentElement.style.setProperty('--acc',h);
    const found=ACCENTS.find(a=>a[1].toLowerCase()===h);
    if(found){const tn=document.getElementById('themeName');if(tn)tn.textContent=found[0]}
  }catch(e){}
})();
// nav mobile — mikedev exact: pill mu-show pud inig open sa menu (e||a)
function toggleNav(){
  const l=document.getElementById('navLinks');if(!l)return;
  l.classList.toggle('open');
  const nav=document.querySelector('.nav-in');if(!nav)return;
  if(l.classList.contains('open')){nav.classList.add('scrolled');nav.classList.add('force-pill')}
  else{nav.classList.remove('force-pill');nav.classList.toggle('scrolled',(window.scrollY||0)>24)}
}
// ---- active-section nav pill — mikedev.site exact ----
// IO rootMargin "-45% 0px -50% 0px" (center band of viewport) over #top + every nav target;
// sliding pill bg-white/[.07] behind the active link (mikedev uses a framer spring:
// stiffness 380 / damping 34 — approximated here with the same ease curve on left/width)
(function(){
  const list=document.querySelector('.links');
  if(!list)return;
  const links=[].slice.call(list.querySelectorAll('a[href^="#"]'));
  if(!links.length)return;
  // sliding pill element (behind link text, same as mikedev's layoutId span)
  const pill=document.createElement('span');
  pill.className='nav-pill';
  list.appendChild(pill);
  // map href -> link el, and section ids from href (About->about, Work->projects, ...)
  const byId={};
  links.forEach(a=>{byId[a.getAttribute('href').slice(1)]=a});
  const setId=Object.keys(byId);
  let active=null;
  function place(a){
    active=a;
    const r=a.getBoundingClientRect(),lr=list.getBoundingClientRect();
    pill.style.left=(r.left-lr.left)+'px';
    pill.style.width=r.width+'px';
    pill.classList.add('show');
  }
  function clear(){
    active=null;
    links.forEach(a=>a.classList.remove('on'));
    pill.classList.remove('show');
  }
  function paint(){
    // mikedev exact: "top" is observed too — active top = no link matches = pill hidden
    if(!active||active.getAttribute('href')==='#top'){clear();return}
    links.forEach(a=>a.classList.toggle('on',a===active));
    place(active);
  }
  const io=new IntersectionObserver(es=>{
    es.forEach(e=>{if(e.isIntersecting)active=byId[e.target.id]||null});
    paint();
  },{rootMargin:'-45% 0px -50% 0px'});              // exact mikedev band
  // top hero + every section — same list as mikedev (["top", ...sections])
  ['top'].concat(setId).forEach(id=>{const el=document.getElementById(id);if(el)io.observe(el)});
  // reposition on resize (pill is pixel-positioned); also re-place after fonts/layout settle
  addEventListener('resize',()=>{if(active)place(active)},{passive:true});
  addEventListener('load',()=>{if(active)place(active)});
  paint();
})();
// accordion
document.querySelectorAll('.acc-head').forEach(b=>b.addEventListener('click',()=>{
  const p=b.parentElement,was=p.classList.contains('open');
  document.querySelectorAll('.acc').forEach(a=>a.classList.remove('open'));
  if(!was)p.classList.add('open');
}));
// chips select
document.querySelectorAll('[data-chips]').forEach(box=>{
  box.addEventListener('click',e=>{
    if(e.target.classList.contains('chip')){e.target.classList.toggle('on');syncChips(box)}
  });
});
function syncChips(box){
  const v=[...box.querySelectorAll('.chip.on')].map(c=>c.textContent).join(', ');
  document.getElementById(box.dataset.chips).value=v;
}
// drag toolkit
let dragEl=null;
document.querySelectorAll('.tool').forEach(t=>{
  t.draggable=true;
  t.addEventListener('dragstart',()=>dragEl=t);
  t.addEventListener('dragover',e=>e.preventDefault());
  t.addEventListener('drop',e=>{e.preventDefault();if(dragEl&&dragEl!==t){const tk=document.getElementById('toolkit');const r=[...tk.children];const i=r.indexOf(dragEl),j=r.indexOf(t);i<j?tk.insertBefore(dragEl,t.nextSibling):tk.insertBefore(dragEl,t)}});
});
function tidyUp(){const tk=document.getElementById('toolkit');[...tk.children].sort((a,b)=>a.textContent.localeCompare(b.textContent)).forEach(n=>tk.appendChild(n));toast('Tidy!')}
// tilt on project cards
document.querySelectorAll('.proj').forEach(c=>{
  c.addEventListener('mousemove',e=>{const r=c.getBoundingClientRect();const x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;c.style.transform=`perspective(900px) rotateX(${-y*5}deg) rotateY(${x*6}deg) translateY(-4px)`});
  c.addEventListener('mouseleave',()=>c.style.transform='');
});
// command palette — mikedev.site UI port (clean-room, UI only):
// Ctrl+K toggle, "/" open, Esc close, ↑↓ navigate, Enter select
let palIdx=0;
function palOpts(){return [...document.querySelectorAll('#palList a')].filter(a=>a.style.display!=='none');}
function palPaint(){
  const items=palOpts();
  if(palIdx>=items.length)palIdx=Math.max(0,items.length-1);
  items.forEach((a,i)=>a.classList.toggle('act',i===palIdx));
  const act=items[palIdx];
  if(act)act.scrollIntoView({block:'nearest'});
}
function palLock(on){try{if(on){if(window.lenis&&window.lenis.stop)window.lenis.stop();document.body.style.overflow='hidden';}else{if(window.lenis&&window.lenis.start)window.lenis.start();document.body.style.overflow='';}}catch(e){}}
function palOpen(){const p=document.getElementById('pal');p.classList.remove('closing');p.classList.add('open');palLock(true);const i=document.getElementById('palInput');i.value='';palFilter('');i.focus();requestAnimationFrame(()=>i.focus());setTimeout(()=>{if(document.getElementById('pal').classList.contains('open'))i.focus();},60);}
function palClose(){const p=document.getElementById('pal');if(!p.classList.contains('open')||p.classList.contains('closing'))return;p.classList.add('closing');palLock(false);setTimeout(()=>{p.classList.remove('open','closing');},150);}
function palPick(){
  const a=palOpts()[palIdx];if(!a)return;
  palClose();setTimeout(()=>a.click(),60);
}
function palKey(e){
  if(e.key==='Escape'){e.preventDefault();palClose();}
  else if(e.key==='ArrowDown'){e.preventDefault();const n=palOpts().length;palIdx=n?(palIdx+1)%n:0;palPaint();}
  else if(e.key==='ArrowUp'){e.preventDefault();const n=palOpts().length;palIdx=n?(palIdx-1+n)%n:0;palPaint();}
  else if(e.key==='Enter'){e.preventDefault();palPick();}
}
addEventListener('keydown',e=>{
  const typing=e.target.matches&&e.target.matches('input,textarea,[contenteditable]');
  if((e.ctrlKey||e.metaKey)&&e.key.toLowerCase()==='k'){e.preventDefault();document.getElementById('pal').classList.contains('open')?palClose():palOpen();}
  else if(e.key==='/'&&!typing&&!document.getElementById('pal').classList.contains('open')){e.preventDefault();palOpen();}
});
// like - mikedev.site exact port (chunk 993-…): heart spring 500/15 (keyed remount),
// count roll y ±100% .3s, canvas confetti count 40 gikan sa button center inig like,
// liked = border accent/40 + bg accent/10 + text accent, busy = opacity .6
function paintLiked(liked){
  const b=document.getElementById('likePillTop');
  if(b){b.classList.toggle('liked',liked);b.setAttribute('aria-pressed',liked?'true':'false');b.setAttribute('aria-label',(liked?'Unlike':'Like')+' this portfolio')}
  const lt=document.getElementById('likeLabelTop');if(lt)lt.textContent=liked?'You liked this':'Like this portfolio';
  try{liked?localStorage.setItem('liked','1'):localStorage.removeItem('liked')}catch(e){}
}
function paintTotal(total){
  const n=document.getElementById('likeNumTop');if(n)n.textContent=total;
}
// count roll — daan mo-slide up-out, bag-o mo-slide up-in (.3s, tabular-nums)
function animTotal(to){
  const wrap=document.getElementById('likeNumWrap');if(!wrap)return;
  const cur=wrap.querySelector('span');if(!cur)return;
  const from=parseInt(cur.textContent||'0',10)||0;
  if(from===to){cur.textContent=to;return}
  if(matchMedia('(prefers-reduced-motion: reduce)').matches){cur.textContent=to;return}
  const nxt=document.createElement('span');nxt.id='likeNumTop';nxt.textContent=to;nxt.className='roll-new';
  cur.removeAttribute('id');cur.className='roll-old';wrap.append(nxt);
  setTimeout(()=>{cur.remove();nxt.className=''},320);
}
// heart spring retrigger — parehas sa framer keyed remount (scale .4 → 1, stiffness 500 damping 15)
function heartSpring(){
  const b=document.getElementById('likePillTop');if(!b)return;
  b.classList.remove('pop');void b.offsetWidth;b.classList.add('pop');
  setTimeout(()=>b.classList.remove('pop'),550);
}
// canvas confetti gikan sa button center — mikedev engine exact (speed 4+9r, gravity .28, count param)
function mikeConfetti(x,y,count){
  if(matchMedia('(prefers-reduced-motion: reduce)').matches)return;
  let hex=(getComputedStyle(document.documentElement).getPropertyValue('--acc').trim()||'#d4fd73').replace('#','');
  if(hex.length===3)hex=hex.split('').map(c=>c+c).join('');
  const base=[parseInt(hex.slice(0,2),16),parseInt(hex.slice(2,4),16),parseInt(hex.slice(4,6),16)];
  const mix=(k,to)=>'rgb('+base.map(v=>Math.round(v+(to-v)*k)).join(',')+')';
  const pal=['rgb('+base.join(',')+')',mix(.2,0),mix(.4,255),'#f5f5f4','#a8a29e'];
  const cv=document.createElement('canvas');
  cv.setAttribute('aria-hidden','true');
  Object.assign(cv.style,{position:'fixed',inset:'0',width:'100vw',height:'100vh',pointerEvents:'none',zIndex:'10000'});
  document.body.appendChild(cv);
  const ctx=cv.getContext('2d');
  if(!ctx){cv.remove();return}
  const dpr=Math.min(window.devicePixelRatio||1,2),W=window.innerWidth,H=window.innerHeight;
  cv.width=W*dpr;cv.height=H*dpr;ctx.scale(dpr,dpr);
  const n=count||40;
  const parts=Array.from({length:n},()=>{
    const ang=Math.random()*Math.PI*2,sp=4+9*Math.random();
    return{x,y,vx:Math.cos(ang)*sp,vy:Math.sin(ang)*sp-6,size:5+6*Math.random(),rot:Math.random()*Math.PI,vr:(Math.random()-.5)*.4,color:pal[Math.random()*pal.length|0],round:Math.random()<.3};
  });
  let raf=0;
  const g=()=>{
    ctx.clearRect(0,0,W,H);
    let alive=0;
    for(const p of parts){
      p.vy+=.28;p.vx*=.985;p.x+=p.vx;p.y+=p.vy;p.rot+=p.vr;
      if(p.y<H+20)alive++;
      ctx.save();ctx.translate(p.x,p.y);ctx.rotate(p.rot);ctx.fillStyle=p.color;
      if(p.round){ctx.beginPath();ctx.arc(0,0,p.size/2.4,0,2*Math.PI);ctx.fill()}
      else ctx.fillRect(-p.size/2,-p.size/4,p.size,p.size/2);
      ctx.restore();
    }
    if(alive)raf=requestAnimationFrame(g);else cv.remove();
  };
  raf=requestAnimationFrame(g);
  setTimeout(()=>{cancelAnimationFrame(raf);cv.remove()},6000);
}
let liking=false;
async function like(ev){
  if(liking)return;liking=true;
  const btn=document.getElementById('likePillTop');
  if(btn)btn.classList.add('busy');
  try{
    const r=await fetch('api_like.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'toggle'})});
    const j=await r.json();
    paintLiked(!!j.liked);
    animTotal(j.total);
    heartSpring();
    if(j.liked){sfx('pop');if(btn){const t=btn.getBoundingClientRect();mikeConfetti(t.left+t.width/2,t.top+t.height/2,40)}}
  }catch{
    toast('Dili ma-like karon, try balik unya.');
  }finally{liking=false;if(btn)btn.classList.remove('busy')}
}
// sync liked state on load + mount spring sa heart (.4 → 1) parehas ni Mike
(async()=>{try{const r=await fetch('api_like.php');const j=await r.json();paintTotal(j.total);paintLiked(!!j.liked);heartSpring()}catch{try{paintLiked(localStorage.getItem('liked')==='1')}catch(e){}heartSpring()}})();
// duty flip — AUTOMATIC sa hover ra (mikeddev exact): walay click, badge kinda flips text lang
function setDuty(on){
  const badge=document.getElementById('dutyBadge'),card=document.getElementById('photoCard');
  if(!badge||!card)return;
  badge.textContent=on?'ON DUTY':'OFF DUTY';
  card.classList.toggle('off',!on);
}
(function(){
  const c=document.getElementById('clock');
  function tick(){try{c.textContent=new Date().toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit',timeZone:'Asia/Manila'})}catch(e){}}
  tick();setInterval(tick,30000);
  const pc=document.getElementById('photoCard');
  // mikedev exact: 3D tilt sa pointer (±14° spring 150/18) + duty flip on hover/unhover
  // framer useSpring approximation: current value chases target via lerp .12/frame (≈ stiffness150/damping18 feel)
  if(pc){
    const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
    let tx=0,ty=0,cx=0,cy=0,gx=50,gy=50,gcx=50,gcy=50,raf=0,active=false;
    function frame(){
      cx+=(tx-cx)*.12;cy+=(ty-cy)*.12;gcx+=(gx-gcx)*.25;gcy+=(gy-gcy)*.25;
      pc.style.transform='rotate(3deg) perspective(1000px) rotateX('+cy.toFixed(3)+'deg) rotateY('+cx.toFixed(3)+'deg)';
      const g=document.getElementById('photoGlow');
      if(g)g.style.background='radial-gradient(60% 50% at '+gcx.toFixed(1)+'% '+gcy.toFixed(1)+'%, rgba(255,255,255,0.18), transparent 70%)';
      if(active||Math.abs(tx-cx)>.01||Math.abs(ty-cy)>.01)raf=requestAnimationFrame(frame);else raf=0;
    }
    pc.addEventListener('pointermove',e=>{
      if(reduced||e.pointerType!=='mouse')return;
      const r=pc.getBoundingClientRect();
      const nx=(e.clientX-r.left)/r.width,ny=(e.clientY-r.top)/r.height;
      tx=(nx-.5)*14;ty=(.5-ny)*14;gx=nx*100;gy=ny*100;
      active=true;if(!raf)raf=requestAnimationFrame(frame);
    });
    pc.addEventListener('mouseenter',()=>{setDuty(false)});
    pc.addEventListener('mouseleave',()=>{tx=0;ty=0;active=false;if(!raf)raf=requestAnimationFrame(frame);setDuty(true)});
  }
})();
// ---- hero scroll-fade — mikedev.site exact (component z):
// progress = scrollY / hero height (framer offset ["start start","end start"]);
// opacity 1→0 at [.7] of exit, y 0→−120px at [1]; desktop-only; reduced-motion = static.
// rAF-throttled, sa hero-inner content ra gi-apply (bg canvas/orbs stay put).
(function(){
  if(matchMedia('(prefers-reduced-motion: reduce)').matches)return;
  const hero=document.getElementById('top'),inner=hero&&hero.querySelector('.hero-inner');
  if(!hero||!inner)return;
  // mike's matchMedia(min-width:1024px): mobile keeps static opacity 1 — same guard
  const mq=matchMedia('(min-width: 1024px)');
  let active=mq.matches,ticking=false;
  function apply(){
    ticking=false;
    if(!active){inner.style.opacity='';inner.style.transform='';return}
    const h=hero.offsetHeight||inner.offsetHeight;
    if(h<=0)return;
    const p=Math.min(1,Math.max(0,(window.scrollY||window.pageYOffset||0)/h));   // progress 0..1 ngadto sa hero end
    // framer useTransform pairs: opacity [0,.7]→[1,0] (linear), y [0,1]→[0,-120]
    const op=Math.max(0,1-p/.7);
    const y=(0-p)*120;
    inner.style.opacity=op.toFixed(4);
    inner.style.transform='translate3d(0,'+y.toFixed(2)+'px,0)';
  }
  function onScroll(){if(!ticking){ticking=true;requestAnimationFrame(apply)}}
  addEventListener('scroll',onScroll,{passive:true});
  mq.addEventListener&&mq.addEventListener('change',e=>{active=e.matches;onScroll()});
  apply();
})();
// back to top — lenis-aware: duration 1.1 same sa anchor helper (mikedev exact)
function toTop(){const l=window.lenis;if(l&&l.scrollTo){l.scrollTo(0,{duration:1.1})}else{scrollTo({top:0,behavior:'smooth'})}}
function toast(m,action){const t=document.getElementById('toast');if(!t)return;t.innerHTML='';t.append(document.createTextNode(m));if(action&&action.label){const b=document.createElement('button');b.type='button';b.className='toast-act';b.textContent=action.label;b.onclick=()=>{t.classList.remove('show');if(action.fn)action.fn()};t.append(b)}t.classList.add('show');clearTimeout(t._h);t._h=setTimeout(()=>t.classList.remove('show'),2600)}
// ---- sound effects toggle — mikedev.site behavior port (clean-room):
// single speaker button, default OFF (volume-x), click = ON (volume) / OFF.
// Walay external mp3 — WebAudio blip lang para sa UI clicks, saved sa localStorage.
let soundOn=false;
try{soundOn=localStorage.getItem('snd')==='1'}catch(e){}
let _ac=null,_lastTap=0;
function _ctx(){
  try{
    const AC=window.AudioContext||window.webkitAudioContext;
    if(!AC)return null;
    if(!_ac)_ac=new AC();
    if(_ac.state==='suspended')_ac.resume();
    return _ac;
  }catch(e){return null}
}
// usa ka oscillator + gain envelope: hinay nga sulod (8ms), glide ngadto sa `to`, pagawas sa `dur`
function tone(o){
  const ctx=_ctx();if(!ctx)return;
  const t0=ctx.currentTime+(o.start||0);
  const osc=ctx.createOscillator(),g=ctx.createGain();
  osc.type=o.type||'sine';
  osc.frequency.setValueAtTime(o.freq,t0);
  if(o.to)osc.frequency.exponentialRampToValueAtTime(o.to,t0+o.dur);
  g.gain.setValueAtTime(0.0001,t0);
  g.gain.exponentialRampToValueAtTime(o.vol||0.05,t0+0.008);
  g.gain.exponentialRampToValueAtTime(0.0001,t0+o.dur);
  osc.connect(g);g.connect(ctx.destination);
  osc.start(t0);osc.stop(t0+o.dur+0.02);
}
// parehas pamati kang Mike: tap sa buttons, pop inig like, two-note jingle inig on/off
function sfx(name){
  if(name!=='on'&&name!=='off'&&!soundOn)return;
  try{
    if(name==='tap'||name==='click'){
      const now=performance.now();
      if(now-_lastTap<45)return;
      _lastTap=now;
    }
    if(name==='tap')tone({freq:1100,to:700,dur:.05,vol:.035});
    else if(name==='click')tone({freq:520,to:340,dur:.04,type:'triangle',vol:.03});
    else if(name==='pop'){tone({freq:260,to:900,dur:.12,type:'triangle',vol:.07});[.06,.11,.17].forEach(function(s,i){tone({freq:1500+350*i+200*Math.random(),start:s,dur:.09,vol:.025})});}
    else if(name==='chime'){tone({freq:1318.5,dur:.5,vol:.05});tone({freq:1975.5,start:.09,dur:.6,vol:.04});}
    else if(name==='on'){tone({freq:660,dur:.08,vol:.05});tone({freq:990,start:.07,dur:.12,vol:.05});}
    else if(name==='off'){tone({freq:990,dur:.08,vol:.04});tone({freq:560,start:.07,dur:.12,vol:.04});}
  }catch(e){}
}
const _SVG_MUTE='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4.7a.7.7 0 0 0-1.2-.5L6.4 7.6A1.4 1.4 0 0 1 5.4 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.4a1.4 1.4 0 0 1 1 .4l3.4 3.4a.7.7 0 0 0 1.2-.5z"/><path d="M22 9l-6 6M16 9l6 6"/></svg>';
const _SVG_VOL='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4.7a.7.7 0 0 0-1.2-.5L6.4 7.6A1.4 1.4 0 0 1 5.4 8H3a1 1 0 0 0-1 1v6a1 1 0 0 0 1 1h2.4a1.4 1.4 0 0 1 1 .4l3.4 3.4a.7.7 0 0 0 1.2-.5z"/><path d="M16 9a5 5 0 0 1 0 6"/><path d="M19.4 18.4a9 9 0 0 0 0-12.7"/></svg>';
function paintSound(){
  const b=document.getElementById('soundBtn');if(!b)return;
  b.classList.toggle('on',soundOn);
  b.setAttribute('aria-pressed',soundOn?'true':'false');
  b.setAttribute('title',soundOn?'Sound on':'Sound off');
  b.setAttribute('aria-label',soundOn?'Turn sound effects off':'Turn sound effects on');
  b.innerHTML=soundOn?_SVG_VOL:_SVG_MUTE;
}
function toggleSound(ev){
  if(ev)ev.stopPropagation();
  soundOn=!soundOn;
  try{soundOn?localStorage.setItem('snd','1'):localStorage.removeItem('snd')}catch(e){}
  paintSound();
  sfx(soundOn?'on':'off');
}
paintSound();
// painit ang AudioContext sa unang gesture para dili ma-block sa browser ang mga async sounds (e.g. pop inig like)
(function(){
  let warmed=false;
  function warm(){if(warmed)return;warmed=true;_ctx();}
  addEventListener('pointerdown',warm,{once:true,passive:true});
  addEventListener('keydown',warm,{once:true});
})();
// soft tap kada pindot kung naka-ON — parehas kang Mike:
// ang naay data-sound="none" (sound toggle, like) excluded kay naa silay kaugalingong pop/on/off.
// pointerdown (capture) para madungog bisan mo-abli ug bag-ong tab (GitHub/FB/LinkedIn);
// click fallback para sa keyboard (Enter) nga walay pointerdown.
function tapEl(e){
  if(!e.target||!e.target.closest)return null;
  if(e.target.closest('[data-sound="none"]'))return 'none';
  const n=e.target.closest('button, a[href], [role="button"], [role="radio"], input[type="checkbox"], label, .chip');
  if(n&&n.disabled)return 'none';
  return n?'tap':'click';
}
// mikedev exact: left-button pointerdown, interactive → tap, background → click
document.addEventListener('pointerdown',function(e){
  if(!soundOn||e.button!==0)return;
  const s=tapEl(e);
  if(s&&s!=='none')sfx(s);
},true);
document.addEventListener('click',function(e){
  if(!soundOn||e.detail!==0)return;
  const s=tapEl(e);
  if(s&&s!=='none')sfx(s);
});
// ---- command palette filter — group-aware (tago ang group kung walay match) ----
function palFilter(q){
  q=(q||'').toLowerCase();
  document.querySelectorAll('#palList a').forEach(a=>{
    const t=(a.textContent+' '+(a.dataset.t||'')).toLowerCase();
    a.style.display=!q||t.includes(q)?'':'none';
  });
  document.querySelectorAll('#palList .pal-group').forEach(g=>{
    let el=g.nextElementSibling,vis=false;
    while(el&&!el.classList.contains('pal-group')){
      if(el.tagName==='A'&&el.style.display!=='none')vis=true;
      el=el.nextElementSibling;
    }
    g.style.display=vis?'':'none';
  });
  palIdx=0;palPaint();
}
(function(){
  // rotating words: Scale. -> Inspire. -> Evolve. -> Scale…, period 4000ms,
  // exit 200ms (y 0 -> -0.3em, opacity -> 0, blur -> 6px), gap ~70ms blank, enter 300ms (y +0.3em -> 0)
  const words=['Scale.','Inspire.','Evolve.'];
  const el=document.getElementById('rotWord');
  const sr=document.getElementById('rotSr');
  if(!el)return;
  let i=words.indexOf((el.textContent||'').trim());
  if(i<0)i=0;
  setInterval(()=>{
    i=(i+1)%words.length;
    el.classList.add('rot-out');
    setTimeout(()=>{
      el.textContent=words[i];
      if(sr)sr.textContent=words[i];
      el.classList.remove('rot-out');
      el.classList.add('rot-in');
      setTimeout(()=>el.classList.remove('rot-in'),320);
    },200);
  },4000);
})();
// ---- hero dot grid — mikedev.site EXACT port (de-minified module 67979, function v) ----
// spacing 28 (1.6x sa low-end devices), repel radius 180, base dot = 1.2px rect @ rgba(255,255,255,.11),
// lit dots: alpha .12+.8*e, radius .7+1.9*e, e=(1-d/180)^2, push 14*e along the cursor->dot vector,
// cursor lerp .1/frame, idle drift tx=m*(.5+.32*sin(.35t)) ty=p*(.45+.25*sin(.5t+1.2)), 4s idle re-arm,
// accent re-read sa motion token change, rAF throttle 33ms sa low-end, full cleanup handlers
(function(){
  const cv=document.getElementById('heroDots'), hero=document.getElementById('top');
  if(!cv||!hero||!cv.getContext)return;
  const ctx=cv.getContext('2d');
  const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;
  const SPACING=28, RADIUS=180;                          // mikedev defaults: spacing=28, radius=180
  // low-end heuristic (mikedev uses its own detector; spacing 1.6x + 33ms throttle + dpr 1)
  const lowEnd=(navigator.hardwareConcurrency||4)<=2||/Android 4|iPhone OS 1[0-2]_/.test(navigator.userAgent);
  const spacing=lowEnd?Math.round(1.6*SPACING):SPACING;   // same low-end 1.6x rule as mikedev
  let W=0,H=0,dpr=1,running=false,visible=true,lastT=0,accRgb='212,253,115';
  // cursor + idle float state — same lerp/inertia constants as mikedev
  const cur={x:0,y:0,tx:0,ty:0};
  let hasMouse=false,idleUntil=0,startT=performance.now();
  function readAcc(){
    try{
      let hex=(getComputedStyle(document.documentElement).getPropertyValue('--acc').trim()||'#d4fd73').replace('#','');
      if(hex.length===3)hex=hex.split('').map(c=>c+c).join('');
      const n=parseInt(hex.slice(0,6),16);
      accRgb=(n>>16&255)+','+(n>>8&255)+','+(n&255);
    }catch(e){}
  }
  function size(){
    const r=hero.getBoundingClientRect();
    dpr=lowEnd?1:Math.min(2,devicePixelRatio||1);         // low-end dpr 1, else capped 2 — same as mikedev
    W=r.width;H=r.height;
    cv.width=Math.round(W*dpr);cv.height=Math.round(H*dpr);
    ctx.setTransform(dpr,0,0,dpr,0,0);
    // start float point at 62%/42% of the hero — same seed point as mikedev
    if(!cur.x){cur.x=cur.tx=.62*W;cur.y=cur.ty=.42*H}
  }
  function draw(now){
    // idle sa 4s: gentle sine float — SAME formulas: tx=.5+.32sin(.35t), ty=.45+.25sin(.5t+1.2)
    if(!hasMouse||now-idleUntil>4000){
      const t=(now-startT)/1000;
      cur.tx=W*(.5+.32*Math.sin(.35*t));
      cur.ty=H*(.45+.25*Math.sin(.5*t+1.2));
    }
    cur.x+=(cur.tx-cur.x)*.1;                             // frame lerp 0.1, exactly like mikedev
    cur.y+=(cur.ty-cur.y)*.1;
    ctx.clearRect(0,0,W,H);
    // uniform centered lattice — mikedev's exact centering math
    const cols=Math.ceil(W/spacing)+1, rows=Math.ceil(H/spacing)+1;
    const ox=(W-(cols-1)*spacing)/2, oy=(H-(rows-1)*spacing)/2;

    const lit=[];                                          // lit dots batched: one path for the grey base
    for(let r=0;r<rows;r++)for(let c=0;c<cols;c++){
      const x=ox+c*spacing,y=oy+r*spacing;
      const dx=x-cur.x,dy=y-cur.y;
      if(dx>RADIUS||dx<-RADIUS||dy>RADIUS||dy<-RADIUS){
        // base lattice = crisp 1.2px rects @ alpha .11 (mikedev exact)
        ctx.rect(x-.6,y-.6,1.2,1.2);
        continue;
      }
      const d=Math.hypot(dx,dy);
      if(d<RADIUS){
        const e=(1-d/RADIUS)**2;
        // push AWAY from cursor by 14*e along the vector — mikedev's displacement rule
        const push=d>0?14*e/d:0;
        lit.push([x+dx*push,y+dy*push,e]);
      }
    }
    ctx.fillStyle='rgba(255,255,255,0.11)';ctx.fill();
    for(const[lx,ly,e]of lit){
      ctx.beginPath();
      ctx.fillStyle='rgba('+accRgb+','+(.12+.8*e).toFixed(3)+')';
      ctx.arc(lx,ly,.7+1.9*e,0,6.2832);                   // radius .7 + 1.9*e, alpha .12 + .8*e
      ctx.fill();
    }
  }
  function loop(now){
    if(!visible){running=false;return}
    if(!lowEnd||now-lastT>=33){draw(now);lastT=now}       // low-end: ~30fps cap; desktop: full rAF
    requestAnimationFrame(loop);
  }
  function kick(){
    if(running||!visible||reduced||document.hidden)return;
    running=true;requestAnimationFrame(loop);
  }
  // pointermove listener is GLOBAL sa mikedev — hero ra gihapon ang coverage sa canvas
  addEventListener('pointermove',e=>{
    if('mouse'!==e.pointerType)return;
    const r=cv.getBoundingClientRect();
    cur.tx=e.clientX-r.left;cur.ty=e.clientY-r.top;
    if(!hasMouse){cur.x=cur.tx;cur.y=cur.ty}               // first move: no fly-in
    hasMouse=true;idleUntil=performance.now();kick();
  },{passive:true});
  document.documentElement.addEventListener('pointerleave',()=>{hasMouse=false});
  // mikeddev-style live accent sync: sa event (dili setInterval polling) — instant, zero lag,
  // ang sunod nga rAF frame impor draw na dayon gamit ang bag-ong color (smooth kay per-frame render)
  document.addEventListener('accentchange',()=>{
    readAcc();
    if(reduced)draw(performance.now());else kick();
  });
  new IntersectionObserver(es=>{visible=es[0].isIntersecting;if(visible)kick()},{threshold:0}).observe(hero);
  document.addEventListener('visibilitychange',()=>{if(!document.hidden)kick()});
  addEventListener('resize',size);
  readAcc();size();
  if(reduced)draw(performance.now());else kick();          // reduced: one static render, no loop
})();
function burstConfetti(x,y,n){
  n=n||80;
  const colors=['#d4fd73','#d4fd73','#ffffff','#e7e5e4','#a3e635'];
  for(let k=0;k<n;k++){
    const p=document.createElement('span');
    p.className='confetti';
    const sz=4+Math.random()*6;
    p.style.left=x+'px';p.style.top=y+'px';
    p.style.width=(sz*0.6)+'px';p.style.height=sz+'px';
    p.style.background=colors[k%colors.length];
    const ang=Math.random()*Math.PI*2, dist=90+Math.random()*(n>100?380:220);
    p.style.setProperty('--dx',(Math.cos(ang)*dist).toFixed(1)+'px');
    p.style.setProperty('--dy',((Math.sin(ang)*dist)-160).toFixed(1)+'px');
    p.style.setProperty('--rot',((Math.random()*720)-360).toFixed(0)+'deg');
    p.style.animationDuration=(0.9+Math.random()*0.9)+'s';
    document.body.append(p);setTimeout(()=>p.remove(),2000);
  }
}
// ---- copy email button — mikedev.site exact (component D):
// clipboard.writeText → 1800ms "Copied to clipboard" + icon swap; catch → mailto fallback
(async function(){
  const btn=document.getElementById('copyEmail');
  if(!btn||!navigator.clipboard)return;
  const txt=btn.querySelector('.copy-txt');
  const email=btn.getAttribute('data-email')||((txt&&txt.textContent)||'').trim();
  let t=0;
  btn.addEventListener('click',async()=>{
    try{
      await navigator.clipboard.writeText(email);
      btn.classList.add('copied');
      if(txt){
        txt.textContent='Copied to clipboard';
        clearTimeout(t);
        t=setTimeout(()=>{btn.classList.remove('copied');txt.textContent=email},1800);   // Mike: exact 1800ms
      }
    }catch(e){
      window.location.href='mailto:'+email;                                             // Mike: exact fallback
    }
  });
})();
// ---- open-to-work sticker easter egg (1/5) + dark pop-toast sunod sa video ----
function showPop(title,sub,ms){
  const t=document.getElementById('popToast');if(!t)return;
  t.innerHTML='<span class="pop-ic">✦</span><span class="pop-tx"><b>'+title+'</b><small>'+sub+'</small></span>';
  t.classList.add('show');clearTimeout(t._h);t._h=setTimeout(()=>t.classList.remove('show'),ms||2800);
}
function secretMuted(){try{return localStorage.getItem('edsiel-secrets-complete-muted')==='1'}catch(e){return false}}
function muteSecretComplete(on){try{on?localStorage.setItem('edsiel-secrets-complete-muted','1'):localStorage.removeItem('edsiel-secrets-complete-muted')}catch(e){}}
// secret hunt — mikedev exact texts: "Secret found" / "Already found this one" /
// complete "You found every secret 🏆", body "<label> · <n>/5", chime + confetti 220 inig complete
function secret(id){
  const LABELS={sticker:'Popped the open-to-work sticker',konami:'Entered the Konami code',name:'Typed my name',sudo:'Tried to get root',wordmark:'Poked every letter'};
  let got=[];try{got=JSON.parse(localStorage.getItem('edsiel-secrets')||'[]');if(!Array.isArray(got))got=[]}catch(e){got=[]}
  got=got.filter(g=>LABELS[g]);
  const isNew=got.indexOf(id)<0;
  if(isNew){got.push(id);try{localStorage.setItem('edsiel-secrets',JSON.stringify(got))}catch(e){}}
  const done=got.length>=5;
  if(isNew&&typeof sfx==='function')sfx('chime');
  if(done&&isNew&&typeof mikeConfetti==='function')mikeConfetti(innerWidth/2,innerHeight/3,220);
  if(done&&secretMuted())return isNew;
  const title=done?'You found every secret 🏆':isNew?'Secret found':'Already found this one';
  const body=done?"Seriously impressive. Say hi through the contact form, I'd love to meet you.":LABELS[id]+' · '+got.length+'/5';
  showPop(title,body+(done?'<label class="pop-mute"><input type="checkbox" onchange="muteSecretComplete(this.checked)"> Don\'t show this again</label>':''),done?5000:2800);
  return isNew;
}
function popSticker(ev){
  if(ev)ev.stopPropagation();
  let n=0;try{n=parseInt(localStorage.getItem('sticker_pops')||'0',10)||0}catch(e){}
  const x=ev&&ev.clientX!==undefined?ev.clientX:innerWidth*0.75;
  const y=ev&&ev.clientY!==undefined?ev.clientY:200;
  mikeConfetti(x,y,70);
  const badge=document.getElementById('stickerBadge');
  if(badge){badge.classList.remove('popped');void badge.offsetWidth;badge.classList.add('popped');setTimeout(()=>badge.classList.remove('popped'),600)}
  if(!n){try{localStorage.setItem('sticker_pops','1')}catch(e){}}
  secret('sticker');
}
// ============================================================
// mikedev.site exact clone — ticklish wordmark letters + canvas confetti.
// Ported 1:1 from the de-minified framer-motion source (no libraries):
//   letter: y:[0,-28%,0] rotate:[0,±12° rand,0] scaleY:[1,1.08,.94,1],
//           duration .6s, ease cubic-bezier(.22,1,.36,1), on pointerenter+down
//   all letters poked → confetti({count:140}) + secret 1x
// ============================================================
(function(){
  const box=document.getElementById('bigName');
  if(!box||box.dataset.ticklish)return;
  box.dataset.ticklish='1';
  const reduced=matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* --- exact mikedev confetti (module 85071) ----------------- */
  function canvasConfetti(opts){
    opts=opts||{};
    if(matchMedia('(prefers-reduced-motion: reduce)').matches)return;
    // palette: accent base → [accent, +20% black, +40% white, stone-100, stone-400]
    let hex=(getComputedStyle(document.documentElement).getPropertyValue('--acc').trim()||'#d4fd73').replace('#','');
    if(hex.length===3)hex=hex.split('').map(c=>c+c).join('');
    const base=[parseInt(hex.slice(0,2),16),parseInt(hex.slice(2,4),16),parseInt(hex.slice(4,6),16)];
    const mix=(k,to)=>'rgb('+base.map(v=>Math.round(v+(to-v)*k)).join(',')+')';
    const pal=['rgb('+base.join(',')+')',mix(.2,0),mix(.4,255),'#f5f5f4','#a8a29e'];

    const cv=document.createElement('canvas');
    cv.setAttribute('aria-hidden','true');
    Object.assign(cv.style,{position:'fixed',inset:'0',width:'100vw',height:'100vh',pointerEvents:'none',zIndex:'10000'});
    document.body.appendChild(cv);
    const ctx=cv.getContext('2d');
    if(!ctx){cv.remove();return}
    const dpr=Math.min(window.devicePixelRatio||1,2),W=window.innerWidth,H=window.innerHeight;
    cv.width=W*dpr;cv.height=H*dpr;ctx.scale(dpr,dpr);
    const x=null!=opts.x?opts.x:W/2,y=null!=opts.y?opts.y:H/2;
    const count=null!=opts.count?opts.count:120;
    // spawn: angle 2π rand, speed 4+9r, vy starts lifted by 6, size 5+6r,
    // rot π rand, vr ±.2, 30% of pieces are round
    const parts=Array.from({length:count},()=>{
      const ang=Math.random()*Math.PI*2,sp=4+9*Math.random();
      return{x,y,vx:Math.cos(ang)*sp,vy:Math.sin(ang)*sp-6,size:5+6*Math.random(),rot:Math.random()*Math.PI,vr:(Math.random()-.5)*.4,color:pal[Math.random()*pal.length|0],round:Math.random()<.3};
    });
    let raf=0;
    const g=()=>{
      ctx.clearRect(0,0,W,H);
      let alive=0;
      for(const p of parts){
        p.vy+=.28;p.vx*=.985;p.x+=p.vx;p.y+=p.vy;p.rot+=p.vr;
        if(p.y<H+20)alive++;
        ctx.save();ctx.translate(p.x,p.y);ctx.rotate(p.rot);ctx.fillStyle=p.color;
        if(p.round){ctx.beginPath();ctx.arc(0,0,p.size/2.4,0,2*Math.PI);ctx.fill()}
        else ctx.fillRect(-p.size/2,-p.size/4,p.size,p.size/2);
        ctx.restore();
      }
      if(alive)raf=requestAnimationFrame(g);else cv.remove();
    };
    raf=requestAnimationFrame(g);
    setTimeout(()=>{cancelAnimationFrame(raf);cv.remove()},6000);
  }

  /* --- per-property keyframe sampler ------------------------- */
  // framer samples y:[...], rotate:[...], scaleY:[...] as one eased timeline;
  // we bake cubic-bezier(.22,1,.36,1) into ~30 sampled WAAPI keyframes
  // (Newton-Raphson solve of x(u)=u, standard cubic-bezier solver).
  function bezier(m1x,m1y,m2x,m2y){
    return function(u){
      if(u<=0)return 0;if(u>=1)return 1;
      let lo=0,hi=1,t=u;
      for(let k=0;k<8;k++){
        const x=3*m1x*t*(1-t)*(1-t)+3*m2x*t*t*(1-t)+t*t*t;
        if(Math.abs(x-u)<1e-5)break;
        x<u?lo=t:hi=t;
        t=(lo+hi)/2;
      }
      return 3*m1y*t*(1-t)*(1-t)+3*m2y*t*t*(1-t)+t*t*t;
    };
  }
  const EASE=bezier(.22,1,.36,1);
  const sample=(k,t)=>{const p=t*(k.length-1),i=Math.min(k.length-2,Math.floor(p));return k[i]+(k[i+1]-k[i])*(p-i)};

  /* --- letters ------------------------------------------------ */
  const letters=[].slice.call(box.querySelectorAll('span')).filter(s=>s.textContent.trim());
  const poked=new Set();
  let announced=false;
  function poke(el){
    // mikedev ordering: onPoke fires BEFORE the reduced-motion/animation guard, so the
    // secret still unlocks even without motion — only the animation itself is skipped
    poked.add(el.index);el.classList.add('poked');
    if(!announced&&poked.size>=letters.length){
      announced=true;
      canvasConfetti({count:140});
      secret('wordmark');
    }
    if(reduced||!el.animate)return;
    const tilt=(Math.random()-.5)*24;   // ±12° random snap, exactly like mikedev
    const kf=[];
    for(let i=0;i<=30;i++){
      const t=EASE(i/30);
      const y=sample([0,-28,0],t),r=sample([0,tilt,0],t),s=sample([1,1.08,.94,1],t);
      kf.push({transform:'translate3d(0,'+y.toFixed(3)+'%,0) rotate('+r.toFixed(3)+'deg) scaleY('+s.toFixed(4)+')'});
    }
    el.animate(kf,{duration:600,easing:'linear'});
  }
  letters.forEach((s,i)=>{
    s.index=i;
    // pointerenter + pointerdown — parehas sa mikedev (onPointerEnter + onPointerDown)
    s.addEventListener('pointerenter',()=>poke(s));
    s.addEventListener('pointerdown',()=>poke(s));
  });
})();
// ---- typing secrets — mikedev exact: lowercase key match, skip sa inputs,
// konami → confetti 160, name → confetti 100 (iya "mike", ato "edsiel")
(function(){
  const KON=['arrowup','arrowup','arrowdown','arrowdown','arrowleft','arrowright','arrowleft','arrowright','b','a'];
  const seq=[];let nameBuf='',sudoBuf='';
  addEventListener('keydown',e=>{
    const t=e.target;
    if(t&&(t.tagName==='INPUT'||t.tagName==='TEXTAREA'||t.isContentEditable))return;
    if(e.metaKey||e.ctrlKey||e.altKey)return;
    const k=e.key.toLowerCase();
    seq.push(k);if(seq.length>KON.length)seq.shift();
    if(seq.length===KON.length&&KON.every((v,i)=>seq[i]===v)){
      seq.length=0;
      mikeConfetti(innerWidth/2,innerHeight/3,160);
      secret('konami');
    }
    if(k.length===1){
      nameBuf=(nameBuf+k).slice(-6);sudoBuf=(sudoBuf+k).slice(-4);
      if(nameBuf==='edsiel'){nameBuf='';mikeConfetti(innerWidth/2,innerHeight/3,100);secret('name');}
      if(sudoBuf==='sudo'){sudoBuf='';secret('sudo');}
    }
  });
})();
