// Hand tracking — mikedev.site style, clean-room port.
// MediaPipe Tasks Vision HandLandmarker (CDN, no build step).
// Move = index fingertip (lm 8) moves hand cursor.
// Pinch (thumb 4 + index 8) = click. Feeds heroDots via synthetic pointermove.
import { HandLandmarker, FilesetResolver } from "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/+esm";

const CONN = [[0,1],[1,2],[2,3],[3,4],[0,5],[5,6],[6,7],[7,8],[5,9],[9,10],[10,11],[11,12],[9,13],[13,14],[14,15],[15,16],[13,17],[17,18],[18,19],[19,20],[0,17]];
const MODEL_URL = "https://storage.googleapis.com/mediapipe-models/hand_landmarker/hand_landmarker/float16/1/hand_landmarker.task";
const WASM_URL = "https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@0.10.14/wasm";
const SKELETON = "#7dd3fc"; // sky blue sama sa screenshot ni Mike

let landmarker = null;
let running = false;
let loading = false;
let stream = null;
let raf = 0;
let lastVideoTime = -1;
let lastClick = 0;
let sx = innerWidth / 2, sy = innerHeight / 2; // smoothed cursor
let hoverEl = null;
let noHandSince = 0;

function $(id) { return document.getElementById(id); }

function setStatus(html) {
  const el = $("handStatus");
  if (el) el.innerHTML = html;
}

function paintBtn(on) {
  const b = $("handBtn");
  if (!b) return;
  b.classList.toggle("on", !!on);
  b.setAttribute("aria-pressed", on ? "true" : "false");
}

function toastFallback(msg) {
  if (typeof window.toast === "function") window.toast(msg);
  else setStatus(msg);
}

async function ensureLandmarker() {
  if (landmarker) return landmarker;
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
}

async function startHand() {
  const panel = $("handPanel"), video = $("handVideo"), cursor = $("handCursor");
  if (!panel || !video) return;
  if (running || loading) return;
  // needs camera API (secure context + getUserMedia)
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    toastFallback("No camera API — use HTTPS / localhost with Chrome/Edge.");
    return;
  }
  loading = true;
  panel.classList.add("show");
  paintBtn(true);
  setStatus("Loading hand model…");
  try {
    await ensureLandmarker();
    stream = await navigator.mediaDevices.getUserMedia({
      video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: "user" },
      audio: false
    });
    video.srcObject = stream;
    // lock the preview box to the real camera aspect so the image never stretches —
    // skeleton canvas then maps 1:1 with the video frame.
    video.onloadedmetadata = () => {
      try {
        if (video.videoWidth && video.videoHeight) {
          video.parentElement.style.aspectRatio = video.videoWidth + " / " + video.videoHeight;
        }
      } catch (e) {}
    };
    await video.play();
    running = true;
    loading = false;
    if (cursor) cursor.classList.add("show");
    setStatus("<b>Move</b> to control cursor • <b>Pinch</b> to click");
    sx = innerWidth / 2; sy = innerHeight / 2;
    lastVideoTime = -1;
    noHandSince = performance.now();
    loop();
  } catch (e) {
    loading = false;
    paintBtn(false);
    panel.classList.remove("show");
    console.warn("[hand]", e);
    if (e && (e.name === "NotAllowedError" || e.name === "SecurityError")) {
      toastFallback("Camera blocked — allow camera permission and try again.");
    } else if (e && e.name === "NotFoundError") {
      toastFallback("No camera found on this device.");
    } else {
      toastFallback("Couldn't start hand tracking. Check camera + connection.");
    }
  }
}

function stopHand() {
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
  clearCanvas();
}

window.toggleHandTracking = function (ev) {
  if (ev) ev.stopPropagation();
  if (running) stopHand();
  else startHand();
};
window.stopHandTracking = stopHand;

function clearCanvas() {
  const cv = $("handOverlay");
  if (!cv) return;
  const ctx = cv.getContext("2d");
  if (ctx) ctx.clearRect(0, 0, cv.width, cv.height);
}

function drawSkeleton(lms) {
  const cv = $("handOverlay");
  const video = $("handVideo");
  if (!cv) return;
  // fit canvas to video box (box aspect is locked to video aspect on metadata,
  // so x*w / y*h maps 1:1 with the frame — no stretch, no crop offset)
  const box = cv.parentElement.getBoundingClientRect();
  const w = Math.max(2, Math.round(box.width)), h = Math.max(2, Math.round(box.height));
  if (cv.width !== w || cv.height !== h) { cv.width = w; cv.height = h; }
  // re-lock aspect mid-stream in case the camera switched resolution
  try {
    if (video && video.videoWidth && video.videoHeight) {
      const want = video.videoWidth + " / " + video.videoHeight;
      if (cv.parentElement.style.aspectRatio !== want) cv.parentElement.style.aspectRatio = want;
    }
  } catch (e) {}
  const ctx = cv.getContext("2d");
  if (!ctx) return;
  ctx.clearRect(0, 0, w, h);
  ctx.lineWidth = 2;
  ctx.strokeStyle = SKELETON;
  ctx.fillStyle = SKELETON;
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
    ctx.arc(x, y, 2.4, 0, 6.2832);
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
  // tight alignment: snap when far (first detect), then fast lerp .55
  // so the ring sticks to the fingertip without lag.
  const dx = x - sx, dy = y - sy;
  if (dx * dx + dy * dy > 120 * 120) { sx = x; sy = y; }
  else { sx += dx * 0.55; sy += dy * 0.55; }
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

function tryClick(x, y) {
  const now = performance.now();
  if (now - lastClick < 900) return; // debounce against pinch jitter
  lastClick = now;
  let el = null;
  try { el = document.elementFromPoint(x, y); } catch (e) {}
  const hit = el ? el.closest("a,button,.chip,[role='button'],input,label") : null;
  if (hit) {
    try {
      if (typeof window.sfx === "function") window.sfx("pop");
    } catch (e) {}
    hit.click();
    setStatus("Clicked: <b>" + (hit.textContent || hit.getAttribute("aria-label") || hit.tagName).trim().slice(0, 24) + "</b>");
    setTimeout(() => { if (running) setStatus("<b>Move</b> to control cursor • <b>Pinch</b> to click"); }, 1200);
  }
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
    if (performance.now() - noHandSince > 2500) setStatus("Show your hand to the camera…");
    return;
  }
  noHandSince = performance.now();
  drawSkeleton(lms);
  const tip = lms[8], thumb = lms[4];
  // mirror X: moving right moves the cursor right
  const cx = (1 - tip.x) * innerWidth;
  const cy = tip.y * innerHeight;
  const pinchD = dist(tip, thumb);
  const pinch = pinchD < 0.055;
  moveCursor(cx, cy, pinch);
  if (pinch) tryClick(sx, sy);
  else if (performance.now() - lastClick > 1500) {
    const el = $("handStatus");
    if (el && el.textContent.startsWith("Clicked")) setStatus("<b>Move</b> to control cursor • <b>Pinch</b> to click");
  }
}

// auto-stop when tab hidden (saves camera + battery)
document.addEventListener("visibilitychange", () => {
  if (document.hidden && running) stopHand();
});
