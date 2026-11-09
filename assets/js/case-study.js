// Case-study walkthrough lightbox: same open/close motion as the reference page.
(function(){
  const modal = document.getElementById('caseShotModal');
  const openBtn = document.querySelector('[data-case-shot]');
  if (!modal || !openBtn) return;

  const closeBtn = modal.querySelector('[data-case-close]');
  const prevOverflow = document.body.style.overflow;
  let lastFocus = null;
  let closing = false;
  let timer = 0;

  function lock(on){
    if (on) {
      lastFocus = document.activeElement;
      document.body.style.overflow = 'hidden';
      if (window.lenis && window.lenis.stop) window.lenis.stop();
    } else {
      document.body.style.overflow = prevOverflow;
      if (window.lenis && window.lenis.start) window.lenis.start();
    }
  }

  function open(){
    if (closing || modal.classList.contains('open')) return;
    clearTimeout(timer);
    lock(true);
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.remove('closing');
    modal.classList.add('open');
    if (closeBtn) setTimeout(function(){ closeBtn.focus(); }, 30);
  }

  function close(){
    if (!modal.classList.contains('open') || closing) return;
    closing = true;
    modal.classList.add('closing');
    timer = setTimeout(function(){
      modal.classList.remove('open', 'closing');
      modal.setAttribute('aria-hidden', 'true');
      closing = false;
      lock(false);
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }, 200);
  }

  openBtn.addEventListener('click', open);
  modal.querySelectorAll('[data-case-close]').forEach(function(el){
    el.addEventListener('click', close);
  });
  modal.addEventListener('click', function(e){
    if (e.target === modal) close();
  });
  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && modal.classList.contains('open')) close();
  });
})();
