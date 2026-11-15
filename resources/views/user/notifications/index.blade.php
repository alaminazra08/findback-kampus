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
                default => $d->locale('id')->translatedFormat('d F Y'),
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
            padding:28px 38px;margin-bottom:20px;border-radius:30px;color:#fff;overflow:hidden;
            background:radial-gradient(120% 140% at 100% 0%,#b61e48 0%,rgba(182,30,72,0) 55%),linear-gradient(135deg,#5e0820 0%,#830d2c 55%,#9c1239 100%);
            box-shadow:0 22px 48px rgba(110,10,38,.30)}
        .nf-hero:before{content:"";position:absolute;left:-70px;bottom:-110px;width:280px;height:280px;border-radius:50%;border:38px solid rgba(255,255,255,.06)}
        .nf-hero:after{content:"";position:absolute;right:210px;top:-70px;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.06)}
        .nf-hero>*{position:relative;z-index:1}
        .nf .nf-hero h1{margin:0 0 6px;font-size:clamp(30px,4vw,42px);line-height:1.1;letter-spacing:-.04em;color:#fff}
        .nf .nf-hero p{margin:0;max-width:460px;color:rgba(255,255,255,.82);line-height:1.6}
        .nf-hero-side{display:flex;align-items:center;gap:22px;flex-wrap:wrap}
        .nf-ring{display:grid;place-items:center;width:96px;height:96px;border-radius:50%;
            background:conic-gradient(#fff calc(var(--p)*1%),rgba(255,255,255,.22) 0)}
        .nf-ring span{display:grid;place-items:center;width:78px;height:78px;border-radius:50%;background:#7a0b28;text-align:center;line-height:1.1}
        .nf-ring b{display:block;font-size:22px;letter-spacing:-.03em}
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
        .nf-stat{position:relative;display:flex;align-items:center;gap:16px;padding:20px 22px;overflow:hidden;
            border:1px solid var(--ln);border-radius:22px;background:#fff;box-shadow:0 10px 30px rgba(58,26,36,.05)}
        .nf-stat-ic{display:grid;place-items:center;flex:none;width:56px;height:56px;border-radius:18px}
        .nf-stat-ic .cu-icon{width:26px;height:26px}
        .nf-stat small{display:block;color:var(--mut);font-size:13.5px;font-weight:600}
        .nf-stat strong{display:block;margin-top:2px;font-size:34px;line-height:1.05;letter-spacing:-.03em}
        .nf-stat.total .nf-stat-ic{background:var(--w-soft);color:var(--w)}
        .nf-stat.read .nf-stat-ic{background:var(--ok-bg);color:var(--ok)}
        .nf-stat.unread{background:linear-gradient(135deg,#fff1f4,#fff);border-color:#efcdd6}
        .nf-stat.unread strong{color:var(--w)}
        .nf-stat.unread .nf-stat-ic{background:#fbdce4;color:var(--w)}
        .nf-stat>*{position:relative;z-index:1}
        .nf-stat-tx{flex:1;min-width:0}
        .nf-bar{display:block;height:6px;margin-top:12px;border-radius:99px;background:#f0e9ec;overflow:hidden}
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
        .nf-item{position:relative;display:flex;gap:16px;padding:18px 20px;margin-bottom:10px;border:1px solid var(--ln);border-radius:20px;background:#fff;transition:.18s}
        .nf-item:hover{border-color:#e2cbd2;box-shadow:0 10px 26px rgba(58,26,36,.07)}
        .nf-item[hidden]{display:none}
        .nf-item.is-unread{border-color:#ecc9d3;background:linear-gradient(95deg,#fff1f5 0%,#fff 72%);box-shadow:0 8px 24px rgba(131,13,44,.07)}
        .nf-item.is-unread:before{content:"";position:absolute;left:-1px;top:20px;bottom:20px;width:4px;border-radius:0 4px 4px 0;background:var(--w)}
        .nf-ic{display:grid;place-items:center;flex:none;width:48px;height:48px;border-radius:16px;background:var(--in-bg);color:var(--in)}
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
        .nf-dot{width:9px;height:9px;border-radius:50%;background:var(--w);box-shadow:0 0 0 4px rgba(131,13,44,.14)}
        .nf-line time{flex:none;font-size:12.5px;color:var(--mut)}
        .nf-body p{margin:6px 0 14px;color:#4d4247;line-height:1.6;overflow-wrap:anywhere}
        .nf-act{display:flex;align-items:center;flex-wrap:wrap;gap:10px}
        .nf-act form{margin:0}
        .nf-pill{display:inline-flex;align-items:center;min-height:34px;padding:0 15px;border-radius:999px;border:1px solid #e6d3d9;background:#fff;color:var(--w);font-weight:700;font-size:13px;cursor:pointer;transition:.18s}
        .nf-pill:hover{background:var(--w);border-color:var(--w);color:#fff}
        .nf-pill.ghost{border-color:transparent;background:transparent;color:var(--mut)}
        .nf-pill.ghost:hover{background:#f5eff1;color:var(--ink)}

        /* Kosong */
        .nf-empty{display:grid;justify-items:center;gap:6px;padding:44px 24px 52px;text-align:center}
        .nf-empty[hidden]{display:none}
        .nf-empty-art{width:190px;height:140px;margin-bottom:8px;color:var(--w)}
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
    </style>

    <div class="nf">
        <header class="nf-hero">
            <div>
                <h1>Notifikasi</h1>
                <p>Informasi terbaru tentang laporan, klaim, dan aktivitas akunmu.</p>
            </div>
            @if ($totalAll === 0)
                <svg class="nf-art" viewBox="0 0 230 120" fill="none" aria-hidden="true">
                    <circle cx="115" cy="60" r="56" stroke="currentColor" stroke-opacity=".14" stroke-width="2"/>
                    <circle cx="115" cy="60" r="40" stroke="currentColor" stroke-opacity=".22" stroke-width="2"/>
                    <circle cx="115" cy="60" r="26" fill="currentColor" fill-opacity=".14"/>
                    <path d="M115 38a15 15 0 0 0-15 15v8l-5 9h40l-5-9v-8a15 15 0 0 0-15-15Z" fill="currentColor" fill-opacity=".92"/>
                    <path d="M108 74a7 7 0 0 0 14 0Z" fill="currentColor" fill-opacity=".92"/>
                    <circle cx="33" cy="30" r="3" fill="currentColor" fill-opacity=".5"/>
                    <circle cx="198" cy="92" r="4" fill="currentColor" fill-opacity=".35"/>
                    <path d="M196 22v10M191 27h10" stroke="currentColor" stroke-opacity=".6" stroke-width="2" stroke-linecap="round"/>
                    <path d="M40 88v8M36 92h8" stroke="currentColor" stroke-opacity=".4" stroke-width="2" stroke-linecap="round"/>
                </svg>
            @else
                <div class="nf-hero-side">
                    <div class="nf-ring" style="--p: {{ $readPct }}" role="img" aria-label="{{ $readPct }} persen sudah dibaca">
                        <span><div><b data-nf="pct">{{ $readPct }}%</b><i>dibaca</i></div></span>
                    </div>
                    @if ($totalUnread > 0)
                        <form method="POST" data-nf-form="all" action="{{ route('user.notifications.readAll') }}">
                            @csrf
                            <button class="nf-btn" type="submit">@include('user.components.icon', ['icon' => 'check']) Tandai semua dibaca</button>
                        </form>
                    @endif
                </div>
            @endif
        </header>

        <div class="nf-stats">
            <div class="nf-stat total">
                <span class="nf-stat-ic">@include('user.components.icon', ['icon' => 'bell'])</span>
                <div class="nf-stat-tx"><small>Total notifikasi</small><strong data-nf="total">{{ $totalAll }}</strong><span class="nf-bar"><i data-nf-bar="total" style="width: {{ $totalPct }}%"></i></span></div>
            </div>
            <div class="nf-stat unread">
                <span class="nf-stat-ic">@include('user.components.icon', ['icon' => 'clock'])</span>
                <div class="nf-stat-tx"><small>Belum dibaca</small><strong data-nf="unread">{{ $totalUnread }}</strong><span class="nf-bar"><i data-nf-bar="unread" style="width: {{ $unreadPct }}%"></i></span></div>
            </div>
            <div class="nf-stat read">
                <span class="nf-stat-ic">@include('user.components.icon', ['icon' => 'check'])</span>
                <div class="nf-stat-tx"><small>Sudah dibaca</small><strong data-nf="read">{{ $totalRead }}</strong><span class="nf-bar"><i data-nf-bar="read" style="width: {{ $readPct }}%"></i></span></div>
            </div>
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
                    <p>Daftar informasi terbaru dari FindBack Kampus.</p>
                </div>
                @if ($totalAll > 0)
                    <div class="nf-chips" role="group" aria-label="Filter notifikasi">
                        <button type="button" class="nf-chip on" data-f="all">Semua</button>
                        <button type="button" class="nf-chip" data-f="unread">Belum dibaca</button>
                        <button type="button" class="nf-chip" data-f="read">Sudah dibaca</button>
                    </div>
                @endif
            </div>

            @forelse ($groups as $label => $items)
                <div class="nf-group">
                    <h3 class="nf-day">{{ $label }}</h3>
                    @foreach ($items as $notification)
                        @php
                            $tone = $toneOf($notification);
                            $isUnread = is_null($notification->read_at);
                            $href = isset($notification->data['claim_id'])
                                ? route('user.claims.index')
                                : (!empty($notification->data['item_id'])
                                    ? route('user.reports.show', (int) $notification->data['item_id'])
                                    : route('user.reports.index'));
                        @endphp
                        <article class="nf-item tone-{{ $tone }} {{ $isUnread ? 'is-unread' : '' }}"
                            data-state="{{ $isUnread ? 'unread' : 'read' }}">
                            <span class="nf-ic" aria-hidden="true">@include('user.components.icon', ['icon' => $iconOf[$tone]])</span>
                            <div class="nf-body">
                                <div class="nf-line">
                                    <div class="nf-title">
                                        <h3>{{ $notification->data['title'] ?? 'Pemberitahuan' }}</h3>
                                        <span class="nf-badge">{{ $badgeOf[$tone] }}</span>
                                        @if ($isUnread)<span class="nf-dot" title="Belum dibaca"></span>@endif
                                    </div>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}"
                                        title="{{ $notification->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}">
                                        {{ $notification->created_at->locale('id')->diffForHumans() }}
                                    </time>
                                </div>
                                <p>{{ $notification->data['message'] ?? '' }}</p>
                                <div class="nf-act">
                                    <a class="nf-pill" href="{{ $href }}">Lihat aktivitas</a>
                                    @if ($isUnread)
                                        <form method="POST" data-nf-form="one" action="{{ route('user.notifications.read', $notification->id) }}">
                                            @csrf
                                            <button class="nf-pill ghost" type="submit">Tandai dibaca</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @empty
                <div class="nf-empty">
                    <svg class="nf-empty-art" viewBox="0 0 190 140" fill="none" aria-hidden="true">
                        <ellipse cx="95" cy="124" rx="50" ry="7" fill="#830d2c" fill-opacity=".08"/>
                        <circle cx="95" cy="64" r="58" fill="#fbeff2"/>
                        <circle cx="95" cy="64" r="42" fill="#f6dfe5"/>
                        <path d="M95 34a21 21 0 0 0-21 21v12l-7 12h56l-7-12V55a21 21 0 0 0-21-21Z" fill="#830d2c"/>
                        <path d="M85 83a10 10 0 0 0 20 0Z" fill="#a3173f"/>
                        <rect x="92" y="26" width="6" height="9" rx="3" fill="#830d2c"/>
                        <path d="M150 28v12M144 34h12" stroke="#e0436b" stroke-width="3" stroke-linecap="round"/>
                        <circle cx="38" cy="40" r="4" fill="#e0436b" fill-opacity=".45"/>
                        <circle cx="156" cy="96" r="5" fill="#830d2c" fill-opacity=".18"/>
                        <path d="M34 92v10M29 97h10" stroke="#830d2c" stroke-opacity=".35" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    <h2>Belum ada notifikasi</h2>
                    <p>Saat status laporan atau klaimmu berubah, kabarnya muncul di sini.</p>
                    <div class="nf-empty-act">
                        <a class="nf-btn solid" href="{{ route('user.reports.index') }}">Buka aktivitas saya</a>
                        <a class="nf-btn" href="{{ route('user.reports.lost.create') }}">Laporkan barang hilang</a>
                    </div>
                </div>
            @endforelse

            <div class="nf-empty" id="nf-filter-empty" hidden>
                <svg class="nf-empty-art" viewBox="0 0 190 140" fill="none" aria-hidden="true"><circle cx="95" cy="64" r="58" fill="#fbeff2"/><circle cx="95" cy="64" r="42" fill="#e4f4ea"/><circle cx="95" cy="64" r="24" fill="#14663f"/><path d="m84 64 8 8 15-16" stroke="#fff" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <h2 id="nf-filter-empty-title">Tidak ada notifikasi</h2>
                <p id="nf-filter-empty-text"></p>
            </div>

            <div class="nf-foot">@include('user.components.pages', ['paginator' => $notifications])</div>
        </section>
    </div>

    <script>
        (function () {
            var panel = document.getElementById('nf-panel');
            if (!panel) return;
            var chips = panel.querySelectorAll('.nf-chip');
            var emptyBox = document.getElementById('nf-filter-empty');
            var msg = {
                unread: ['Semua sudah dibaca', 'Tidak ada notifikasi baru di halaman ini.'],
                read: ['Belum ada yang dibaca', 'Notifikasi yang sudah kamu buka akan tampil di sini.']
            };
            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var f = chip.dataset.f, shown = 0;
                    chips.forEach(function (c) { c.classList.toggle('on', c === chip); });
                    panel.querySelectorAll('.nf-group').forEach(function (g) {
                        var n = 0;
                        g.querySelectorAll('.nf-item').forEach(function (it) {
                            var ok = f === 'all' || it.dataset.state === f;
                            it.hidden = !ok;
                            if (ok) n++;
                        });
                        g.hidden = n === 0;
                        shown += n;
                    });
                    emptyBox.hidden = shown !== 0;
                    if (shown === 0 && msg[f]) {
                        document.getElementById('nf-filter-empty-title').textContent = msg[f][0];
                        document.getElementById('nf-filter-empty-text').textContent = msg[f][1];
                    }
                });
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
                put('unread', unread); put('read', read); put('pct', pct + '%');
                var w = { total: total ? 100 : 0, unread: total ? 100 - pct : 0, read: pct };
                Object.keys(w).forEach(function (k) {
                    var b = root.querySelector('[data-nf-bar="' + k + '"]'); if (b) b.style.width = w[k] + '%';
                });
                var ring = root.querySelector('.nf-ring'); if (ring) ring.style.setProperty('--p', pct);
                var on = root.querySelector('.nf-chip.on'); if (on) on.click();
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
                var dot = item.querySelector('.nf-dot'); if (dot) dot.remove();
                var f = item.querySelector('[data-nf-form="one"]'); if (f) f.remove();
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
@endsection