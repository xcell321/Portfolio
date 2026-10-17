// Hand tracking — mikedev.site style, clean-room port.
// MediaPipe Tasks Vision HandLandmarker (CDN, no build step).
// Move = index fingertip (lm 8) moves hand cursor.
// Pinch (thumb 4 + index 8) = click. Feeds heroDots via synthetic pointermove.
// Loading (sama kang Mike): dark 80% overlay + spinner dayon inig-click;
// camera-first — inig-live sa cam makita na ang face (dimmed) sa luyo
// samtang nag-load ang hand model; smooth fade-out inig-ready.
// Dynamic import (dili static) ang library — kung ma-block/hang ang CDN,
// mo-load gihapon ni nga module ug makita ang red error sa panel + watchdog
// messages, imbis dead module ug spinner nga walay katapusan.

const LIB_URL = "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/+esm";

const CONN = [[0,1],[1,2],[2,3],[3,4],[0,5],[5,6],[6,7],[7,8],[5,9],[9,10],[10,11],[11,12],[9,13],[13,14],[14,15],[15,16],[13,17],[17,18],[18,19],[19,20],[0,17]];
const MODEL_URL = "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task";
const WASM_URL = "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm";

let landmarker = null;
let lmP = null; // in-flight model load — shared aron usa ra ka download bisan pa-retry
let running = false;
let loading = false;
let gen = 0; // session token — mang-invalidate sa in-flight loads inig stop
let stream = null;
let raf = 0;
let lastVideoTime = -1;
let lastClick = 0;
let sx = innerWidth / 2, sy = innerHeight / 2; // smoothed cursor
let trail = []; // rolling average (last 3) for stable cursor
let hoverEl = null;
let wasPinch = false;
let pinchSince = 0;
let scrollMode = false;
let lastScrollY = 0;

function $(id) { return document.getElementById(id); }

// loading overlay sa sulod sa preview — morag kang Mike: dark 80% cover +
// spinner samtang nag-load ang model; makita ra gihapon ang face (dimmed)
// sa luyo inig-live sa camera kay camera-first man; dili blocking ang page
function setStatus(msg, isErr) {
  const el = $("handLoading"), m = $("handLoadMsg");
  if (!el) return;
  clearTimeout(el._fade);
  el.classList.remove("hide", "fade");
  el.classList.toggle("err", !!isErr);
  if (m) m.textContent = msg;
}

// inig-ready: smooth fade-out (300ms) dayon display:none, pariha sa
// AnimatePresence exit fade sa kang Mike
function hideStatus() {
  const el = $("handLoading");
  if (!el) return;
  clearTimeout(el._fade);
  el.classList.add("fade");
  el._fade = setTimeout(() => {
    el.classList.add("hide");
    el.classList.remove("fade");
  }, 320);
}

function paintBtn(on) {
  const b = $("handBtn");
  if (!b) return;
  b.classList.toggle("on", !!on);
  b.setAttribute("aria-pressed", on ? "true" : "false");
}

function toastFallback(msg) {
  if (typeof window.toast === "function") window.toast(msg);
  else setStatus(msg, true);
}

function ensureLandmarker() {
  if (landmarker) return Promise.resolve(landmarker);
  if (!lmP) {
    lmP = (async () => {
      const { FilesetResolver, HandLandmarker } = await import(LIB_URL);
      const vision = await FilesetResolver.forVisionTasks(WASM_URL);
      landmarker = await HandLandmarker.createFromOptions(vision, {
        baseOptions: { modelAssetPath: MODEL_URL, delegate: "GPU" },
        runningMode: "VIDEO",
        numHands: 1,
        minHandDetectionConfidence: 0.5,
        minHandPresenceConfidence: 0.5,
        minTrackingConfidence: 0.5
      });
      return landmarker;
    })();
    lmP.catch(() => {}); // guard: kung ma-cancel samtang nag-load, walay unhandled rejection
    lmP.then(() => { lmP = null; }, () => { lmP = null; });
  }
  return lmP;
}

