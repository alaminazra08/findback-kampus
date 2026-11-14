import os,re,json,subprocess,datetime,sys,shutil
from pathlib import Path
p=Path(sys.argv[1]).resolve(); out=Path(sys.argv[2]).resolve(); out.mkdir(parents=True,exist_ok=True)
report={'generated_at':datetime.datetime.now().isoformat(),'project':str(p),'scope':'static checks + optional local CLI; not browser E2E','checks':{},'warnings':[]}
def files(glob):return list(p.glob(glob))
views=files('resources/views/**/*.blade.php'); css=files('public/css/**/*.css'); js=files('public/js/**/*.js'); controllers=files('app/Http/Controllers/**/*.php'); tests=files('tests/**/*Test.php')
report['inventory']={'blade':len(views),'css':len(css),'js':len(js),'controllers':len(controllers),'tests':len(tests)}
route=(p/'routes/web.php'); route_text=route.read_text(encoding='utf-8',errors='replace') if route.exists() else ''
report['checks']['route_source_exists']=route.exists()
report['checks']['route_markers']={k:bool(re.search(k,route_text,re.I)) for k in ['admin','user','login','register','claim','statistic','report']}
report['checks']['role_middleware_signals']={'middleware':len(re.findall(r'\bmiddleware\s*\(',route_text)),'auth':len(re.findall(r'auth',route_text,re.I))}
report['checks']['view_directives']={k:sum(len(re.findall(k,v.read_text(encoding='utf-8',errors='replace'))) for v in views) for k in [r'@extends\s*\(',r'@include\s*\(',r'@push\s*\(',r'@stack\s*\(']}
report['checks']['responsive']={'media_query_files':sum('@media' in f.read_text(encoding='utf-8',errors='replace') for f in css),'viewport_meta_views':sum('name="viewport"' in f.read_text(encoding='utf-8',errors='replace') or "name='viewport'" in f.read_text(encoding='utf-8',errors='replace') for f in views),'fixed_width_candidate_files':[]}
for f in css:
 s=f.read_text(encoding='utf-8',errors='replace'); hits=re.findall(r'(?<![\w-])width\s*:\s*(?:[5-9]\d\d|[1-9]\d{3,})px',s)
 if hits:report['checks']['responsive']['fixed_width_candidate_files'].append({'file':str(f.relative_to(p)),'count':len(hits)})
report['checks']['test_names']=[str(f.relative_to(p)) for f in tests]
report['checks']['ajax_signals']={'fetch_files':sum(bool(re.search(r'\bfetch\s*\(',f.read_text(encoding='utf-8',errors='replace'))) for f in js),'popstate_files':sum('popstate' in f.read_text(encoding='utf-8',errors='replace') for f in js),'history_api_files':sum('pushState' in f.read_text(encoding='utf-8',errors='replace') for f in js)}
php=shutil.which('php'); php_local=Path('C:/xampp/php/php.exe')
if os.name=='nt' and php_local.exists():php=str(php_local)
def run(name,command,timeout=120):
 try:
  r=subprocess.run(command,cwd=p,capture_output=True,text=True,timeout=timeout,encoding='utf-8',errors='replace'); report['checks'][name]={'status':'pass' if r.returncode==0 else 'fail','exit_code':r.returncode,'output_tail':(r.stdout+'\n'+r.stderr)[-4500:]}
 except subprocess.TimeoutExpired:report['checks'][name]={'status':'timeout'}
 except Exception as e:report['checks'][name]={'status':'not_run','reason':str(e)}
if php and (p/'artisan').exists():
 run('php_version',[php,'-v'],20)
 run('route_list',[php,'artisan','route:list','--json'],90)
 report['checks']['artisan_test']={'status':'not_run','reason':'Intentionally skipped: tests may modify database; run only after isolated testing database is confirmed'}
else:report['checks']['laravel_cli']={'status':'not_run','reason':'PHP or artisan unavailable'}
report['warnings']=['Static CSS candidates are not confirmed overflow.','CLI tests may require a configured local database and dependencies.','No browser, device viewport, live authentication, or admin-user end-to-end verification was performed.','Do not share full report publicly without reviewing command output for sensitive details.']
(out/'audit-stage4.json').write_text(json.dumps(report,indent=2,ensure_ascii=False),encoding='utf-8')
lines=['# Audit FindBack Tahap 4','',f"Waktu: {report['generated_at']}",'','## Inventaris',json.dumps(report['inventory'],ensure_ascii=False),'','## Hasil pemeriksaan CLI']
for k in ['php_version','route_list','artisan_test']:
 x=report['checks'].get(k,{});lines.append(f"- {k}: {x.get('status','tidak dijalankan')} (exit {x.get('exit_code','-')})")
lines+=['','## Batasan','- Pemeriksaan ini tidak membuktikan tampilan mobile dan desktop sudah benar.','- Uji manual browser tetap diperlukan: 320/360/375/390/425/768/1024/1280/1440/1920 px.','- Uji alur user lapor -> admin verifikasi -> status user -> klaim -> pengembalian, serta back/forward browser dan AJAX.','- Periksa detail JSON untuk kandidat masalah dan hasil tes.']
(out/'RINGKASAN-TAHAP4.md').write_text('\n'.join(lines),encoding='utf-8')
print('=== AUDIT TAHAP 4 ===');print(json.dumps(report['inventory'],ensure_ascii=False));
for k in ['route_list','artisan_test']:print(k,report['checks'].get(k,{}).get('status','not_run'))
print('Laporan:',out/'audit-stage4.json');print('Audit selesai; status test gagal tidak disembunyikan.')
