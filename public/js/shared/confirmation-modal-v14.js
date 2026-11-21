/* FindBack global confirmation V14: one accessible modal for admin actions. */
(()=>{'use strict';
const init=()=>{
 let dialog=document.getElementById('fb-confirm-global-v14');
 if(!dialog){dialog=document.createElement('dialog');dialog.id='fb-confirm-global-v14';dialog.innerHTML='<section class="fb-modal-v14-panel"><h2 id="fb-modal-v14-title"></h2><p id="fb-modal-v14-message"></p><div class="fb-modal-v14-actions"><button type="button" data-fb-modal-cancel>Batal</button><button type="button" data-fb-modal-ok>Lanjutkan</button></div></section>';dialog.setAttribute('aria-labelledby','fb-modal-v14-title');dialog.setAttribute('aria-describedby','fb-modal-v14-message');document.body.appendChild(dialog)};
 const cancel=dialog.querySelector('[data-fb-modal-cancel]'),ok=dialog.querySelector('[data-fb-modal-ok]');let callback=null;
 cancel.addEventListener('click',()=>dialog.close());ok.addEventListener('click',()=>{const fn=callback;callback=null;dialog.close();if(fn)fn()});
 dialog.addEventListener('close',()=>{callback=null});
 const ask=(msg,label,fn,trigger)=>{if(dialog.open)return;let clean=(label||'').replace(/^[\s✓✕+]+/g,'').trim().replace(/^Konfirmasi\s+/i,'').trim();const low=clean.toLowerCase();let title=clean ? clean+'?' : 'Konfirmasi tindakan?';if(low==='persetujuan')title='Setujui laporan?';else if(low==='penolakan')title='Tolak laporan?';else if(low==='serah terima')title='Simpan serah terima?';else if(low.startsWith('setujui'))title=clean+'?';else if(low.startsWith('tolak'))title=clean+'?';else if(low.startsWith('simpan'))title=clean+'?';else if(low.startsWith('batalkan'))title=clean+'?';
 dialog.querySelector('h2').textContent=title;dialog.querySelector('p').textContent=msg;ok.textContent=clean&&clean.length<32?clean:'Ya, Lanjutkan';delete ok.dataset.claimTone;
 if(trigger?.dataset.claimTone==='approve'){
  ok.dataset.claimTone='approve';
  const icon=trigger.querySelector('svg');
  if(icon)ok.prepend(icon.cloneNode(true));
 }
 callback=fn;dialog.showModal()};
 document.addEventListener('click',e=>{const btn=e.target.closest?.('[data-confirm][data-confirm-event="click"]');if(!btn||btn.dataset.fbModalBypass==='1')return;e.preventDefault();e.stopImmediatePropagation();if(btn.form&&!btn.form.reportValidity())return;let msg=btn.dataset.confirm;if(/menyetujui laporan ini\?/i.test(msg))msg='Setujui laporan ini setelah memastikan identitas, foto, deskripsi, dan lokasi telah diperiksa?';ask(msg,btn.textContent,()=>{btn.dataset.fbModalBypass='1';try{if(btn.form){if(btn.form.requestSubmit)btn.form.requestSubmit(btn);else btn.form.submit()}else btn.click()}finally{delete btn.dataset.fbModalBypass}},btn)},true);
 document.addEventListener('submit',e=>{const form=e.target;if(form.dataset.confirmEvent!=='submit'||!form.dataset.confirm||form.dataset.fbModalBypass==='1')return;e.preventDefault();e.stopImmediatePropagation();const submitter=e.submitter;ask(form.dataset.confirm,submitter?.textContent||'Konfirmasi tindakan',()=>{form.dataset.fbModalBypass='1';try{if(form.requestSubmit)form.requestSubmit(submitter||undefined);else form.submit()}finally{delete form.dataset.fbModalBypass}})},true);
};if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init,{once:true});else init();
})();
