/* FindBack V10: feedback begins on touch-down, cancels on swipe/scroll. */
(function () {
  'use strict';
  const selector = '.fb-items-summary > article, .fb-v-stats-v18 > .fb-v-stat';
  const activeClass = 'fb-touch-v10';
  const timeoutByCard = new WeakMap();
  let gesture = null;
  const coarse = () => matchMedia('(hover: none), (pointer: coarse)').matches;
  function reset(card) {
    if (!card) return;
    clearTimeout(timeoutByCard.get(card));
    card.classList.remove(activeClass);
  }
  function start(card, x, y) {
    if (gesture) reset(gesture.card);
    reset(card);
    gesture = {card, x, y, moved:false};
    card.classList.add(activeClass);
  }
  function move(x,y) {
    if (!gesture) return;
    if (Math.hypot(x - gesture.x, y - gesture.y) > 10) {
      reset(gesture.card);
      gesture.moved = true;
    }
  }
  function finish() {
    if (!gesture) return;
    const g = gesture; gesture = null;
    if (g.moved) { reset(g.card); return; }
    timeoutByCard.set(g.card, setTimeout(() => reset(g.card), 360));
  }
  if (window.PointerEvent) {
    document.addEventListener('pointerdown', e => {
      if (e.pointerType === 'mouse' || !coarse()) return;
      const card = e.target.closest(selector);
      if (card) start(card,e.clientX,e.clientY);
    }, {passive:true});
    document.addEventListener('pointermove', e => {
      if (e.pointerType !== 'mouse') move(e.clientX,e.clientY);
    }, {passive:true});
    document.addEventListener('pointerup', e => {
      if (e.pointerType !== 'mouse') finish();
    }, {passive:true});
    document.addEventListener('pointercancel', () => {if(gesture) reset(gesture.card); gesture=null;}, {passive:true});
  } else {
    document.addEventListener('touchstart', e => {
      if (!coarse() || !e.touches.length) return;
      const card = e.target.closest(selector);
      if (card) start(card,e.touches[0].clientX,e.touches[0].clientY);
    }, {passive:true});
    document.addEventListener('touchmove', e => {
      if (e.touches.length) move(e.touches[0].clientX,e.touches[0].clientY);
    }, {passive:true});
    document.addEventListener('touchend',finish,{passive:true});
    document.addEventListener('touchcancel', () => {if(gesture) reset(gesture.card); gesture=null;},{passive:true});
  }
  window.addEventListener('scroll', () => {if(gesture){reset(gesture.card); gesture=null;}}, {passive:true});
  document.addEventListener('visibilitychange', () => {if(document.hidden&&gesture){reset(gesture.card);gesture=null;}});
})();