async function startHand() {
  const panel = $("handPanel"), video = $("handVideo"), cursor = $("handCursor");
  if (!panel || !video) return;
  if (running || loading) return;
  // warm audio INSIDE the real click gesture (before any await) —
  // kung dili, suspended ang AudioContext ug silent tanang hand-click sounds
  try { if (typeof window.warmAudio === "function") window.warmAudio(); } catch (e) {}
  // needs camera API (secure context + getUserMedia)
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    toastFallback("No camera API — use HTTPS / localhost with Chrome/Edge.");
    return;
  }
  const my = ++gen; // token sa session — inig stop/cancel, stale na kining tanan nga awaits
  loading = true;
  panel.classList.add("show");
  paintBtn(true);
  setStatus("Loading hand model…", false); // spinner dayon, dili maghuwat, dili blocking
  let tCam = 0, tSlow = 0, tStuck = 0; // watchdog timers (messages lang, dili mo-cancel)
  try {
    // Mike style: CAMERA una — mo-play dayon ang preview (makita na ang nawng,
    // dimmed lang sa loading overlay) samtang ang model nag-load sa background
    const cam = navigator.mediaDevices.getUserMedia({
      video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
      audio: false
    });
    const modelP = ensureLandmarker(); // dunganon sa camera para walay wasted time
    // kung dugay ang camera (pending pa ang permission prompt), naay guidance
    // imbis hilom nga spinner — dili mo-cancel, sulti lang unsay buhaton
    tCam = setTimeout(() => { if (my === gen && !stream) setStatus("Waiting for camera… click Allow in the address bar.", false); }, 8000);
    const s = await cam;
    clearTimeout(tCam);
    if (my !== gen) { s.getTracks().forEach(t => { try { t.stop(); } catch (e) {} }); return; }
    stream = s;
    video.srcObject = s;
    await video.play();
    if (my !== gen) return;
    // camera live na — naa na ang face sa user (dimmed sa luyo sa 80% dark
    // overlay, sama kang Mike) samtang nagpadayon ang loading sa hand model
    setStatus("Camera ready — loading hand model…", false);
    // watchdog messages samtang nag-load ang model (hinay/blocked nga net)
    tSlow = setTimeout(() => { if (my === gen && loading) setStatus("Still loading hand model… slow connection?", false); }, 15000);
    tStuck = setTimeout(() => { if (my === gen && loading) setStatus("Hand model stuck loading — check your internet connection.", true); }, 45000);
    await modelP;
    clearTimeout(tSlow); clearTimeout(tStuck);
    if (my !== gen) return;
    running = true;
    loading = false;
    hideStatus();
    if (cursor) cursor.classList.add("show");
    sx = innerWidth / 2; sy = innerHeight / 2;
    trail = [];
    lastVideoTime = -1;
    loop();
  } catch (e) {
    clearTimeout(tCam); clearTimeout(tSlow); clearTimeout(tStuck);
    if (my !== gen) return;
    loading = false;
    if (stream) { stream.getTracks().forEach(t => { try { t.stop(); } catch (_e) {} }); stream = null; }
    if (video) video.srcObject = null;
    console.warn("[hand]", e);
    // panel magpabilin nga abli (Mike style) — error makita sa overlay in red
    // imbis mo-close; click sa hand button o X para sirado/retry
    if (e && (e.name === "NotAllowedError" || e.name === "SecurityError")) {
      setStatus("Camera blocked — allow camera permission and try again.", true);
    } else if (e && e.name === "NotFoundError") {
      setStatus("No camera found on this device.", true);
    } else {
      setStatus("Couldn't load hand tracking — check camera + internet, then retry.", true);
    }
  }
}

