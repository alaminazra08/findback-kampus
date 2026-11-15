/* FindBack V9 — intentional taps only; no activation while swiping/scrolling. */
(function () {
  'use strict';
  if (!window.PointerEvent) return;
  const selector = '.fb-items-summary > article, .fb-v-stats-v18 > .fb-v-stat';
  const timers = new WeakMap();
  let active = null;
  let x = 0, y = 0, moved = false;
  const touchMode = () => window.matchMedia('(hover: none), (pointer: coarse)').matches;
  function clear(card) {
    if (!card) return;
    window.clearTimeout(timers.get(card));
    card.classList.remove('fb-touch-active');
  }
  document.addEventListener('pointerdown', function(e) {
    if (e.pointerType === 'mouse' || !touchMode()) return;
    const card = e.target.closest(selector);
    if (!card) return;
    if (active && active !== card) clear(active);
    active = card; x = e.clientX; y = e.clientY; moved = false;
    clear(card);
  }, {passive:true});
  document.addEventListener('pointermove', function(e) {
    if (!active || e.pointerType === 'mouse') return;
    if (Math.hypot(e.clientX-x,e.clientY-y) > 9) { moved = true; clear(active); }
  }, {passive:true});
  document.addEventListener('pointerup', function(e) {
    if (!active || e.pointerType === 'mouse') return;
    const card = active; active = null;
    if (moved || !card.contains(e.target)) return;
    card.classList.add('fb-touch-active');
    timers.set(card,window.setTimeout(() => clear(card), 430));
  }, {passive:true});
  document.addEventListener('pointercancel', function(){clear(active); active=null;}, {passive:true});
  window.addEventListener('scroll',function(){clear(active);active=null;},{passive:true});
  document.addEventListener('visibilitychange',function(){if(document.hidden){clear(active);active=null;}});
})();
