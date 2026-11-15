/* FindBack unified matching photo viewer V5: one owner for open, close and zoom. */
(()=>{'use strict';
function start(){
 const dialog=document.getElementById('fb-match-photo-dialog');if(!dialog||typeof dialog.showModal!=='function')return;
 const stage=dialog.querySelector('.fb-match-photo-stage');
 const canvas=dialog.querySelector('.fb-match-photo-canvas');
 const img=dialog.querySelector('[data-fb-photo-image]');
 const title=dialog.querySelector('#fb-match-photo-title');
 const error=dialog.querySelector('[data-fb-photo-error]');
 const out=dialog.querySelector('[data-fb-photo-zoom-out]');
 const inc=dialog.querySelector('[data-fb-photo-zoom-in]');
 const reset=dialog.querySelector('[data-fb-photo-reset]');
 const value=dialog.querySelector('[data-fb-photo-zoom-value]');
 if(!stage||!canvas||!img||!title||!error||!out||!inc||!reset||!value)return;
 const uid=dialog.dataset.fbPhotoUser||'anonymous';
 const key='findback.matching.photoZoom.v5.user.'+uid;
 const clamp=n=>Math.max(50,Math.min(300,Math.round(n/25)*25));
 let zoom=100,opener=null,loadToken=0;
 try{const stored=localStorage.getItem(key);if(stored!==null&&stored!==''){const n=Number(stored);if(Number.isFinite(n))zoom=clamp(n)}}catch(_){}
 function update(){
   value.textContent=zoom+'%';out.disabled=zoom<=50;inc.disabled=zoom>=300;
   if(!img.naturalWidth||!img.naturalHeight||!dialog.open)return;
   const sw=Math.max(120,stage.clientWidth-24),sh=Math.max(120,stage.clientHeight-24);
   const fit=Math.min(1,sw/img.naturalWidth,sh/img.naturalHeight);
   const w=Math.round(img.naturalWidth*fit*zoom/100),h=Math.round(img.naturalHeight*fit*zoom/100);
   canvas.style.width=Math.max(stage.clientWidth,w+24)+'px';canvas.style.height=Math.max(stage.clientHeight,h+24)+'px';
   img.style.width=w+'px';img.style.height=h+'px';img.style.maxWidth='none';img.style.maxHeight='none';
 }
 function change(n){zoom=clamp(n);try{localStorage.setItem(key,String(zoom))}catch(_){}update()}
 out.addEventListener('click',()=>change(zoom-25));inc.addEventListener('click',()=>change(zoom+25));reset.addEventListener('click',()=>change(100));
 function open(link){
  if(dialog.open)return;
  opener=link;loadToken++;
  const label=(link.dataset.photoLabel||'Laporan').replace(/^Laporan\s+/i,'').trim();
  title.textContent='Foto Laporan '+label;
  error.hidden=true;img.hidden=false;img.alt='Foto laporan '+label;
  img.removeAttribute('src');img.style.width='';img.style.height='';canvas.style.width='';canvas.style.height='';
  dialog.showModal();value.textContent=zoom+'%';out.disabled=zoom<=50;inc.disabled=zoom>=300;
  img.src=link.href;
 }
 // Capture before other delegated listeners can navigate to the raw image.
 document.addEventListener('click',e=>{
  const link=e.target.closest?.('a[data-fb-match-photo]');if(!link)return;
  if(!link.closest('.match-wrap'))return;
  e.preventDefault();e.stopImmediatePropagation();open(link);
 },true);
 img.addEventListener('load',()=>{error.hidden=true;img.hidden=false;requestAnimationFrame(update)});
 img.addEventListener('error',()=>{img.hidden=true;error.hidden=false});
 dialog.querySelectorAll('[data-fb-photo-close]').forEach(b=>b.addEventListener('click',()=>dialog.close()));
 dialog.addEventListener('click',e=>{if(e.target===dialog)dialog.close()});
 dialog.addEventListener('close',()=>{
   img.removeAttribute('src');img.style.width='';img.style.height='';canvas.style.width='';canvas.style.height='';
   try{opener?.focus({preventScroll:true})}catch(_){}opener=null;
 });
 stage.addEventListener('wheel',e=>{if(!e.ctrlKey)return;e.preventDefault();change(zoom+(e.deltaY<0?25:-25))},{passive:false});
 let timer=0;window.addEventListener('resize',()=>{if(!dialog.open)return;clearTimeout(timer);timer=setTimeout(update,80)},{passive:true});
}
if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start,{once:true});else start();
})();
