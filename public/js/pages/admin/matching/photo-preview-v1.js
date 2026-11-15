/* FindBack matching photo preview V4: persistent, responsive zoom. */
(()=>{'use strict';
const dialog=document.getElementById('fb-match-photo-dialog');if(!dialog||typeof dialog.showModal!=='function')return;
const image=dialog.querySelector('[data-fb-photo-image]'),error=dialog.querySelector('[data-fb-photo-error]'),title=dialog.querySelector('#fb-match-photo-title'),stage=dialog.querySelector('.fb-match-photo-stage');
if(!image||!stage)return;
let opener=null,zoom=100,baseW=0,baseH=0;
const account=(document.querySelector('meta[name="fb-user-id"]')?.content||document.querySelector('[data-user-id]')?.dataset.userId||'local-admin');
const key='findback:matching-photo-zoom:v4:'+account;
try{const n=Number(localStorage.getItem(key));if(Number.isFinite(n)&&n>=50&&n<=300)zoom=n;}catch(e){}
const controls=document.createElement('div');controls.className='fb-photo-zoom-controls';controls.setAttribute('role','group');controls.setAttribute('aria-label','Kontrol zoom foto');
controls.innerHTML='<button type="button" data-zoom-out aria-label="Perkecil foto">-</button><output data-zoom-value aria-live="polite">100%</output><button type="button" data-zoom-in aria-label="Perbesar foto">+</button><button type="button" data-zoom-reset>Reset</button>';
const footer=dialog.querySelector('.fb-match-photo-footer');if(footer)footer.prepend(controls);else dialog.querySelector('.fb-match-photo-box').append(controls);
const value=controls.querySelector('[data-zoom-value]');
function fit(){if(!image.naturalWidth||!image.naturalHeight)return;const maxW=Math.max(120,stage.clientWidth-32),maxH=Math.max(120,stage.clientHeight-32);const ratio=Math.min(1,maxW/image.naturalWidth,maxH/image.naturalHeight);baseW=Math.max(1,Math.round(image.naturalWidth*ratio));baseH=Math.max(1,Math.round(image.naturalHeight*ratio));render();}
function render(){value.textContent=zoom+'%';controls.querySelector('[data-zoom-out]').disabled=zoom<=50;controls.querySelector('[data-zoom-in]').disabled=zoom>=300;if(!baseW)return;image.style.width=Math.round(baseW*zoom/100)+'px';image.style.height=Math.round(baseH*zoom/100)+'px';image.style.maxWidth='none';image.style.maxHeight='none';image.style.flexShrink='0';}
function setZoom(n){zoom=Math.max(50,Math.min(300,Math.round(n/25)*25));try{localStorage.setItem(key,String(zoom));}catch(e){}render();}
controls.querySelector('[data-zoom-out]').addEventListener('click',()=>setZoom(zoom-25));controls.querySelector('[data-zoom-in]').addEventListener('click',()=>setZoom(zoom+25));controls.querySelector('[data-zoom-reset]').addEventListener('click',()=>setZoom(100));
image.addEventListener('load',()=>{image.hidden=false;error.hidden=true;fit();});image.addEventListener('error',()=>{image.hidden=true;error.hidden=false;});
document.addEventListener('click',event=>{const link=event.target.closest?.('a[data-fb-match-photo]');if(!link||!link.closest('.match-wrap'))return;event.preventDefault();if(dialog.open)return;opener=link;baseW=0;baseH=0;image.removeAttribute('style');image.hidden=false;error.hidden=true;image.alt='Foto '+(link.dataset.photoLabel||'laporan').toLowerCase();title.textContent='Foto '+(link.dataset.photoLabel||'Laporan').replace(/^LAPORAN /i,'Laporan ');dialog.showModal();image.src=link.href;render();});
dialog.querySelectorAll('[data-fb-photo-close]').forEach(button=>button.addEventListener('click',()=>dialog.close()));dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});dialog.addEventListener('close',()=>{image.removeAttribute('src');image.removeAttribute('style');opener?.focus({preventScroll:true});opener=null;});
const observer=new ResizeObserver(()=>{if(dialog.open&&image.complete&&image.naturalWidth)fit();});observer.observe(stage);
stage.addEventListener('wheel',e=>{if(!e.ctrlKey)return;e.preventDefault();setZoom(zoom+(e.deltaY<0?25:-25));},{passive:false});
})();
