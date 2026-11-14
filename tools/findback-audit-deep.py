#!/usr/bin/env python3
"""Read-only heuristic asset audit. No third-party dependencies."""
import argparse, collections, datetime, hashlib, json, pathlib, re

def read(path):
    return path.read_text(encoding='utf-8', errors='replace')

def clean_css(text):
    return re.sub(r'/\*.*?\*/', '', text, flags=re.S)

def css_rules(text):
    # Heuristic: not a full CSS parser. Track selectors before opening braces.
    text=clean_css(text)
    for match in re.finditer(r'([^{}]+)\{', text):
        selector=match.group(1).strip()
        if not selector or selector.startswith('@'): continue
        selector=selector.split('}')[-1].strip()
        if selector and len(selector)<250:
            yield re.sub(r'\s+', ' ', selector)

def audit(root):
    css=sorted((root/'public/css').rglob('*.css'))
    js=sorted((root/'public/js').rglob('*.js'))
    selectors=collections.defaultdict(set)
    hashes=collections.defaultdict(list)
    for p in css+js:
        data=p.read_bytes()
        hashes[hashlib.sha256(data).hexdigest()].append(p.relative_to(root).as_posix())
    for p in css:
        for selector in set(css_rules(read(p))):
            selectors[selector].add(p.relative_to(root).as_posix())
    shared=sorted(({'selector':s,'files':sorted(paths)} for s,paths in selectors.items() if len(paths)>1), key=lambda x:(-len(x['files']),x['selector']))
    duplicates=[{'files':v} for v in hashes.values() if len(v)>1]
    js_candidates=[]
    for p in js:
        s=read(p)
        markers={
          'addEventListener':len(re.findall(r'\.addEventListener\s*\(',s)),
          'DOMContentLoaded':len(re.findall(r'\bDOMContentLoaded\b',s)),
          'popstate':len(re.findall(r'\bpopstate\b',s)),
          'fetch':len(re.findall(r'\bfetch\s*\(',s)),
          'history.pushState':len(re.findall(r'\bhistory\.pushState\s*\(',s)),
          'history.replaceState':len(re.findall(r'\bhistory\.replaceState\s*\(',s)),
        }
        if any(markers.values()):js_candidates.append({'file':p.relative_to(root).as_posix(),'signals':markers})
    references=[]
    for p in (root/'resources/views').rglob('*.blade.php'):
        s=read(p)
        for m in re.finditer(r'(?:asset\s*\(\s*[\'\"]|[\'\"])(?P<path>(?:css|js)/[^\'\"?]+\.(?:css|js))',s):
            references.append({'view':p.relative_to(root).as_posix(),'asset':m.group('path')})
    unused=[]
    referenced={r['asset'] for r in references}
    for p in css+js:
        rel=p.relative_to(root/'public').as_posix()
        if rel not in referenced:unused.append(rel)
    return {
      'generated_at_utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),
      'scope':'Static heuristic; no browser/runtime verification',
      'counts':{'css':len(css),'js':len(js),'cross_file_selector_candidates':len(shared),'identical_file_groups':len(duplicates),'js_files_with_signals':len(js_candidates),'assets_without_literal_blade_reference':len(unused)},
      'identical_file_groups':duplicates,
      'cross_file_selector_candidates':shared,
      'js_event_navigation_signals':js_candidates,
      'assets_without_literal_blade_reference_NOT_PROOF_OF_UNUSED':unused,
      'limitations':['CSS parsing is heuristic, including media rules and nesting','Shared selectors may be intentional and ordered','Listener counts do not establish duplicate registration','Assets may be referenced dynamically, by PHP, CSS imports, or Vite','No browser, database, route, or AJAX behavior was executed']
    }

def main():
    a=argparse.ArgumentParser()
    a.add_argument('--project',required=True)
    a.add_argument('--output',required=True)
    args=a.parse_args()
    root=pathlib.Path(args.project).resolve()
    if not (root/'artisan').is_file():raise SystemExit('ERROR: Laravel project not found')
    result=audit(root)
    dest=pathlib.Path(args.output)
    dest.parent.mkdir(parents=True,exist_ok=True)
    dest.write_text(json.dumps(result,ensure_ascii=False,indent=2),encoding='utf-8')
    print('=== AUDIT STATIS TAHAP 2 ===')
    for key,value in result['counts'].items():print(f'{key}: {value}')
    print('Laporan JSON:',dest)
    print('CATATAN: kandidat konflik bukan bukti bug. Tidak ada file produksi yang diubah.')

if __name__=='__main__':main()