function stopHand() {
  gen++; // invalidate ang in-flight startHand (camera/model downloads)
  running = false;
  loading = false;
  cancelAnimationFrame(raf);
  if (stream) { stream.getTracks().forEach(t => { try { t.stop(); } catch (e) {} }); stream = null; }
  const video = $("handVideo");
  if (video) video.srcObject = null;
  $("handPanel")?.classList.remove("show");
  $("handCursor")?.classList.remove("show", "pinch");
  if (hoverEl) { hoverEl.classList.remove("hand-hover"); hoverEl = null; }
  paintBtn(false);
  hideStatus();
  clearCanvas();
  trail = [];
  wasPinch = false;
  scrollMode = false;
}

window.toggleHandTracking = function (ev) {
  if (ev) ev.stopPropagation();
  const panel = $("handPanel");
  // sirado/open state ang basehan (dili lang running) — para pwede ma-cancel
  // bisan nag-load pa o naay error nga overlay
  if (running || loading || (panel && panel.classList.contains("show"))) stopHand();
  else startHand();
};
window.stopHandTracking = stopHand;

function clearCanvas() {
  const cv = $("handOverlay");
  if (!cv) return;
  const ctx = cv.getContext("2d");
  if (ctx) ctx.clearRect(0, 0, cv.width, cv.height);
}

function accRgb() {
  try {
    let hex = (getComputedStyle(document.documentElement).getPropertyValue("--acc").trim() || "#d4fd73").replace("#", "");
    if (hex.length === 3) hex = hex.split("").map(c => c + c).join("");
    const n = parseInt(hex.slice(0, 6), 16);
    return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
  } catch (e) { return [212, 253, 115]; }
}

function drawSkeleton(lms, pinch) {
  const cv = $("handOverlay");
  if (!cv) return;
  // fixed 220×165 box (exact Mike size/radius) — ang canvas nag-fill sa box,
  // x*w / y*h mapping sama sa kang Mike
  const box = cv.parentElement.getBoundingClientRect();
  const w = Math.max(2, Math.round(box.width)), h = Math.max(2, Math.round(box.height));
  if (cv.width !== w || cv.height !== h) { cv.width = w; cv.height = h; }
  const ctx = cv.getContext("2d");
  if (!ctx) return;
  ctx.clearRect(0, 0, w, h);
  const rgb = accRgb().join(",");
  ctx.lineWidth = 1.5;
  ctx.strokeStyle = "rgba(" + rgb + "," + (pinch ? "0.9" : "0.5") + ")";
  ctx.fillStyle = "rgba(" + rgb + ",0.9)";
  ctx.beginPath();
  for (const [a, b] of CONN) {
    // No extra (1-x) here: video and canvas are both mirrored with scaleX(-1) in CSS,
    // so raw x*w keeps the skeleton aligned with the hand in the preview.
    // (1-x) + CSS mirror would double-mirror and misalign.
    const ax = lms[a].x * w, ay = lms[a].y * h;
    const bx = lms[b].x * w, by = lms[b].y * h;
    ctx.moveTo(ax, ay);
    ctx.lineTo(bx, by);
  }
  ctx.stroke();
  for (const p of lms) {
    const x = p.x * w, y = p.y * h;
    ctx.beginPath();
    ctx.arc(x, y, 2, 0, 6.2832);
    ctx.fill();
  }
}

function dist(a, b) {
  const dx = a.x - b.x, dy = a.y - b.y;
  return Math.hypot(dx, dy);
}

