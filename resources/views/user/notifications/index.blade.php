@extends('user.layouts.campus')
@section('title', 'Notifikasi')

@section('content')
    @php
        $user = auth()->user();
        $totalAll = $user->notifications()->count();
        $totalUnread = $user->unreadNotifications()->count();
        $totalRead = $totalAll - $totalUnread;
        $readPct = $totalAll > 0 ? (int) round($totalRead / $totalAll * 100) : 0;
        $unreadPct = $totalAll > 0 ? 100 - $readPct : 0;
        $totalPct = $totalAll > 0 ? 100 : 0;

        $chatUnread = \App\Models\ChatMessage::where('user_id', auth()->id())
            ->where('sender_id', '!=', auth()->id())
            ->whereNull('read_at')
            ->count();

        // Warna, ikon, dan label mengikuti isi notifikasi
        $toneOf = function ($n) {
            $status = (string) ($n->data['status'] ?? '');
            $title = strtolower((string) ($n->data['title'] ?? ''));
            if (str_contains($status, 'reject') || str_contains($title, 'ditolak')) return 'danger';
            if (str_contains($status, 'requested') || str_contains($title, 'revisi') || str_contains($title, 'bukti')) return 'warn';
            if (str_contains($status, 'approved') || str_contains($title, 'disetujui') || str_contains($title, 'selesai')) return 'success';
            return 'info';
        };
        $iconOf = ['danger' => 'box', 'warn' => 'clock', 'success' => 'check', 'info' => 'bell'];
        $badgeOf = ['danger' => 'Ditolak', 'warn' => 'Perlu tindakan', 'success' => 'Berhasil', 'info' => 'Info'];

        $dayLabel = function ($date) {
            $d = $date->copy()->timezone('Asia/Jakarta')->startOfDay();
            $today = now('Asia/Jakarta')->startOfDay();
            return match (true) {
                $d->equalTo($today) => 'Hari ini',
                $d->equalTo($today->copy()->subDay()) => 'Kemarin',
                default => $d->locale('id')->translatedFormat('j M Y'),
            };
        };
        $groups = $notifications->getCollection()->groupBy(fn($n) => $dayLabel($n->created_at));
    @endphp

    <style>
        .nf{--w:#830d2c;--w2:#a3173f;--w-soft:#fbeff2;--ink:#261b20;--mut:#7a6f74;--ln:#efe8ea;
            --ok:#14663f;--ok-bg:#e4f4ea;--wa:#8f5a00;--wa-bg:#fff0cf;--er:#b3162f;--er-bg:#fde7ea;--in:#830d2c;--in-bg:#fbeff2;
            width:100%;margin:0;padding:8px 0 56px;color:var(--ink)}
        .nf *{box-sizing:border-box}

        /* Banner utama */
        .nf-hero{position:relative;display:flex;align-items:center;justify-content:space-between;gap:24px;flex-wrap:wrap;
            padding:16px 30px;margin-bottom:16px;border-radius:30px;color:#fff;overflow:hidden;
            background:radial-gradient(120% 140% at 100% 0%,#b61e48 0%,rgba(182,30,72,0) 55%),linear-gradient(135deg,#5e0820 0%,#830d2c 55%,#9c1239 100%);
            box-shadow:0 22px 48px rgba(110,10,38,.30)}
        .nf-hero:before{content:"";position:absolute;left:-70px;bottom:-110px;width:280px;height:280px;border-radius:50%;border:38px solid rgba(255,255,255,.06)}
        .nf-hero:after{content:"";position:absolute;right:210px;top:-70px;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.06)}
        .nf-hero>*{position:relative;z-index:1}
        .nf .nf-hero h1{margin:0 0 6px;font-size:clamp(24px,3vw,32px);line-height:1.1;letter-spacing:-.04em;color:#fff}
        .nf .nf-hero p{margin:0;max-width:460px;color:rgba(255,255,255,.82);line-height:1.6}
        .nf-hero-side{display:flex;align-items:center;gap:22px;flex-wrap:wrap}
        .nf-ring{display:grid;place-items:center;width:72px;height:72px;border-radius:50%;
            background:conic-gradient(#fff calc(var(--p)*1%),rgba(255,255,255,.22) 0)}
        .nf-ring span{display:grid;place-items:center;width:56px;height:56px;border-radius:50%;background:#7a0b28;text-align:center;line-height:1.1}
        .nf-ring b{display:block;font-size:17px;letter-spacing:-.03em}
        .nf-ring i{display:block;font-style:normal;font-size:10.5px;color:rgba(255,255,255,.75);margin-top:2px}
        .nf-art{display:block;width:230px;height:120px;margin:-6px 6px -6px 0;color:#fff}
        .nf-hero form{margin:0}
        .nf-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:44px;padding:0 20px;border:1px solid #e6d3d9;
            border-radius:999px;background:#fff;color:var(--w);font-weight:700;cursor:pointer;transition:.18s;box-shadow:0 2px 10px rgba(80,20,40,.05)}
        .nf-btn:hover{background:var(--w-soft);border-color:var(--w)}
        .nf-hero .nf-btn{border-color:transparent;box-shadow:0 10px 24px rgba(0,0,0,.22)}
        .nf-btn.solid{background:linear-gradient(135deg,var(--w),var(--w2));border-color:transparent;color:#fff;box-shadow:0 8px 20px rgba(131,13,44,.25)}
        .nf-btn.solid:hover{filter:brightness(1.06);background:linear-gradient(135deg,var(--w),var(--w2))}
        .nf-btn .cu-icon{width:18px;height:18px}

        /* Kartu ringkasan */
        .nf-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:22px}
        .nf-stat{position:relative;display:flex;align-items:center;gap:16px;padding:14px 18px;overflow:hidden;
            border:1px solid var(--ln);border-radius:22px;background:#fff;box-shadow:0 10px 30px rgba(58,26,36,.05)}
        .nf-stat-ic{display:grid;place-items:center;flex:none;width:56px;height:56px;border-radius:18px}
        .nf-stat-ic .cu-icon{width:26px;height:26px}
        .nf-stat small{display:block;color:var(--mut);font-size:13.5px;font-weight:600}
        .nf-stat strong{display:block;margin-top:2px;font-size:26px;line-height:1.05;letter-spacing:-.03em}
        .nf-stat.total .nf-stat-ic{background:var(--w-soft);color:var(--w)}
        .nf-stat.read .nf-stat-ic{background:var(--ok-bg);color:var(--ok)}
        .nf-stat.unread{background:linear-gradient(135deg,#fff1f4,#fff);border-color:#efcdd6}
        .nf-stat.unread strong{color:var(--w)}
        .nf-stat.unread .nf-stat-ic{background:#fbdce4;color:var(--w)}
        .nf-stat>*{position:relative;z-index:1}
        .nf-stat-tx{flex:1;min-width:0}
        .nf-stat.total .nf-bar{visibility:hidden}
        .nf-bar{display:block;height:5px;margin-top:8px;border-radius:99px;background:#f0e9ec;overflow:hidden}
        .nf-bar i{display:block;height:100%;border-radius:99px;background:var(--w);transition:none}
        .nf-stat.unread .nf-bar{background:#f7dbe2}.nf-stat.unread .nf-bar i{background:#e0436b}
        .nf-stat.read .nf-bar{background:#dcefe4}.nf-stat.read .nf-bar i{background:#2f9565}
        .nf-stat{transition:.2s}
        .nf-stat:hover{box-shadow:0 16px 36px rgba(58,26,36,.09)}

        .nf-chat{display:flex;align-items:center;gap:14px;padding:14px 18px;margin-bottom:22px;border:1px solid #efd3db;border-radius:18px;background:linear-gradient(100deg,#fff5f7,#fff);transition:.18s}
        .nf-chat:hover{border-color:var(--w);box-shadow:0 8px 22px rgba(131,13,44,.08)}
        .nf-chat-ic{display:grid;place-items:center;flex:none;width:38px;height:38px;border-radius:50%;background:var(--w);color:#fff}
        .nf-chat-ic .cu-icon{width:19px;height:19px}

        /* Panel utama */
        .nf-panel{border:1px solid var(--ln);border-radius:26px;background:#fff;box-shadow:0 16px 44px rgba(58,26,36,.06);overflow:hidden}
        .nf-panel-top{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;padding:24px 28px;
            border-bottom:1px solid var(--ln);background:linear-gradient(180deg,#fff8fa,#fff)}
        .nf-panel-top h2{margin:0;font-size:21px;letter-spacing:-.02em}
        .nf-panel-top p{margin:3px 0 0;font-size:13.5px;color:var(--mut)}
        .nf-chips{display:flex;gap:4px;padding:4px;border-radius:999px;background:#f5eff1}
        .nf-chip{padding:8px 16px;border:0;border-radius:999px;background:transparent;color:var(--mut);font-weight:700;font-size:13px;cursor:pointer;transition:.18s}
        .nf-chip:hover{color:var(--w)}
        .nf-chip.on{background:#fff;color:var(--w);box-shadow:0 2px 8px rgba(58,26,36,.12)}

        .nf-group{padding:6px 28px 10px}
        .nf-day{display:flex;align-items:center;gap:12px;margin:20px 0 10px;font-size:13px;font-weight:700;letter-spacing:0;color:var(--mut)}
        .nf-day:after{content:"";flex:1;height:1px;background:var(--ln)}

        /* Item */
        .nf-item{position:relative;display:flex;gap:14px;padding:14px 18px;margin-bottom:10px;border:1px solid var(--ln);border-radius:20px;background:#fff;transition:.18s}
        .nf-item:hover{border-color:#e2cbd2;box-shadow:0 10px 26px rgba(58,26,36,.07)}
        .nf-item[hidden]{display:none}
        .nf-item.is-unread{border-color:#ecc9d3;background:linear-gradient(95deg,#fff1f5 0%,#fff 72%);box-shadow:0 8px 24px rgba(131,13,44,.07)}
        .nf-item.is-unread:before{content:"";position:absolute;left:-1px;top:20px;bottom:20px;width:4px;border-radius:0 4px 4px 0;background:var(--w)}
        .nf-ic{display:grid;place-items:center;flex:none;width:42px;height:42px;border-radius:14px;background:var(--in-bg);color:var(--in)}
        .nf-ic .cu-icon{width:23px;height:23px}
        .tone-success .nf-ic{background:var(--ok-bg);color:var(--ok)}
        .tone-warn .nf-ic{background:var(--wa-bg);color:var(--wa)}
        .tone-danger .nf-ic{background:var(--er-bg);color:var(--er)}
        .nf-body{flex:1;min-width:0}
        .nf-line{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .nf-title{display:flex;align-items:center;gap:10px;flex-wrap:wrap;min-width:0}
        .nf-title h3{margin:0;font-size:16.5px;letter-spacing:-.015em}
        .nf-item:not(.is-unread) .nf-title h3{font-weight:600}
        .nf-badge{padding:3px 10px;border-radius:999px;font-size:11.5px;font-weight:700;background:var(--in-bg);color:var(--in)}
        .tone-success .nf-badge{background:var(--ok-bg);color:var(--ok)}
        .tone-warn .nf-badge{background:var(--wa-bg);color:var(--wa)}
        .tone-danger .nf-badge{background:var(--er-bg);color:var(--er)}
        .nf-dot{display:none;width:9px;height:9px;border-radius:50%;background:var(--w);box-shadow:0 0 0 4px rgba(131,13,44,.14)}
        .nf-line time{flex:none;font-size:13px;color:var(--mut)}
        .nf-body p{margin:4px 0 10px;color:#4d4247;font-size:14px;line-height:1.55;overflow-wrap:anywhere}
        .nf-act{display:flex;align-items:center;flex-wrap:wrap;gap:10px}
        .nf-act form{margin:0}
        .nf-pill{display:inline-flex;align-items:center;min-height:34px;padding:0 15px;border-radius:999px;border:1px solid #e6d3d9;background:#fff;color:var(--w);font-weight:700;font-size:13px;cursor:pointer;transition:.18s}
        .nf-pill:hover{background:var(--w);border-color:var(--w);color:#fff}
        .nf-pill.ghost{border-color:transparent;background:transparent;color:var(--w)}
        .nf-pill.ghost:hover{background:#f5eff1;color:var(--ink)}

        /* Kosong */
        .nf-empty{display:grid;justify-items:center;gap:6px;padding:44px 24px 52px;text-align:center}
        .nf-empty[hidden]{display:none}
        .nf-empty-art{
            width:190px;height:140px;margin-bottom:8px;color:var(--w);
            display:block;border:0!important;outline:0!important;box-shadow:none!important;
            -webkit-tap-highlight-color:transparent;user-select:none;
        }
        .nf-empty-art:focus,.nf-empty-art:focus-visible,.nf-empty-art:active{
            border:0!important;outline:0!important;box-shadow:none!important;
        }
        .nf-empty-art .nf-empty-bell{
            transform-box:fill-box;transform-origin:50% 12%;
        }
        @keyframes nfEmptyBellSwing{
            0%,100%{transform:rotate(0deg)}
            18%{transform:rotate(-14deg)}
            36%{transform:rotate(12deg)}
            54%{transform:rotate(-9deg)}
            72%{transform:rotate(6deg)}
            88%{transform:rotate(-3deg)}
        }
        @media(hover:hover) and (pointer:fine){
            .nf-empty-art:hover .nf-empty-bell{animation:nfEmptyBellSwing .68s ease-in-out both}
        }
        .nf-empty-art.is-ringing .nf-empty-bell{animation:nfEmptyBellSwing .68s ease-in-out both}
        @media(prefers-reduced-motion:reduce){
            .nf-empty-art:hover .nf-empty-bell,.nf-empty-art.is-ringing .nf-empty-bell{animation:none!important}
        }
        .nf-empty h2{margin:0;font-size:22px;letter-spacing:-.02em}
        .nf-empty p{max-width:400px;margin:0 0 18px;color:var(--mut)}
        .nf-empty-act{display:flex;gap:10px;flex-wrap:wrap;justify-content:center}
        .nf-foot{padding:0 28px 22px}

        .nf a:focus-visible,.nf button:focus-visible{outline:2px solid var(--w);outline-offset:3px}
        @media(max-width:860px){.nf-stats{grid-template-columns:1fr}}
        @media(max-width:600px){
            .nf-hero{padding:26px 22px;border-radius:24px}.nf-hero-side{width:100%}.nf-hero .nf-btn{flex:1}
            .nf-panel-top,.nf-group,.nf-foot{padding-left:14px;padding-right:14px}
            .nf-chips{width:100%}.nf-chip{flex:1;padding:8px 6px}
            .nf-item{padding:16px 14px;gap:12px}.nf-ic{width:42px;height:42px;border-radius:14px}
                    }
             .nf-pager{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:18px 28px;border-top:1px solid var(--ln)}
.nf-pager-info{font-size:13px;color:var(--mut)}
.nf-pager-nav{display:flex;align-items:center;gap:8px}
.nf-pg{display:grid;place-items:center;min-width:40px;height:40px;padding:0 10px;border:1px solid var(--ln);border-radius:12px;background:#fff;color:var(--ink);font-weight:700;font-size:14px;transition:.18s}
a.nf-pg:hover{border-color:var(--w);color:var(--w);background:var(--w-soft)}
.nf-pg.is-on{background:var(--w);border-color:var(--w);color:#fff;box-shadow:0 6px 16px rgba(131,13,44,.3)}
.nf-pg.is-off{color:#c9bfc3;background:#fdfafb}
@media(max-width:600px){.nf-pager{padding:14px;justify-content:center}}


        /* Responsive refinement: desktop remains spacious, mobile gets a compact hierarchy */
        @media (min-width: 861px) {
            .nf { max-width: 100%; }
            .nf-hero { min-height: 108px; }
            .nf-stats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
        @media (min-width: 601px) and (max-width: 860px) {
            .nf-hero { padding: 20px 24px; }
            .nf-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .nf-stat:first-child { grid-column: 1 / -1; }
            .nf-panel-top { padding: 20px; }
            .nf-group { padding-left: 20px; padding-right: 20px; }
        }
        @media (max-width: 600px) {
            .nf {
                padding: 4px 0 28px;
                width: 100%;
                min-width: 0;
            }
            .nf-hero {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 66px;
                align-items: center;
                gap: 10px 12px;
                padding: 20px 18px;
                margin-bottom: 14px;
                min-height: 0;
                border-radius: 22px;
                box-shadow: 0 12px 28px rgba(110,10,38,.18);
            }
            .nf-hero > div:first-child { min-width: 0; }
            .nf .nf-hero h1 { font-size: 25px; margin-bottom: 5px; }
            .nf .nf-hero p { font-size: 13px; line-height: 1.45; max-width: 100%; }
            .nf-hero:before {
                width: 180px; height: 180px; left: -78px; bottom: -112px;
                border-width: 24px;
            }
            .nf-hero:after { width: 118px; height: 118px; right: -34px; top: -48px; }
            .nf-hero-side { display: contents; }
            .nf-art { grid-column: 2; grid-row: 1; width: 76px; height: auto; margin: 0; }
            .nf-ring {
                grid-column: 2;
                grid-row: 1;
                justify-self: end;
                width: 62px;
                height: 62px;
            }
            .nf-ring span { width: 50px; height: 50px; }
            .nf-ring b { font-size: 15px; }
            .nf-ring i { font-size: 9px; }
            .nf-hero-side form {
                grid-column: 1 / -1;
                width: 100%;
            }
            .nf-hero .nf-btn {
                width: 100%;
                min-height: 40px;
                padding: 0 12px;
                font-size: 12px;
            }

            /* Three metrics are arranged as one full-width total and two compact cards. */
            .nf-stats {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 14px;
            }
            .nf-stat {
                min-width: 0;
                gap: 10px;
                padding: 12px;
                border-radius: 17px;
                box-shadow: 0 5px 16px rgba(58,26,36,.045);
            }
            .nf-stat:first-child { grid-column: 1 / -1; }
            .nf-stat-ic {
                width: 42px;
                height: 42px;
                border-radius: 13px;
            }
            .nf-stat-ic .cu-icon { width: 21px; height: 21px; }
            .nf-stat small { font-size: 11.5px; line-height: 1.3; }
            .nf-stat strong { font-size: 22px; }
            .nf-bar { height: 4px; margin-top: 6px; }

            .nf-chat {
                gap: 10px;
                padding: 12px;
                margin-bottom: 14px;
                border-radius: 16px;
            }
            .nf-chat > div { min-width: 0; }
            .nf-chat p { font-size: 12.5px; line-height: 1.45; }

            .nf-panel { border-radius: 20px; }
            .nf-panel-top {
                display: block;
                padding: 17px 14px 14px;
            }
            .nf-panel-top h2 { font-size: 18px; }
            .nf-panel-top p { font-size: 12px; line-height: 1.45; }
            .nf-chips {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                width: 100%;
                gap: 3px;
                margin-top: 14px;
                padding: 4px;
                border-radius: 13px;
            }
            .nf-chip {
                min-width: 0;
                padding: 9px 3px;
                border-radius: 10px;
                font-size: 11.5px;
                line-height: 1.2;
                white-space: nowrap;
            }
            .nf-group { padding: 4px 11px 8px; }
            .nf-day { margin: 16px 2px 9px; font-size: 12px; }
            .nf-item {
                display: flex;
                align-items: flex-start;
                gap: 10px;
                padding: 13px 11px;
                margin-bottom: 8px;
                border-radius: 16px;
            }
            .nf-item.is-unread:before { top: 15px; bottom: 15px; }
            .nf-ic {
                width: 36px;
                height: 36px;
                border-radius: 11px;
            }
            .nf-ic .cu-icon { width: 20px; height: 20px; }
            .nf-body { min-width: 0; }
            .nf-line {
                display: flex;
                align-items: flex-start;
                flex-direction: column;
                gap: 4px;
            }
            .nf-title { gap: 7px; }
            .nf-title h3 { font-size: 14px; line-height: 1.35; }
            .nf-badge { padding: 3px 8px; font-size: 10.5px; }
            .nf-line time { font-size: 11px; }
            .nf-body p { margin: 6px 0 9px; font-size: 12.5px; line-height: 1.5; }
            .nf-act { gap: 7px; }
            .nf-pill { min-height: 32px; padding: 0 12px; font-size: 11.5px; }
            .nf-foot { padding: 0 12px 16px; }
            .nf-empty { padding: 34px 16px 38px; }
            .nf-empty-art { width: 150px; height: 112px; }
            .nf-empty h2 { font-size: 19px; }
            .nf-empty p { font-size: 13px; }
            .nf-pager {
                justify-content: center;
                gap: 9px;
                padding: 13px 10px;
            }
            .nf-pager-info { width: 100%; text-align: center; font-size: 11.5px; }
            .nf-pg { min-width: 36px; height: 36px; border-radius: 10px; }
        }
        @media (max-width: 360px) {
            .nf-hero { grid-template-columns: minmax(0, 1fr) 58px; padding: 17px 14px; }
            .nf-ring { width: 56px; height: 56px; }
            .nf-ring span { width: 46px; height: 46px; }
            .nf .nf-hero h1 { font-size: 23px; }
            .nf-stat { padding: 10px; gap: 8px; }
            .nf-stat-ic { width: 36px; height: 36px; }
            .nf-chip { font-size: 10.5px; }
        }

    
        /* Final refinement: heading is intentionally unboxed */
        .nf .nf-hero {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:22px;
            flex-wrap:wrap;
            min-height:0;
            margin:0 0 20px;
            padding:8px 0 18px;
            color:var(--ink);
            background:transparent;
            border:0;
            border-radius:0;
            box-shadow:none;
            overflow:visible;
        }
        .nf .nf-hero:before,
        .nf .nf-hero:after { display:none; }
        .nf .nf-hero > * { position:relative; z-index:1; }
        .nf .nf-hero h1 {
            color:#68152f;
            font-size:clamp(25px,3vw,32px);
            margin:0 0 6px;
            letter-spacing:-.035em;
        }
        .nf .nf-hero p {
            color:#78666d;
            max-width:560px;
            line-height:1.55;
        }
        .nf .nf-hero-side { gap:14px; }
        .nf .nf-ring {
            width:66px;
            height:66px;
            background:conic-gradient(#8b2442 calc(var(--p)*1%),#f0dfe5 0);
        }
        .nf .nf-ring span {
            width:54px;
            height:54px;
            background:#fff;
            color:#68152f;
        }
        .nf .nf-ring i { color:#8b737c; }
        .nf .nf-hero .nf-btn {
            min-height:42px;
            color:#68152f;
            background:#fff;
            border:1px solid #ead6dd;
            box-shadow:0 3px 12px rgba(65,28,42,.045);
        }
        .nf .nf-hero .nf-btn:hover {
            background:#fbf3f5;
            border-color:#d9b7c2;
        }
        @media (max-width:600px) {
            .nf .nf-hero {
                display:grid;
                grid-template-columns:minmax(0,1fr) 66px;
                align-items:center;
                gap:10px 12px;
                margin:0 0 16px;
                padding:12px 0 16px;
                border-radius:0;
                box-shadow:none;
                background:transparent;
            }
            .nf .nf-hero h1 { font-size:25px; }
            .nf .nf-hero p { font-size:13px; line-height:1.5; }
            .nf .nf-hero-side { display:contents; }
            .nf .nf-ring {
                grid-column:2;
                grid-row:1;
                justify-self:end;
                width:62px;
                height:62px;
            }
            .nf .nf-ring span { width:50px; height:50px; }
            .nf .nf-hero-side form {
                grid-column:1 / -1;
                width:100%;
            }
            .nf .nf-hero .nf-btn {
                width:100%;
                min-height:42px;
                border-radius:13px;
                font-size:12px;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .nf .nf-hero .nf-btn { transition:none; }
        }

    
        /* =========================================================
           FIND BACK - CLEAN NOTIFICATION LIST
           Final visual layer: simple, readable, responsive.
           ========================================================= */
        .nf {
            --w:#831333;
            --w2:#a52b50;
            --w-soft:#f9edf1;
            --ink:#282126;
            --mut:#7d7076;
            --ln:#eee7ea;
        }

        /* Heading: plain page heading, no colored banner or progress ring */
        .nf .nf-hero,
        .nf .nf-hero-clean {
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:18px;
            flex-wrap:wrap;
            min-height:0;
            margin:4px 0 20px;
            padding:8px 0 16px;
            color:var(--ink);
            background:transparent;
            border:0;
            border-radius:0;
            box-shadow:none;
            overflow:visible;
        }
        .nf .nf-hero:before,
        .nf .nf-hero:after { display:none; }
        .nf .nf-hero-copy { min-width:0; }
        .nf .nf-hero h1 {
            margin:0 0 5px;
            color:#68152f;
            font-size:clamp(25px,3vw,32px);
            line-height:1.2;
            letter-spacing:-.035em;
        }
        .nf .nf-hero p {
            max-width:620px;
            margin:0;
            color:#786b71;
            font-size:14px;
            line-height:1.55;
        }
        .nf .nf-hero form { margin:0; }
        .nf .nf-markall {
            min-height:38px;
            padding:0 13px;
            border:1px solid #ead9df;
            border-radius:10px;
            background:#fff;
            color:#831333;
            box-shadow:none;
            font-size:12.5px;
            font-weight:650;
        }
        .nf .nf-markall:hover {
            background:#fbf3f5;
            border-color:#d9b8c3;
        }
        .nf .nf-markall .cu-icon { width:16px;height:16px; }

        /* Compact summary cards */
        .nf .nf-stats {
            display:grid;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:12px;
            margin:0 0 20px;
        }
        .nf .nf-stat {
            min-width:0;
            padding:13px 15px;
            gap:12px;
            border:1px solid #eee6e9;
            border-radius:16px;
            background:#fff;
            box-shadow:0 4px 14px rgba(45,24,32,.035);
        }
        .nf .nf-stat:hover { box-shadow:0 7px 18px rgba(45,24,32,.055); }
        .nf .nf-stat-ic {
            width:42px;height:42px;
            border-radius:13px;
        }
        .nf .nf-stat-ic .cu-icon { width:20px;height:20px; }
        .nf .nf-stat small { font-size:12px; }
        .nf .nf-stat strong { font-size:22px; }
        .nf .nf-bar { height:4px;margin-top:6px; }

        /* Notification panel and filter */
        .nf .nf-panel {
            overflow:hidden;
            border:1px solid #eee7ea;
            border-radius:20px;
            background:#fff;
            box-shadow:0 8px 24px rgba(45,24,32,.035);
        }
        .nf .nf-panel-top {
            align-items:center;
            padding:19px 22px;
            background:#fff;
            border-bottom:1px solid #eee8eb;
        }
        .nf .nf-panel-top h2 { font-size:19px; }
        .nf .nf-panel-top p { font-size:12.5px; }
        .nf .nf-chips {
            gap:3px;
            padding:4px;
            border-radius:12px;
            background:#f6f1f3;
        }
        .nf .nf-chip {
            min-height:34px;
            padding:8px 14px;
            border-radius:9px;
            font-size:12px;
            font-weight:650;
        }
        .nf .nf-chip:hover { color:#831333;background:#fff8fa; }
        .nf .nf-chip.on {
            color:#831333;
            background:#fff;
            box-shadow:0 2px 7px rgba(45,24,32,.09);
        }

        /* Group headings */
        .nf .nf-group { padding:2px 22px 0; }
        .nf .nf-day {
            gap:12px;
            margin:19px 0 5px;
            color:#65575e;
            font-size:13px;
            font-weight:750;
        }
        .nf .nf-day:after { background:#eee8eb; }

        /* Clean list rows inspired by learning-platform notification feeds */
        .nf .nf-item,
        .nf .nf-item.is-unread {
            display:flex;
            align-items:flex-start;
            gap:14px;
            margin:0;
            padding:17px 8px;
            border:0;
            border-bottom:1px solid #f0ebed;
            border-radius:0;
            background:#fff;
            box-shadow:none;
            transition:background .16s ease;
        }
        .nf .nf-item:last-child { border-bottom:0; }
        .nf .nf-item:hover {
            border-color:#f0ebed;
            background:#fdf9fa;
            box-shadow:none;
        }
        .nf .nf-item.is-unread {
            background:#fffafb;
        }
        .nf .nf-item.is-unread:before { display:none; }
        .nf .nf-ic {
            width:44px;height:44px;
            flex:none;
            border-radius:50%;
            background:#f8edf1;
            color:#831333;
        }
        .nf .nf-ic .cu-icon { width:21px;height:21px; }
        .nf .tone-success .nf-ic { background:#e9f5ed;color:#187448; }
        .nf .tone-warn .nf-ic { background:#fff4df;color:#956000; }
        .nf .tone-danger .nf-ic { background:#fcecef;color:#b4233d; }
        .nf .nf-body { min-width:0; }
        .nf .nf-line {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:8px 14px;
        }
        .nf .nf-title {
            display:flex;
            align-items:center;
            gap:7px;
            min-width:0;
        }
        .nf .nf-title h3 {
            margin:0;
            color:#30262b;
            font-size:15px;
            line-height:1.4;
            font-weight:700;
            letter-spacing:-.012em;
        }
        .nf .nf-item:not(.is-unread) .nf-title h3 { font-weight:600; }
        .nf .nf-badge {
            padding:3px 8px;
            border-radius:999px;
            font-size:10.5px;
            font-weight:650;
            white-space:nowrap;
        }
        .nf .nf-dot {
            display:inline-block;
            flex:none;
            width:8px;height:8px;
            margin-left:2px;
            background:#b42345;
            box-shadow:0 0 0 3px #f9e9ee;
        }
        .nf .nf-line time {
            padding-top:2px;
            color:#91848a;
            font-size:11.5px;
            white-space:nowrap;
        }
        .nf .nf-body p {
            margin:4px 0 7px;
            color:#51464c;
            font-size:13.5px;
            line-height:1.5;
            overflow-wrap:anywhere;
        }
        .nf .nf-act { gap:8px; }
        .nf .nf-pill {
            min-height:29px;
            padding:0 11px;
            border-radius:8px;
            font-size:11.5px;
            font-weight:650;
        }
        .nf .nf-pill:not(.ghost) {
            border-color:#ead9df;
            color:#831333;
            background:#fff;
        }
        .nf .nf-pill:not(.ghost):hover {
            color:#fff;background:#831333;border-color:#831333;
        }
        .nf .nf-pill.ghost {
            min-height:29px;
            padding:0 7px;
            color:#8a2946;
        }
        .nf .nf-pill.ghost:hover { background:#f8edf1;color:#68152f; }

        .nf .nf-pager { padding:15px 22px; }
        .nf .nf-pg { border-radius:10px; }
        .nf .nf-empty { padding:38px 20px 44px; }

        /* Tablet */
        @media (min-width:601px) and (max-width:860px) {
            .nf .nf-stats { grid-template-columns:repeat(2,minmax(0,1fr)); }
            .nf .nf-stat:first-child { grid-column:1/-1; }
            .nf .nf-panel-top { padding:17px 18px; }
            .nf .nf-group { padding-left:18px;padding-right:18px; }
        }

        /* Mobile */
        @media (max-width:600px) {
            .nf { padding:5px 0 30px; }
            .nf .nf-hero {
                align-items:flex-start;
                gap:10px;
                margin:3px 0 14px;
                padding:8px 4px 13px;
            }
            .nf .nf-hero h1 { font-size:25px; }
            .nf .nf-hero p { max-width:100%;font-size:12.5px;line-height:1.5; }
            .nf .nf-markall {
                min-height:34px;
                padding:0 10px;
                font-size:11px;
            }
            .nf .nf-stats {
                grid-template-columns:repeat(2,minmax(0,1fr));
                gap:9px;
                margin-bottom:14px;
            }
            .nf .nf-stat:first-child { grid-column:1/-1; }
            .nf .nf-stat {
                padding:11px;
                gap:9px;
                border-radius:14px;
            }
            .nf .nf-stat-ic { width:38px;height:38px;border-radius:11px; }
            .nf .nf-stat small { font-size:11px; }
            .nf .nf-stat strong { font-size:20px; }
            .nf .nf-panel { border-radius:17px; }
            .nf .nf-panel-top {
                display:block;
                padding:15px 13px 13px;
            }
            .nf .nf-panel-top h2 { font-size:17px; }
            .nf .nf-panel-top p { font-size:11.5px; }
            .nf .nf-chips {
                display:grid;
                grid-template-columns:repeat(3,minmax(0,1fr));
                width:100%;
                margin-top:12px;
                gap:3px;
                padding:4px;
            }
            .nf .nf-chip {
                min-width:0;
                min-height:34px;
                padding:7px 2px;
                font-size:10.5px;
                white-space:nowrap;
            }
            .nf .nf-group { padding:0 12px; }
            .nf .nf-day { margin:16px 1px 3px;font-size:12px; }
            .nf .nf-item,
            .nf .nf-item.is-unread {
                gap:10px;
                padding:14px 2px;
            }
            .nf .nf-ic {
                width:38px;height:38px;
                border-radius:50%;
            }
            .nf .nf-ic .cu-icon { width:19px;height:19px; }
            .nf .nf-line {
                display:flex;
                flex-direction:column;
                align-items:flex-start;
                gap:3px;
            }
            .nf .nf-title { flex-wrap:wrap;gap:6px; }
            .nf .nf-title h3 { font-size:13.5px;line-height:1.35; }
            .nf .nf-badge { font-size:9.5px;padding:2px 7px; }
            .nf .nf-line time { font-size:10.5px; }
            .nf .nf-body p { margin:4px 0 7px;font-size:12px;line-height:1.45; }
            .nf .nf-act { gap:5px; }
            .nf .nf-pill { min-height:28px;padding:0 9px;font-size:10.5px; }
            .nf .nf-pager { padding:12px 8px; }
            .nf .nf-pager-info { font-size:10.5px; }
            .nf .nf-pg { min-width:33px;height:33px; }
            .nf .nf-chat { padding:11px;border-radius:13px; }
        }
        @media (max-width:360px) {
            .nf .nf-hero { flex-direction:column;align-items:flex-start; }
            .nf .nf-markall { align-self:flex-start; }
            .nf .nf-stat { padding:9px;gap:7px; }
            .nf .nf-stat-ic { width:34px;height:34px; }
            .nf .nf-chip { font-size:9.5px; }
            .nf .nf-item { gap:8px; }
            .nf .nf-ic { width:34px;height:34px; }
        }
        @media (prefers-reduced-motion:reduce) {
            .nf .nf-item,.nf .nf-markall { transition:none; }
        }

    
        /* Final spacing refinement: compact action row and notification section */
        .nf .nf-hero.nf-hero-clean {
            align-items:center;
            margin:2px 0 14px;
            padding:5px 0 10px;
            gap:12px;
        }
        .nf .nf-hero-copy { flex:1 1 240px; }
        .nf .nf-markall { white-space:nowrap; }

        .nf .nf-panel-top {
            min-height:0;
            padding:17px 22px;
        }
        .nf .nf-panel-top h2 { margin:0; }

        @media (max-width:600px) {
            .nf .nf-hero.nf-hero-clean {
                display:grid;
                grid-template-columns:minmax(0,1fr) auto;
                align-items:center;
                gap:4px 8px;
                margin:0 0 10px;
                padding:3px 2px 8px;
            }
            .nf .nf-hero-copy {
                display:contents;
            }
            .nf .nf-hero h1 {
                grid-column:1;
                grid-row:1;
                margin:0;
                font-size:24px;
            }
            .nf .nf-hero p {
                grid-column:1 / -1;
                grid-row:2;
                margin:0;
                font-size:12px;
                line-height:1.4;
            }
            .nf .nf-hero form {
                grid-column:2;
                grid-row:1;
                justify-self:end;
            }
            .nf .nf-markall {
                min-height:32px;
                padding:0 9px;
                gap:5px;
                border-radius:9px;
                font-size:10.5px;
            }
            .nf .nf-markall .cu-icon { width:14px;height:14px; }

            /* Remove the extra subtitle row and tighten the filter header */
            .nf .nf-panel-top {
                padding:13px 12px 11px;
            }
            .nf .nf-panel-top h2 {
                font-size:16.5px;
                line-height:1.25;
            }
            .nf .nf-panel-top > div:first-child p { display:none; }
            .nf .nf-chips { margin-top:9px; }
            .nf .nf-stats { margin-bottom:11px; }
        }
        @media (max-width:360px) {
            .nf .nf-hero.nf-hero-clean {
                grid-template-columns:minmax(0,1fr);
                gap:7px;
            }
            .nf .nf-hero h1,
            .nf .nf-hero p,
            .nf .nf-hero form {
                grid-column:1;
            }
            .nf .nf-hero form { grid-row:3;justify-self:start; }
        }

    
        /* FINAL: compact notification summary + underline tabs */
        .nf .nf-summary {
            display:flex;
            align-items:center;
            flex-wrap:wrap;
            gap:7px;
            margin:0 0 17px;
            padding:0 1px 13px;
            border-bottom:1px solid #eee7ea;
            color:#776a70;
            font-size:13px;
            line-height:1.4;
        }
        .nf .nf-summary strong {
            color:#30252b;
            font-size:14px;
            font-weight:750;
            font-variant-numeric:tabular-nums;
        }
        .nf .nf-summary-sep { color:#c7b9bf; }
        .nf .nf-summary-unread strong { color:#a52249; }
        .nf .nf-summary-read strong { color:#187448; }

        .nf .nf-panel-top {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            padding:16px 22px 0;
            border-bottom:0;
        }
        .nf .nf-panel-top h2 {
            margin:0;
            font-size:18px;
        }
        .nf .nf-chips {
            display:flex;
            align-items:stretch;
            gap:22px;
            padding:0;
            margin:0;
            border:0;
            border-radius:0;
            background:transparent;
        }
        .nf .nf-chip {
            position:relative;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            min-height:40px;
            padding:8px 1px 10px;
            border:0;
            border-radius:0;
            background:transparent;
            color:#786a71;
            box-shadow:none;
            font-size:12.5px;
            font-weight:600;
            white-space:nowrap;
        }
        .nf .nf-chip:hover,
        .nf .nf-chip.on {
            color:#831333;
            background:transparent;
            box-shadow:none;
        }
        .nf .nf-chip.on:after {
            content:"";
            position:absolute;
            right:0;
            bottom:0;
            left:0;
            height:2.5px;
            border-radius:3px 3px 0 0;
            background:#831333;
        }
        .nf .nf-chip:focus-visible {
            outline:2px solid #c9879e;
            outline-offset:3px;
        }
        .nf .nf-group { padding-top:0; }

        @media (max-width:860px) {
            .nf .nf-panel-top {
                align-items:flex-start;
                flex-direction:column;
                gap:5px;
                padding:14px 14px 0;
            }
            .nf .nf-chips {
                display:grid;
                grid-template-columns:repeat(3,minmax(0,1fr));
                width:100%;
                gap:14px;
            }
            .nf .nf-chip {
                min-width:0;
                min-height:39px;
                padding:8px 1px 9px;
                font-size:11px;
            }
        }
        @media (max-width:600px) {
            .nf .nf-summary {
                gap:5px;
                margin:0 0 12px;
                padding:0 2px 10px;
                font-size:11.5px;
            }
            .nf .nf-summary strong { font-size:12px; }
            .nf .nf-panel-top {
                padding:12px 12px 0;
                gap:3px;
            }
            .nf .nf-panel-top h2 { font-size:16px; }
            .nf .nf-chips { gap:10px; }
            .nf .nf-chip {
                min-height:36px;
                font-size:10.5px;
                font-weight:650;
            }
        }
        @media (max-width:360px) {
            .nf .nf-summary { font-size:10.5px;gap:4px; }
            .nf .nf-summary strong { font-size:11px; }
            .nf .nf-chips { gap:6px; }
            .nf .nf-chip { font-size:9.5px; }
        }

    
        /* Notification actions: right aligned on the same row */
        .nf .nf-item {
            display:flex;
            align-items:center;
            gap:12px;
        }
        .nf .nf-body {
            flex:1 1 auto;
            min-width:0;
        }
        .nf .nf-act {
            flex:0 0 auto;
            display:flex;
            flex-direction:column;
            align-items:flex-end;
            justify-content:center;
            gap:5px;
            margin-left:auto;
            padding-left:4px;
        }
        .nf .nf-act form { margin:0; }
        .nf .nf-act .nf-pill {
            min-height:31px;
            padding:0 11px;
            white-space:nowrap;
            font-size:11px;
        }
        .nf .nf-act .nf-pill.ghost { min-height:27px; }

        @media (max-width:600px) {
            .nf .nf-item,
            .nf .nf-item.is-unread {
                align-items:center;
                gap:9px;
                padding:13px 2px;
            }
            .nf .nf-body { flex:1 1 0%; }
            .nf .nf-line {
                display:flex;
                flex-direction:column;
                align-items:flex-start;
                gap:2px;
            }
            .nf .nf-title h3 { font-size:13px; }
            .nf .nf-body p {
                margin:4px 0 0;
                font-size:11.5px;
                line-height:1.4;
            }
            .nf .nf-act {
                flex:0 0 auto;
                align-items:flex-end;
                gap:3px;
                margin-left:0;
                padding-left:0;
            }
            .nf .nf-act .nf-pill {
                min-height:29px;
                padding:0 8px;
                border-radius:8px;
                font-size:10px;
            }
            .nf .nf-act .nf-pill.ghost {
                min-height:25px;
                padding:0 5px;
                font-size:9.5px;
            }
        }
        @media (max-width:370px) {
            .nf .nf-item,
            .nf .nf-item.is-unread { gap:7px; }
            .nf .nf-ic { width:32px;height:32px; }
            .nf .nf-act .nf-pill {
                min-height:27px;
                padding:0 6px;
                font-size:9px;
            }
            .nf .nf-title h3 { font-size:12px; }
        }

    
        /* =========================================================
           FINAL RESPONSIVE ACTIONS
           - Mark-all appears only when unread notifications exist (Blade condition).
           - Per-item "Tandai dibaca" appears only on unread items (Blade condition).
           - Desktop actions share one tidy row; mobile actions stack compactly.
           ========================================================= */
        .nf .nf-hero.nf-hero-clean {
            width:100%;
            align-items:center;
            justify-content:space-between;
            gap:14px 24px;
        }
        .nf .nf-hero-copy {
            flex:1 1 360px;
            min-width:0;
        }
        .nf .nf-markall {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            min-height:38px;
            white-space:nowrap;
            line-height:1;
        }

        /* Desktop: keep the two actions on one aligned line. */
        @media (min-width:861px) {
            .nf .nf-panel {
                width:100%;
            }
            .nf .nf-panel-top {
                padding-left:24px;
                padding-right:24px;
            }
            .nf .nf-group {
                padding-left:24px;
                padding-right:24px;
            }
            .nf .nf-item,
            .nf .nf-item.is-unread {
                align-items:center;
                gap:16px;
                padding:17px 8px;
            }
            .nf .nf-body {
                flex:1 1 auto;
                min-width:0;
            }
            .nf .nf-line {
                align-items:center;
                flex-wrap:nowrap;
            }
            .nf .nf-title { min-width:0; }
            .nf .nf-line time {
                margin-left:auto;
                padding-left:12px;
            }
            .nf .nf-act {
                flex:0 0 auto;
                flex-direction:row;
                align-items:center;
                justify-content:flex-end;
                gap:7px;
                margin-left:10px;
                padding:0;
            }
            .nf .nf-act .nf-pill {
                min-height:32px;
                padding:0 12px;
                font-size:11.5px;
                white-space:nowrap;
            }
            .nf .nf-act .nf-pill.ghost {
                min-height:30px;
                padding:0 9px;
            }
        }

        /* Tablet: maintain a compact row without squeezing the content. */
        @media (min-width:601px) and (max-width:860px) {
            .nf .nf-hero.nf-hero-clean {
                align-items:flex-start;
            }
            .nf .nf-item,
            .nf .nf-item.is-unread {
                align-items:center;
                gap:11px;
            }
            .nf .nf-act {
                flex-direction:column;
                align-items:flex-end;
                gap:3px;
                margin-left:4px;
            }
            .nf .nf-act .nf-pill {
                min-height:30px;
                padding:0 9px;
                font-size:10.5px;
                white-space:nowrap;
            }
        }

        /* Mobile: title and mark-all share a line; description sits below.
           Actions remain in a narrow right column, avoiding large blank rows. */
        @media (max-width:600px) {
            .nf .nf-hero.nf-hero-clean {
                display:grid;
                grid-template-columns:minmax(0,1fr) auto;
                align-items:center;
                gap:5px 8px;
                margin:0 0 12px;
                padding:3px 2px 9px;
            }
            .nf .nf-hero-copy { display:contents; }
            .nf .nf-hero h1 {
                grid-column:1;
                grid-row:1;
                min-width:0;
                font-size:24px;
            }
            .nf .nf-hero p {
                grid-column:1 / -1;
                grid-row:2;
                max-width:none;
                font-size:12px;
                line-height:1.45;
            }
            .nf .nf-hero form {
                grid-column:2;
                grid-row:1;
                justify-self:end;
                width:auto;
            }
            .nf .nf-markall {
                min-height:34px;
                padding:0 10px;
                border-radius:9px;
                font-size:10.5px;
                gap:5px;
            }
            .nf .nf-markall .cu-icon { width:14px;height:14px; }

            .nf .nf-item,
            .nf .nf-item.is-unread {
                display:flex;
                align-items:center;
                gap:9px;
                padding:13px 2px;
            }
            .nf .nf-ic {
                flex:0 0 36px;
                width:36px;
                height:36px;
            }
            .nf .nf-body {
                flex:1 1 0%;
                min-width:0;
            }
            .nf .nf-line {
                display:flex;
                flex-direction:column;
                align-items:flex-start;
                gap:3px;
            }
            .nf .nf-title {
                display:flex;
                align-items:center;
                flex-wrap:wrap;
                gap:5px;
            }
            .nf .nf-title h3 { font-size:13px;line-height:1.35; }
            .nf .nf-badge { font-size:9.5px; }
            .nf .nf-line time { font-size:10.5px; }
            .nf .nf-body p {
                margin:4px 0 0;
                font-size:11.5px;
                line-height:1.4;
            }
            .nf .nf-act {
                flex:0 0 auto;
                display:flex;
                flex-direction:column;
                align-items:flex-end;
                justify-content:center;
                gap:2px;
                margin:0 0 0 2px;
                padding:0;
            }
            .nf .nf-act .nf-pill {
                min-height:28px;
                padding:0 7px;
                border-radius:8px;
                font-size:9.5px;
                white-space:nowrap;
            }
            .nf .nf-act .nf-pill.ghost {
                min-height:24px;
                padding:0 5px;
                font-size:9px;
            }
        }

        @media (max-width:370px) {
            .nf .nf-hero.nf-hero-clean {
                grid-template-columns:minmax(0,1fr);
                gap:6px;
            }
            .nf .nf-hero h1,
            .nf .nf-hero p,
            .nf .nf-hero form {
                grid-column:1;
            }
            .nf .nf-hero h1 { grid-row:1; }
            .nf .nf-hero p { grid-row:2; }
            .nf .nf-hero form {
                grid-row:3;
                justify-self:start;
            }
            .nf .nf-item,
            .nf .nf-item.is-unread { gap:7px; }
            .nf .nf-ic { flex-basis:32px;width:32px;height:32px; }
            .nf .nf-title h3 { font-size:12px; }
            .nf .nf-act .nf-pill { font-size:9px;padding:0 5px; }
        }

        @media (prefers-reduced-motion:reduce) {
            .nf .nf-markall,
            .nf .nf-act .nf-pill,
            .nf .nf-item { transition:none; }
        }

    
        /* Read state is controlled by the small unread marker beside the status badge.
           Keep the action column reserved for the single primary action: Lihat aktivitas. */
        .nf .nf-read-indicator-form {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            flex:none;
            margin:0;
            padding:0;
            line-height:0;
        }
        .nf .nf-read-indicator {
            display:inline-block;
            flex:none;
            width:9px;
            height:9px;
            min-width:9px;
            margin:0 2px;
            padding:0;
            border:0;
            border-radius:50%;
            background:#b42345;
            box-shadow:0 0 0 3px #f9e9ee;
            cursor:pointer;
            transition:transform .18s ease, opacity .18s ease, box-shadow .18s ease;
        }
        .nf .nf-read-indicator:hover {
            transform:scale(1.22);
            box-shadow:0 0 0 5px #f6e0e7;
        }
        .nf .nf-read-indicator:focus-visible {
            outline:2px solid #831333;
            outline-offset:5px;
        }
        .nf .nf-read-indicator-form.is-read-confirmed {
            opacity:0;
            transform:scale(.55);
            transition:opacity .18s ease, transform .18s ease;
        }
        .nf .nf-item,
        .nf .nf-item.is-unread {
            transition:background-color .2s ease, border-color .2s ease;
        }
        .nf .nf-act {
            flex-direction:row;
            align-items:center;
            justify-content:flex-end;
        }
        .nf .nf-act .nf-pill.ghost { display:none; }
        @media (max-width:600px) {
            .nf .nf-act { flex-direction:column; }
            .nf .nf-read-indicator { width:9px;height:9px;min-width:9px; }
        }
        @media (prefers-reduced-motion:reduce) {
            .nf .nf-read-indicator,
            .nf .nf-read-indicator-form.is-read-confirmed,
            .nf .nf-item,
            .nf .nf-item.is-unread { transition:none; }
        }

    
        /* Desktop: notification time follows the message on the left,
           matching the reading flow on mobile. Keep the primary action at right. */
        @media (min-width: 601px) {
            .nf .nf-body {
                display:grid;
                grid-template-columns:minmax(0, 1fr);
                min-width:0;
                align-content:center;
            }
            .nf .nf-line {
                display:contents;
            }
            .nf .nf-title {
                grid-row:1;
                min-width:0;
            }
            .nf .nf-body > p {
                grid-row:2;
                margin:5px 0 3px;
            }
            .nf .nf-line > time {
                grid-row:3;
                justify-self:start;
                margin:0;
                font-size:12px;
                line-height:1.35;
                color:var(--mut);
                white-space:nowrap;
            }
            .nf .nf-act {
                align-self:center;
            }
        }

    
        /* Make the complete notification row a single navigation target.
           The unread dot remains above the stretched link so it can still mark as read. */
        .nf .nf-item-link {
            position:absolute;
            inset:0;
            z-index:1;
            display:block;
            border-radius:inherit;
            text-decoration:none;
            cursor:pointer;
        }
        .nf .nf-item-link:focus-visible {
            outline:3px solid rgba(131,13,44,.55);
            outline-offset:3px;
        }
        .nf .nf-read-indicator-form {
            position:relative;
            z-index:2;
        }
        .nf .nf-read-indicator {
            cursor:pointer;
        }
        .nf .nf-item:hover .nf-item-link {
            background:rgba(131,13,44,.012);
        }

    

        /* Status baca: tanpa titik merah kecil. Notifikasi belum dibaca
           tetap dibedakan secara halus melalui latar dan bobot judul. */
        .nf .nf-dot,
        .nf .nf-read-indicator-form { display:none !important; }
        .nf .nf-item.is-unread { background:#fffafb; }
        .nf .nf-item.is-unread .nf-title h3 { font-weight:750; }
        .nf .nf-item-link { z-index:3; }

        /* FINAL USER NOTIFICATION PATCH
           - No hover tint on notification rows.
           - Unread/read colors are state-based only.
           - Mobile pagination controls align to the bottom-right like desktop.
        */
        .nf .nf-item,
        .nf .nf-item:hover {
            transition: none !important;
        }
        .nf .nf-item.is-unread,
        .nf .nf-item.is-unread:hover {
            background: #fffafb !important;
        }
        .nf .nf-item:not(.is-unread),
        .nf .nf-item:not(.is-unread):hover {
            background: #fff !important;
        }
        .nf .nf-item:hover .nf-item-link {
            background: transparent !important;
        }
        .nf .nf-pager {
            justify-content: space-between;
        }
        .nf .nf-pager-nav {
            margin-left: auto;
            justify-content: flex-end;
        }
        @media (max-width: 600px) {
            .nf .nf-pager {
                display: flex;
                flex-direction: row;
                justify-content: flex-end;
                align-items: center;
                gap: 10px;
                padding: 13px 12px;
            }
            .nf .nf-pager-info {
                flex: 1 0 100%;
                width: 100%;
                text-align: center;
            }
            .nf .nf-pager-nav {
                width: auto;
                margin-left: auto;
                justify-content: flex-end;
            }
        }

        /* Pagination navigation uses the browser's native fragment positioning.
           Disable smooth scrolling here to avoid animated jumps/flicker on page changes. */
        html:has(.nf) {
            scroll-behavior: auto !important;
        }
        .nf .nf-pager {
            scroll-margin-top: 112px;
            scroll-behavior: auto !important;
        }

        /* Posisi filter: desktop di kanan, mobile sebagai grup di tengah. */
        @media (min-width: 861px) {
            .nf .nf-panel-top {
                display: flex !important;
                align-items: center !important;
                justify-content: space-between !important;
            }
            .nf .nf-panel-top > div:first-child {
                flex: 1 1 auto;
                min-width: 0;
            }
            .nf .nf-chips {
                width: min(100%, 330px) !important;
                margin: 0 0 0 auto !important;
                justify-self: end;
            }
        }
        @media (max-width: 860px) {
            .nf .nf-panel-top {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
            }
            .nf .nf-chips {
                width: min(100%, 320px) !important;
                margin: 10px auto 0 !important;
                align-self: center !important;
                justify-self: center;
            }
        }
        /* Minimal premium action: compact text action instead of a large button. */
        .nf .nf-hero .nf-markall {
            min-height:40px !important;
            padding:8px 4px !important;
            gap:7px !important;
            border:0 !important;
            border-radius:8px !important;
            background:transparent !important;
            color:#831333 !important;
            box-shadow:none !important;
            font-size:12px !important;
            font-weight:700 !important;
            letter-spacing:.005em;
            transition:color .16s ease, opacity .16s ease !important;
        }
        .nf .nf-hero .nf-markall:hover {
            background:transparent !important;
            border-color:transparent !important;
            color:#a3173f !important;
            box-shadow:none !important;
        }
        .nf .nf-hero .nf-markall:focus-visible {
            outline:2px solid rgba(131,19,51,.35);
            outline-offset:3px;
        }
        .nf .nf-hero .nf-markall .cu-icon {
            width:15px !important;
            height:15px !important;
            flex:none;
        }
        @media (max-width:600px) {
            .nf .nf-hero.nf-hero-clean {
                grid-template-columns:minmax(0,1fr) auto !important;
                gap:5px 8px !important;
            }
            .nf .nf-hero form {
                grid-column:2 !important;
                grid-row:1 !important;
                justify-self:end !important;
                width:auto !important;
            }
            .nf .nf-hero .nf-markall {
                min-height:38px !important;
                padding:7px 2px !important;
                gap:5px !important;
                font-size:10.5px !important;
            }
            .nf .nf-hero .nf-markall .cu-icon {
                width:14px !important;
                height:14px !important;
            }
        }
        @media (max-width:370px) {
            .nf .nf-hero.nf-hero-clean {
                grid-template-columns:minmax(0,1fr) auto !important;
                gap:5px 6px !important;
            }
            .nf .nf-hero h1 { grid-column:1 !important;grid-row:1 !important; }
            .nf .nf-hero p { grid-column:1 / -1 !important;grid-row:2 !important; }
            .nf .nf-hero form { grid-column:2 !important;grid-row:1 !important; }
            .nf .nf-hero .nf-markall { font-size:9.5px !important;gap:4px !important; }
        }
</style>
    

    <div class="nf">
        <header class="nf-hero nf-hero-clean">
            <div class="nf-hero-copy">
                <h1>Notifikasi</h1>
                <p>Informasi terbaru tentang laporan, klaim, dan aktivitas akunmu.</p>
            </div>
            @if ($totalUnread > 0)
                <form method="POST" data-nf-form="all" action="{{ route('user.notifications.readAll') }}">
                    @csrf
                    <button class="nf-btn nf-markall" type="submit">
                        @include('user.components.icon', ['icon' => 'check'])
                        Tandai semua dibaca
                    </button>
                </form>
            @endif
        </header>

        <div class="nf-summary" aria-label="Ringkasan notifikasi">
            <span><strong data-nf="total">{{ $totalAll }}</strong> notifikasi</span>
            <span class="nf-summary-sep" aria-hidden="true">·</span>
            <span class="nf-summary-unread"><strong data-nf="unread">{{ $totalUnread }}</strong> belum dibaca</span>
        </div>

        @if ($chatUnread)
            <a class="nf-chat" href="{{ route('user.chat.index') }}">
                <span class="nf-chat-ic">@include('user.components.icon', ['icon' => 'chat'])</span>
                <span><strong>{{ $chatUnread }} pesan admin belum dibaca.</strong> Buka chat klaim untuk membalas.</span>
            </a>
        @endif

        <section class="nf-panel" id="nf-panel">
            <div class="nf-panel-top">
                <div>
                    <h2>Semua notifikasi</h2>
                </div>
                @if ($totalAll > 0)
                    <nav class="nf-chips" aria-label="Filter notifikasi">
                        <a class="nf-chip nf-filter-link {{ $filter === 'all' ? 'on' : '' }}"
                            href="{{ route('user.notifications.index', ['filter' => 'all']) }}" data-filter="all" @if($filter === 'all') aria-current="page" @endif>Semua</a>
                        <a class="nf-chip nf-filter-link {{ $filter === 'unread' ? 'on' : '' }}"
                            href="{{ route('user.notifications.index', ['filter' => 'unread']) }}" data-filter="unread" @if($filter === 'unread') aria-current="page" @endif>Belum dibaca</a>
                    </nav>
                @endif
            </div>

            @forelse ($groups as $label => $items)
                <div class="nf-group">
                    <h3 class="nf-day">{{ $label }}</h3>
                    @foreach ($items as $notification)
                        @php
                            $tone = $toneOf($notification);
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        <article class="nf-item tone-{{ $tone }} {{ $isUnread ? 'is-unread' : '' }}"
                            data-state="{{ $isUnread ? 'unread' : 'read' }}">
                            <span class="nf-ic" aria-hidden="true">@include('user.components.icon', ['icon' => $iconOf[$tone]])</span>
                            <div class="nf-body">
                                <div class="nf-line">
                                    <div class="nf-title">
                                        <h3>{{ \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Pemberitahuan') }}</h3>
                                        <span class="nf-badge">{{ $badgeOf[$tone] }}</span>
                                    </div>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}"
                                        title="{{ $notification->created_at->timezone('Asia/Jakarta')->locale('id')->translatedFormat('j M Y, H:i') }}">
                                        {{ $notification->created_at->locale('id')->diffForHumans() }}
                                    </time>
                                </div>
                                <p>{{ \App\Support\WorkflowStatus::notificationText($notification->data['message'] ?? '') }}</p>
                            </div>
                            <a class="nf-item-link" href="{{ route('user.notifications.open', $notification->id) }}"
                                aria-label="Buka notifikasi dan tandai sudah dibaca: {{ \App\Support\WorkflowStatus::notificationText($notification->data['title'] ?? 'Pemberitahuan') }}"></a>
                        </article>
                    @endforeach
                </div>
            @empty
                <div class="nf-empty">
                    <svg class="nf-empty-art" viewBox="0 0 190 140" fill="none" aria-hidden="true" focusable="false">
                        <ellipse cx="95" cy="124" rx="50" ry="7" fill="#830d2c" fill-opacity=".08"/>
                        <circle cx="95" cy="64" r="58" fill="#fbeff2"/>
                        <circle cx="95" cy="64" r="42" fill="#f6dfe5"/>
                        <g class="nf-empty-bell">
                            <path d="M95 34a21 21 0 0 0-21 21v12l-7 12h56l-7-12V55a21 21 0 0 0-21-21Z" fill="#830d2c"/>
                            <path d="M85 83a10 10 0 0 0 20 0Z" fill="#a3173f"/>
                            <rect x="92" y="26" width="6" height="9" rx="3" fill="#830d2c"/>
                        </g>
                        <path d="M150 28v12M144 34h12" stroke="#e0436b" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="38" cy="40" r="4" fill="#e0436b" fill-opacity=".45"/>
                        <circle cx="156" cy="96" r="5" fill="#830d2c" fill-opacity=".18"/>
                        <path d="M34 92v10M29 97h10" stroke="#830d2c" stroke-opacity=".35" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <h2>Belum ada notifikasi</h2>
                    <p>Saat status laporan atau klaimmu berubah, kabarnya muncul di sini.</p>
                    <div class="nf-empty-act">
                        <a class="nf-btn solid" href="{{ route('user.reports.index') }}">Buka aktivitas saya</a>
                    </div>
                </div>
            @endforelse

            <div class="nf-empty" id="nf-filter-empty" hidden>
                <svg class="nf-empty-art" viewBox="0 0 190 140" fill="none" aria-hidden="true"><circle cx="95" cy="64" r="58" fill="#fbeff2"/><circle cx="95" cy="64" r="42" fill="#e4f4ea"/><circle cx="95" cy="64" r="24" fill="#14663f"/><path d="m84 64 8 8 15-16" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <h2 id="nf-filter-empty-title">Tidak ada notifikasi</h2>
                <p id="nf-filter-empty-text"></p>
            </div>

            @if ($notifications->total() > 0)
                <div class="nf-pager" id="nf-pager">
                    <span class="nf-pager-info">Menampilkan {{ $notifications->firstItem() }}–{{ $notifications->lastItem() }} dari {{ $notifications->total() }} notifikasi</span>
                    <nav class="nf-pager-nav" aria-label="Halaman notifikasi">
                        @if ($notifications->onFirstPage())
                            <span class="nf-pg is-off" aria-disabled="true">‹</span>
                        @else
                            <a class="nf-pg" href="{{ $notifications->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">‹</a>
                        @endif

                        @foreach ($notifications->getUrlRange(max(1, $notifications->currentPage() - 2), min($notifications->lastPage(), $notifications->currentPage() + 2)) as $page => $url)
                            @if ($page == $notifications->currentPage())
                                <span class="nf-pg is-on" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="nf-pg" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($notifications->hasMorePages())
                            <a class="nf-pg" href="{{ $notifications->nextPageUrl() }}" rel="next" aria-label="Berikutnya">›</a>
                        @else
                            <span class="nf-pg is-off" aria-disabled="true">›</span>
                        @endif
                    </nav>
                </div>
            @endif
        </section>
    </div>

<script>
        /* Filter tab dan pagination memakai hasil query server agar total item akurat. */
</script>

    <script>
        /* Ilustrasi lonceng: animasikan hanya loncengnya, tanpa kotak fokus di sekeliling SVG. */
        (function () {
            var art = document.querySelector('.nf-empty-art');
            if (!art) return;
            art.addEventListener('click', function () {
                art.classList.remove('is-ringing');
                void art.getBoundingClientRect();
                art.classList.add('is-ringing');
                window.setTimeout(function () { art.classList.remove('is-ringing'); }, 720);
            });
        })();
    </script>

    <script>
        /* Tandai dibaca tanpa memuat ulang halaman, jadi posisi scroll tidak melompat */
        (function () {
            var root = document.querySelector('.nf');
            if (!root) return;
            var num = function (k) { var e = root.querySelector('[data-nf="' + k + '"]'); return e ? parseInt(e.textContent, 10) || 0 : 0; };
            var put = function (k, v) { var e = root.querySelector('[data-nf="' + k + '"]'); if (e) e.textContent = v; };

            function refresh(unread) {
                var total = num('total'), read = total - unread;
                var pct = total ? Math.round(read / total * 100) : 0;
                put('unread', unread); put('read', read);
                var w = { total: total ? 100 : 0, unread: total ? 100 - pct : 0, read: pct };
                Object.keys(w).forEach(function (k) {
                    var b = root.querySelector('[data-nf-bar="' + k + '"]'); if (b) b.style.width = w[k] + '%';
                });
                // Filter sekarang berbasis query server; jangan memicu klik tab dari sini.
                var allForm = root.querySelector('[data-nf-form="all"]'); if (allForm && unread === 0) allForm.hidden = true;
                var bell = document.querySelector('.cu-bell');
                if (bell) {
                    var badge = bell.querySelector('b');
                    if (unread > 0) { if (badge) badge.textContent = Math.min(unread, 99); }
                    else if (badge) badge.remove();
                    bell.setAttribute('aria-label', 'Notifikasi, ' + unread + ' belum dibaca');
                }
            }

            function markRead(item) {
                if (!item || !item.classList.contains('is-unread')) return false;
                item.classList.remove('is-unread');
                item.dataset.state = 'read';
                var f = item.querySelector('[data-nf-form="one"]');
                if (f) {
                    f.classList.add('is-read-confirmed');
                    window.setTimeout(function () { f.remove(); }, 190);
                }
                return true;
            }

            root.addEventListener('submit', function (e) {
                var form = e.target.closest('[data-nf-form]');
                if (!form) return;
                e.preventDefault();
                var kind = form.dataset.nfForm, btn = form.querySelector('button');
                if (btn) btn.disabled = true;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    credentials: 'same-origin',
                    redirect: 'manual'
                }).then(function (r) {
                    if (!(r.ok || r.type === 'opaqueredirect' || (r.status >= 300 && r.status < 400))) throw new Error('gagal');
                    var unread = num('unread');
                    if (kind === 'one') {
                        if (markRead(form.closest('.nf-item'))) unread = Math.max(0, unread - 1);
                    } else {
                        root.querySelectorAll('.nf-item.is-unread').forEach(markRead);
                        unread = 0;
                    }
                    refresh(unread);
                }).catch(function () {
                    if (btn) btn.disabled = false;
                    form.removeAttribute('data-nf-form');   // cadangan: kirim biasa jika AJAX gagal
                    form.submit();
                });
            });
        })();
    </script>

    <style>
        /* Prevent the browser from applying its own scroll anchoring while
           the notification list is replaced during pagination. */
        .nf,
        .nf-panel {
            overflow-anchor: none !important;
        }
        html {
            scroll-behavior: auto !important;
        }
    
        /* Pagination mobile: info text rata kanan, sejajar dengan sisi kanan tombol. */
        @media (max-width: 600px) {
            .nf .nf-panel {
                overflow-anchor: none !important;
                height: auto !important;
                min-height: 0 !important;
            }
            .nf .nf-pager {
                justify-content: flex-end !important;
                align-items: flex-end !important;
                flex-wrap: wrap;
                gap: 10px;
            }
            .nf .nf-pager-info {
                display: block;
                flex: 1 0 100%;
                width: 100%;
                text-align: right !important;
                margin: 0;
            }
            .nf .nf-pager-nav {
                display: flex;
                width: auto;
                margin-left: auto;
                justify-content: flex-end;
            }
        }
        .nf .nf-panel {
            overflow-anchor: none !important;
            height: auto !important;
            min-height: 0 !important;
        }
        .nf .nf-pager { margin-top: 0 !important; }

        /* Dua filter saja: semua dan belum dibaca, konsisten di desktop/mobile. */
        .nf .nf-chips {
            display: grid;
            grid-template-columns: repeat(2, minmax(112px, 1fr));
            width: min(100%, 330px);
            gap: 4px;
        }
        .nf .nf-filter-link {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            text-align: center;
        }
        .nf .nf-filter-link.on {
            color: #831333;
            background: transparent;
            box-shadow: none;
        }
        .nf .nf-filter-link {
            position:relative;
            transition:color 180ms ease, background-color 180ms ease;
        }
        .nf .nf-filter-link::after {
            content:"";
            position:absolute;
            right:0;
            bottom:0;
            left:0;
            height:3px;
            border-radius:3px 3px 0 0;
            background:#831333;
            transform:scaleX(0);
            transform-origin:center;
            transition:transform 220ms cubic-bezier(.2,.75,.25,1);
        }
        .nf .nf-filter-link.on::after { transform:scaleX(1); }
        .nf .nf-filter-link:hover { background:transparent; box-shadow:none; }
        .nf .nf-filter-link:active { background:#fff7fa; }
        @media (max-width: 600px) {
            .nf .nf-chips { width: 100%; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .nf .nf-filter-link { min-width: 0; padding: 9px 6px; }
        }

        /* Browser scroll anchoring can move the viewport when the list is replaced.
           Disable it for this page; pagination must never initiate scrolling. */
        html:has(.nf),
        body:has(.nf),
        .nf,
        .nf .nf-panel,
        .nf .nf-list,
        .nf .nf-pager {
            overflow-anchor: none !important;
        }

        /* Final alignment: tabs at the far right on desktop, centered on mobile. */
        .nf .nf-panel-top {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
        }
        .nf .nf-panel-top > div {
            flex:0 1 auto;
            min-width:0;
        }
        .nf .nf-panel-top .nf-chips {
            display:flex;
            flex:0 0 auto;
            width:auto;
            margin:0 0 0 auto;
            justify-content:flex-end;
        }
        .nf .nf-panel-top .nf-chip {
            flex:0 0 auto;
        }
        @media (max-width:600px) {
            .nf .nf-panel-top {
                display:flex;
                flex-direction:column;
                align-items:stretch;
                gap:0;
            }
            .nf .nf-panel-top .nf-chips {
                display:grid;
                grid-template-columns:repeat(2,minmax(0,1fr));
                width:min(100%, 280px);
                margin:12px auto 0;
                justify-content:center;
            }
            .nf .nf-panel-top .nf-chip {
                min-width:0;
                text-align:center;
            }
        }

        /* Premium layered status badges: khusus notifikasi laporan disetujui/ditolak. */
        .nf .nf-ic {
            position:relative;
            isolation:isolate;
            width:48px;
            height:48px;
            border-radius:50%;
            display:grid;
            place-items:center;
            flex:none;
            box-shadow:0 5px 13px rgba(39,24,31,.055), inset 0 1px 0 rgba(255,255,255,.9);
        }
        .nf .nf-ic::before {
            content:"";
            position:absolute;
            inset:5px;
            z-index:-1;
            border-radius:50%;
            background:rgba(255,255,255,.94);
            border:1px solid rgba(255,255,255,.95);
            box-shadow:0 2px 6px rgba(39,24,31,.07), inset 0 0 0 1px rgba(255,255,255,.8);
        }
        .nf .nf-ic .cu-icon {
            position:relative;
            z-index:1;
            width:22px;
            height:22px;
            stroke-width:1.9;
        }
        .nf .tone-success .nf-ic {
            background:linear-gradient(145deg,#effaf3 0%,#dcefe4 100%);
            color:#147447;
            box-shadow:0 5px 14px rgba(20,116,71,.09), inset 0 1px 0 rgba(255,255,255,.95);
        }
        .nf .tone-success .nf-ic::before {
            border-color:rgba(20,116,71,.10);
        }
        .nf .tone-danger .nf-ic {
            background:linear-gradient(145deg,#fff1f4 0%,#f7dfe6 100%);
            color:#b51e40;
            box-shadow:0 5px 14px rgba(181,30,64,.09), inset 0 1px 0 rgba(255,255,255,.95);
        }
        .nf .tone-danger .nf-ic::before {
            border-color:rgba(181,30,64,.10);
        }
        @media (max-width:600px) {
            .nf .nf-ic { width:44px;height:44px; }
            .nf .nf-ic::before { inset:4px; }
            .nf .nf-ic .cu-icon { width:20px;height:20px; }
        }

        /* Pagination user: compact, stable and visually aligned with Admin. */
        .nf #nf-panel {
            display: block !important;
            width: 100%;
            height: auto !important;
            min-height: 0 !important;
            overflow: hidden;
        }
        .nf #nf-panel .nf-pager {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 66px;
            padding: 14px 20px;
            margin: 0;
            border-top: 1px solid var(--ln);
            scroll-margin-top: 112px;
        }
        .nf #nf-panel .nf-pager-info {
            flex: 1 1 auto;
            min-width: 0;
            width: auto;
            margin: 0;
            text-align: left;
            font-size: 12px;
            line-height: 1.45;
        }
        .nf #nf-panel .nf-pager-nav {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            justify-content: flex-end;
            gap: 5px;
            width: auto;
            margin: 0 0 0 auto;
        }
        .nf #nf-panel .nf-pg {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            min-width: 36px;
            width: auto;
            height: 36px;
            padding: 0 9px;
            border: 1px solid var(--ln);
            border-radius: 9px;
            background: #fff;
            color: var(--ink);
            font-size: 12px;
            font-weight: 750;
            line-height: 1;
            text-decoration: none;
            transition: background-color .16s ease, border-color .16s ease, color .16s ease, box-shadow .16s ease;
        }
        .nf #nf-panel a.nf-pg:hover {
            border-color: #d9a6b8;
            background: #fff7fa;
            color: var(--w);
        }
        .nf #nf-panel .nf-pg.is-on {
            border-color: var(--w);
            background: var(--w);
            color: #fff;
            box-shadow: 0 5px 12px rgba(131,13,44,.18);
        }
        .nf #nf-panel .nf-pg.is-off {
            color: #c9bfc3;
            background: #fdfafb;
            box-shadow: none;
        }
        .nf #nf-panel[aria-busy="true"] {
            opacity: .72;
            pointer-events: none;
        }
        @media (max-width: 760px) {
            .nf #nf-panel .nf-pager {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
                padding: 13px 14px;
            }
            .nf #nf-panel .nf-pager-info {
                width: 100%;
                text-align: left;
                font-size: 11.5px;
            }
            .nf #nf-panel .nf-pager-nav {
                width: 100%;
                justify-content: flex-end;
                margin-left: 0;
            }
            .nf #nf-panel .nf-pg {
                min-width: 35px;
                height: 35px;
                border-radius: 9px;
            }
        }
        @media (max-width: 360px) {
            .nf #nf-panel .nf-pager { padding: 12px 11px; }
            .nf #nf-panel .nf-pg { min-width: 32px; height: 33px; padding: 0 7px; }
        }
</style>

    <script>
        /*
         * User notification pagination — match Admin navigation behavior.
         * Keep the pagination/footer at the same viewport position when changing
         * pages, including shorter final pages, without jumping to the page top.
         */
        (function () {
            var panel = document.getElementById('nf-panel');
            if (!panel || !document.querySelector('.nf-pager-nav')) return;

            if ('scrollRestoration' in history) history.scrollRestoration = 'manual';

            var busy = false;
            var STORAGE_KEY = 'findback:user:notifications:viewport:v2';

            function getAnchor(selector) {
                return document.querySelector(selector) || document.getElementById('nf-panel');
            }

            function snapshot(selector) {
                var anchor = getAnchor(selector || '.nf-pager');
                return {
                    x: window.scrollX || window.pageXOffset || 0,
                    y: window.scrollY || window.pageYOffset || 0,
                    selector: anchor && anchor.matches('.nf-pager') ? '.nf-pager' :
                        (anchor && anchor.matches('.nf-panel-top') ? '.nf-panel-top' : '#nf-panel'),
                    top: anchor ? anchor.getBoundingClientRect().top : 0,
                    path: window.location.pathname
                };
            }

            function restoreViewport(saved) {
                if (!saved || saved.path !== window.location.pathname) return;
                var attempts = 0;
                function apply() {
                    var anchor = getAnchor(saved.selector);
                    if (anchor) {
                        var delta = anchor.getBoundingClientRect().top - saved.top;
                        var targetY = Math.max(0, (window.scrollY || 0) + delta);
                        window.scrollTo({ left: saved.x || 0, top: targetY, behavior: 'instant' });
                    } else {
                        window.scrollTo({ left: saved.x || 0, top: saved.y || 0, behavior: 'instant' });
                    }
                    attempts += 1;
                    if (attempts < 3) requestAnimationFrame(apply);
                }
                requestAnimationFrame(apply);
            }

            function safeUrl(value) {
                var url = new URL(value, window.location.href);
                url.hash = '';
                return url;
            }

            async function loadPage(value, addHistory, historyState) {
                if (busy) return;
                var url = safeUrl(value);
                if (url.origin !== window.location.origin) return;

                var currentPanel = document.getElementById('nf-panel');
                if (!currentPanel) return;

                var saved = historyState && historyState.viewport
                    ? historyState.viewport
                    : snapshot('.nf-pager');
                busy = true;
                currentPanel.setAttribute('aria-busy', 'true');

                try {
                    var response = await fetch(url.href, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });
                    if (!response.ok) throw new Error('Gagal memuat halaman notifikasi.');

                    var html = await response.text();
                    var parsed = new DOMParser().parseFromString(html, 'text/html');
                    var nextPanel = parsed.getElementById('nf-panel');
                    if (!nextPanel) throw new Error('Panel notifikasi tidak ditemukan.');

                    nextPanel.removeAttribute('aria-busy');
                    nextPanel.style.height = 'auto';
                    nextPanel.style.minHeight = '0';

                    var focused = document.activeElement;
                    if (focused && currentPanel.contains(focused) && typeof focused.blur === 'function') {
                        focused.blur();
                    }

                    currentPanel.replaceWith(nextPanel);

                    if (addHistory) {
                        history.replaceState(
                            Object.assign({}, history.state || {}, { viewport: saved }),
                            '',
                            window.location.href
                        );
                        history.pushState(
                            { notificationPage: true, viewport: saved },
                            '',
                            url.pathname + url.search
                        );
                    }

                    restoreViewport(saved);
                } catch (error) {
                    currentPanel.removeAttribute('aria-busy');
                    try {
                        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(saved));
                    } catch (_) {}
                    busy = false;
                    window.location.assign(url.href);
                    return;
                }
                busy = false;
            }

            // Restore after a normal browser navigation fallback, if required.
            try {
                var fallback = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || 'null');
                if (fallback) {
                    sessionStorage.removeItem(STORAGE_KEY);
                    restoreViewport(fallback);
                }
            } catch (_) {
                try { sessionStorage.removeItem(STORAGE_KEY); } catch (_) {}
            }

            // Seed the current history entry so Back/Forward restores the same position.
            if (!history.state || !history.state.notificationPage) {
                history.replaceState(
                    Object.assign({}, history.state || {}, { notificationPage: true, viewport: snapshot('.nf-pager') }),
                    '',
                    window.location.href
                );
            }

            document.addEventListener('click', function (event) {
                var link = event.target.closest('.nf-pager-nav a, .nf-filter-link');
                if (!link || event.defaultPrevented || event.button !== 0 ||
                    event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

                event.preventDefault();
                var isFilter = link.classList.contains('nf-filter-link');
                var saved = snapshot(isFilter ? '.nf-panel-top' : '.nf-pager');
                var target = safeUrl(link.href);
                if (isFilter) target.searchParams.delete('page');
                loadPage(target.href, true, { viewport: saved });
                if (typeof link.blur === 'function') link.blur();
            });

            window.addEventListener('popstate', function (event) {
                var saved = event.state && event.state.viewport ? event.state.viewport : snapshot('.nf-pager');
                loadPage(window.location.href, false, { viewport: saved });
            });
        })();
    </script>

@endsection
