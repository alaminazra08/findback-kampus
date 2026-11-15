/* FindBack V3: persistent zoom for matching photo preview, per account and browser. */
(()=>{'use strict';
const dialog=document.getElementById('fb-match-photo-dialog');
if(!dialog||!dialog.querySelector('[data-fb-photo-zoom]'))return;
const stage=dialog.querySelector('.fb-match-photo-stage');
const canvas=dialog.querySelector('.fb-match-photo-canvas');
const img=dialog.querySelector('[data-fb-photo-image]');
const value=dialog.querySelector('[data-fb-photo-zoom-value]');
const dec=dialog.querySelector('[data-fb-photo-zoom-out]');
const inc=dialog.querySelector('[data-fb-photo-zoom-in]');
const reset=dialog.querySelector('[data-fb-photo-reset]');
if(!stage||!canvas||!img||!value||!dec||!inc||!reset)return;
const userId=dialog.dataset.fbPhotoUser||'unknown';
const key='findback.matching.photoZoom.v3.user.'+userId;
const clamp=n=>Math.max(50,Math.min(300,Math.round(n/25)*25));
let zoom=100;
try{const stored=Number(localStorage.getItem(key));if(Number.isFinite(stored)&&stored>=50&&stored<=300)zoom=clamp(stored);}catch(_){}
function store(){try{localStorage.setItem(key,String(zoom));}catch(_){}}
function paint(preservePosition=false){
 value.textContent=zoom+'%';dec.disabled=zoom<=50;inc.disabled=zoom>=300;
 if(!img.complete||!img.naturalWidth||!img.naturalHeight)return;
 const availWidth=Math.max(80,stage.clientWidth-30),availHeight=Math.max(80,stage.clientHeight-30);
 const factor=Math.min(availWidth/img.naturalWidth,availHeight/img.naturalHeight,1);
 const width=Math.max(1,Math.round(img.naturalWidth*factor*zoom/100));
 const height=Math.max(1,Math.round(img.naturalHeight*factor*zoom/100));
 const oldX=stage.scrollWidth>stage.clientWidth?(stage.scrollLeft+stage.clientWidth/2)/stage.scrollWidth:.5;
 const oldY=stage.scrollHeight>stage.clientHeight?(stage.scrollTop+stage.clientHeight/2)/stage.scrollHeight:.5;
 canvas.style.width=Math.max(stage.clientWidth,width+24)+'px';
 canvas.style.height=Math.max(stage.clientHeight,height+24)+'px';
 img.style.width=width+'px';img.style.height=height+'px';
 requestAnimationFrame(()=>{
  stage.scrollLeft=Math.max(0,stage.scrollWidth* (preservePosition?oldX:.5)-stage.clientWidth/2);
  stage.scrollTop=Math.max(0,stage.scrollHeight*(preservePosition?oldY:.5)-stage.clientHeight/2);
 });
}
function setZoom(n){const next=clamp(n);if(next===zoom)return;zoom=next;store();paint(true);}
dec.addEventListener('click',()=>setZoom(zoom-25));
inc.addEventListener('click',()=>setZoom(zoom+25));
reset.addEventListener('click',()=>setZoom(100));
img.addEventListener('load',()=>paint(false));
dialog.addEventListener('close',()=>{img.style.width='';img.style.height='';canvas.style.width='';canvas.style.height='';});
// The existing preview script opens the dialog; update dimensions once the dialog is visible.
document.addEventListener('click',e=>{if(e.target.closest?.('a[data-fb-match-photo]'))requestAnimationFrame(()=>paint(false));});
window.addEventListener('resize',()=>{if(dialog.open)paint(true);},{passive:true});
if('ResizeObserver'in window){new ResizeObserver(()=>{if(dialog.open&&img.naturalWidth)paint(true);}).observe(stage);}
// On touchscreen, native scroll remains available while enlarged.
value.textContent=zoom+'%';dec.disabled=zoom<=50;inc.disabled=zoom>=300;
})();