function moveCursor(x, y, pinch) {
  const cursor = $("handCursor");
  if (!cursor) return;
  // rolling average of last 3 positions — stable cursor without lag
  trail.push({ x, y });
  if (trail.length > 3) trail.shift();
  let ax = 0, ay = 0;
  for (const p of trail) { ax += p.x; ay += p.y; }
  sx = ax / trail.length;
  sy = ay / trail.length;
  cursor.style.transform = "translate(" + sx.toFixed(1) + "px," + sy.toFixed(1) + "px)" + (pinch ? " scale(.65)" : "");
  cursor.classList.toggle("pinch", !!pinch);
  // feed heroDots + tilt + all pointermove listeners (global-listener style)
  try {
    window.dispatchEvent(new PointerEvent("pointermove", {
      clientX: sx, clientY: sy, pointerType: "mouse", bubbles: true
    }));
  } catch (e) {
    try { window.dispatchEvent(new Event("pointermove")); } catch (_e) {}
  }
  // hover highlight on the link/button under the hand
  try {
    const el = document.elementFromPoint(sx, sy);
    const hit = el ? el.closest("a,button,.chip,[role='button']") : null;
    if (hit !== hoverEl) {
      if (hoverEl) hoverEl.classList.remove("hand-hover");
      hoverEl = hit;
      if (hoverEl) hoverEl.classList.add("hand-hover");
    }
  } catch (e) {}
}

function fireClick(x, y) {
  let el = null;
  try { el = document.elementFromPoint(x, y); } catch (e) {}
  const hit = el ? el.closest("a,button,.chip,[role='button'],input,label") : null;
  if (!hit) return;
  try {
    if (typeof window.sfx === "function") window.sfx("pop");
  } catch (e) {}
  try {
    // detail:1 sama sa tinuod nga click — para dili mag-double ang tap sound sa main.js guard
    hit.dispatchEvent(new MouseEvent("click", { bubbles: true, cancelable: true, view: window, detail: 1, clientX: x, clientY: y }));
  } catch (e) {
    hit.click();
  }
}

function doScroll(dy) {
  const l = window.lenis;
  if (l && l.scrollTo) {
    try { l.scrollTo(window.scrollY + dy, { duration: 0.4 }); return; }
    catch (e) {}
  }
  window.scrollBy({ top: dy, behavior: "smooth" });
}

function loop() {
  if (!running) return;
  const video = $("handVideo");
  raf = requestAnimationFrame(loop);
  if (!video || video.readyState < 2 || video.videoWidth === 0) return;
  if (video.currentTime === lastVideoTime) return;
  lastVideoTime = video.currentTime;
  let res = null;
  try {
    res = landmarker.detectForVideo(video, performance.now());
  } catch (e) {
    return;
  }
  const lms = res && res.landmarks && res.landmarks[0];
  if (!lms) {
    clearCanvas();
    wasPinch = false;
    scrollMode = false;
    return;
  }
  const thumb = lms[4], tip = lms[8];
  const pinchD = dist(tip, thumb);
  const pinch = pinchD < 0.06;
  drawSkeleton(lms, pinch);
  // cursor follows the THUMB mapped from the center 50% of the frame to full screen —
  // small hand movements cover the whole page
  const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
  const cx = (1 - (clamp(thumb.x, 0.25, 0.75) - 0.25) / 0.5) * innerWidth;
  const cy = ((clamp(thumb.y, 0.25, 0.75) - 0.25) / 0.5) * innerHeight;
  moveCursor(cx, cy, pinch || scrollMode);
  const now = performance.now();
  if (pinch && !wasPinch) {
    // pinch start (rising edge) = click, 800ms debounce
    if (now - lastClick > 800) {
      lastClick = now;
      fireClick(sx, sy);
    }
    pinchSince = now;
    scrollMode = false;
    lastScrollY = sy;
  }
  if (pinch && !scrollMode && now - pinchSince > 1000) {
    // hold pinch 1s = scroll mode, vertical hand motion scrolls the page
    scrollMode = true;
    lastScrollY = sy;
  }
  if (scrollMode) {
    if (!pinch) {
      scrollMode = false;
    } else {
      doScroll(-((sy - lastScrollY) * 3.5));
      lastScrollY = sy;
    }
  }
  wasPinch = pinch;
}

// NOTE: walay visibilitychange/auto-stop — sama kang Mike, magpadayon ang
// hand tracking (face + camera) bisan mo-alt-tab o mo-switch tab ang user.
// Ang rAF loop natural ra mo-pause sa hidden tab ug mo-resume inig-balik,
// apan dili ma-close ang panel ug dili mo-stop ang camera stream.
