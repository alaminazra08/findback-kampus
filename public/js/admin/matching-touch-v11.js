/* FindBack V11: matching touch animation; cancels when swiping. */
(function(){
'use strict';
const selector='.match-wrap:has(> .match-index-heading) .match-stat';
let current=null,timer=null;
function clear(){if(timer)clearTimeout(timer);timer=null;if(current)current.card.classList.remove('fb-match-touch-v11');current=null;}
document.addEventListener('pointerdown',function(e){
 if(e.pointerType==='mouse'||!matchMedia('(hover:none), (pointer:coarse)').matches)return;
 const card=e.target.closest(selector);if(!card)return;
 clear();current={card,x:e.clientX,y:e.clientY,moved:false};card.classList.add('fb-match-touch-v11');
},{passive:true});
document.addEventListener('pointermove',function(e){
 if(!current||e.pointerType==='mouse')return;
 if(Math.hypot(e.clientX-current.x,e.clientY-current.y)>10){clear();}
},{passive:true});
document.addEventListener('pointerup',function(e){
 if(!current||e.pointerType==='mouse')return;
 const card=current.card;current=null;timer=setTimeout(()=>{card.classList.remove('fb-match-touch-v11');timer=null;},380);
},{passive:true});
document.addEventListener('pointercancel',clear,{passive:true});
window.addEventListener('scroll',clear,{passive:true});
document.addEventListener('visibilitychange',function(){if(document.hidden)clear();});
})();
