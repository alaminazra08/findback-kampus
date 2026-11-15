/* FindBack V12: shared touch feedback for Items, Verification and Matching. */
(function(){'use strict';
const selector='.fb-items-summary > article, .fb-v-stats > .fb-v-stat, .match-stats > .match-stat';
const cls='fb-stat-active-v12';let gesture=null;const timers=new WeakMap();
function reset(card){if(!card)return;clearTimeout(timers.get(card));card.classList.remove(cls);}
function start(card,x,y){if(gesture)reset(gesture.card);reset(card);gesture={card,x,y,moved:false};card.classList.add(cls);}
function move(x,y){if(!gesture)return;if(Math.hypot(x-gesture.x,y-gesture.y)>10){reset(gesture.card);gesture.moved=true;}}
function end(){if(!gesture)return;const g=gesture;gesture=null;if(g.moved){reset(g.card);return;}timers.set(g.card,setTimeout(()=>reset(g.card),380));}
function cancel(){if(gesture)reset(gesture.card);gesture=null;}
const touchDevice=()=>matchMedia('(hover:none), (pointer:coarse)').matches;
if(window.PointerEvent){
document.addEventListener('pointerdown',e=>{if(e.pointerType==='mouse'||!touchDevice())return;const card=e.target.closest(selector);if(card)start(card,e.clientX,e.clientY);},{passive:true});
document.addEventListener('pointermove',e=>{if(e.pointerType!=='mouse')move(e.clientX,e.clientY);},{passive:true});
document.addEventListener('pointerup',e=>{if(e.pointerType!=='mouse')end();},{passive:true});
document.addEventListener('pointercancel',cancel,{passive:true});
}else{
document.addEventListener('touchstart',e=>{if(!touchDevice()||!e.touches.length)return;const card=e.target.closest(selector);if(card)start(card,e.touches[0].clientX,e.touches[0].clientY);},{passive:true});
document.addEventListener('touchmove',e=>{if(e.touches.length)move(e.touches[0].clientX,e.touches[0].clientY);},{passive:true});
document.addEventListener('touchend',end,{passive:true});document.addEventListener('touchcancel',cancel,{passive:true});}
window.addEventListener('scroll',cancel,{passive:true});document.addEventListener('visibilitychange',()=>{if(document.hidden)cancel();});
})();
