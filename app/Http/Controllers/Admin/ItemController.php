<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use App\Support\ReportAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;

class ItemController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DAFTAR LAPORAN BARANG
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:lost,found'],
            'status' => ['nullable', 'in:verified,claimed,returned'],
            'group' => ['nullable', 'in:aktif,selesai,semua'],
        ]);

        $activeStatuses = [
            'verified',
            'claimed',
            'returned',
        ];

        $query = LostFoundItem::query()
            ->whereIn('status', $activeStatuses)
            ->with('reporter:id,name,email,role');

        /*
        | Pencarian barang
        */
        if ($search = trim($filters['search'] ?? '')) {
            $query->where(function ($q) use ($search): void {

                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%');

                if (preg_match('/^(?:BRG-)?(\d+)$/i', $search, $matches)) {
                    $q->orWhere('id', (int) $matches[1]);
                }
            });
        }

        /*
        | Filter jenis
        */
        if ($type = $filters['type'] ?? null) {
            $query->where('type', $type);
        }

        /*
        | Filter tahap
        */
        $group = $filters['group'] ?? 'semua';

        if ($group === 'aktif') {
            $query->whereIn('status', [
                'verified',
                'claimed',
            ]);
        } elseif ($group === 'selesai') {
            $query->where('status', 'returned');
        }

        /*
        | Filter status
        */
        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        /*
        | Statistik dan daftar barang
        */
        return view('admin.items.index', [
            'items' => $query
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            'total' => LostFoundItem::query()
                ->whereIn('status', $activeStatuses)
                ->count(),

            'lost' => LostFoundItem::query()
                ->where('type', 'lost')
                ->whereIn('status', $activeStatuses)
                ->count(),

            'found' => LostFoundItem::query()
                ->where('type', 'found')
                ->whereIn('status', $activeStatuses)
                ->count(),

            'pending' => LostFoundItem::query()
                ->where('status', 'pending')
                ->count(),

            'returned' => LostFoundItem::query()
                ->where('status', 'returned')
                ->count(),

            'group' => $group,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | TAMBAH BARANG OLEH ADMIN
    |--------------------------------------------------------------------------
    | Admin menambahkan laporan barang hilang atau ditemukan.
    | Laporan langsung disetujui tanpa verifikasi manual.
    */

    public function store(Request $request): RedirectResponse
    {
        /*
        | Validasi data
        */
        $validated = $request->validate([
            'type' => [
                'required',
                'in:lost,found',
            ],

            'title' => [
                'required',
                'string',
                'max:150',
            ],

            'location' => [
                'required',
                'string',
                'max:255',
            ],

            'event_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            'photo' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max(5 * 1024),
            ],
        ]);

        /*
        | Identitas administrator
        */
        $admin = $request->user();

        abort_unless(
            $admin && $admin->role === 'admin',
            403,
            'Hanya administrator yang dapat menambahkan barang.'
        );

        $photoPath = null;

        try {
            /*
            | Simpan foto ke storage public
            */
            if ($request->hasFile('photo')) {
                $photoPath = $request
                    ->file('photo')
                    ->store('reports', 'public');

                if (!$photoPath) {
                    throw new \RuntimeException(
                        'Foto barang gagal disimpan.'
                    );
                }
            }

            /*
            | Simpan laporan dan audit secara atomik
            */
            $item = DB::transaction(
                function () use (
                    $validated,
                    $admin,
                    $photoPath
                ) {
                    $item = new LostFoundItem();

                    $item->user_id = $admin->id;

                    $item->title = trim(
                        $validated['title']
                    );

                    $item->type = $validated['type'];

                    $item->location = trim(
                        $validated['location']
                    );

                    $item->event_date =
                        $validated['event_date'];

                    $item->description = trim(
                        $validated['description']
                    );

                    $item->photo_path = $photoPath;

                    /*
                    | Persetujuan otomatis oleh admin
                    */
                    $item->status = 'verified';

                    $item->reviewed_by = $admin->id;

                    $item->reviewed_at = now();

                    $item->save();

                    /*
                    | Catat pembuatan laporan
                    */
                    ReportAudit::record(
                        $item,
                        $admin,
                        'created',
                        'Laporan barang dibuat langsung oleh Administrator.'
                    );

                    /*
                    | Catat persetujuan otomatis
                    */
                    ReportAudit::record(
                        $item,
                        $admin,
                        'verified',
                        'Laporan disetujui otomatis karena dibuat oleh Administrator.'
                    );

                    return $item;
                }
            );

        } catch (\Throwable $exception) {

            /*
            | Bersihkan foto jika database gagal
            */
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }

            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'save' =>
                        'Laporan gagal disimpan. '
                        . 'Silakan periksa kembali data '
                        . 'atau hubungi administrator sistem.',
                ]);
        }

        /*
        | Kode laporan mengikuti ID database
        */
        $reportCode = 'BRG-' . str_pad(
            (string) $item->id,
            4,
            '0',
            STR_PAD_LEFT
        );

        /*
        | Kembali ke daftar barang
        */
        return redirect()
            ->route('admin.items.index', [
                'group' => 'semua',
            ])
            ->with(
                'success',
                'Laporan ' . $reportCode
                . ' berhasil ditambahkan dan '
                . 'langsung dipublikasikan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL LAPORAN BARANG
    |--------------------------------------------------------------------------
    */

    public function show(LostFoundItem $item): View
    {
        $item->load([
            'reporter:id,name,email,role',
            'reviewer:id,name',

            'revisions' => fn ($q) =>
                $q->orderBy('created_at'),
        ]);

        /*
        | Ambil riwayat klaim
        */
        $claims = ItemClaim::query()
            ->where('lost_found_item_id', $item->id)
            ->with([
                'claimant:id,name',
                'reviewer:id,name',
                'handoverOfficer:id,name',
            ])
            ->orderBy('created_at')
            ->get();

        /*
        | Riwayat audit laporan
        */
        $history = ReportAudit::history($item);

        foreach ($claims as $claim) {

            /*
            | Klaim diajukan
            */
            if ($claim->created_at) {
                $history->push([
                    'at' => $claim->created_at,

                    'title' => 'Klaim diajukan',

                    'actor' =>
                        $claim->claimant?->name
                        ?? 'Pengaju klaim',

                    'detail' =>
                        'Pengajuan kepemilikan barang diterima.',

                    'kind' => 'claim',
                ]);
            }

            /*
            | Klaim diperiksa
            */
            if ($claim->reviewed_at) {
                $history->push([
                    'at' => $claim->reviewed_at,

                    'title' =>
                        $claim->status === 'rejected'
                            ? 'Klaim ditolak'
                            : 'Klaim disetujui',

                    'actor' =>
                        $claim->reviewer?->name
                        ?? 'Admin',

                    'detail' =>
                        $claim->rejection_reason
                        ?: 'Keputusan klaim telah dicatat.',

                    'kind' =>
                        $claim->status === 'rejected'
                            ? 'rejected'
                            : 'verified',
                ]);
            }

            /*
            | Serah terima barang
            */
            if ($claim->returned_at) {
                $history->push([
                    'at' => $claim->returned_at,

                    'title' =>
                        'Serah terima barang dikonfirmasi',

                    'actor' =>
                        $claim->handoverOfficer?->name
                        ?? (
                            $claim->handover_officer_name
                            ?? 'Petugas'
                        ),

                    'detail' =>
                        'Barang diterima oleh '
                        . (
                            $claim->recipient_name
                            ?: 'penerima tercatat'
                        )
                        . '. Kode serah terima: '
                        . (
                            $claim->handover_code
                            ?: 'tidak tercatat'
                        )
                        . '.',

                    'kind' => 'returned',
                ]);
            }
        }

        /*
        | Urutkan riwayat berdasarkan waktu
        */
        $history = $history
            ->sortBy(
                fn ($event) =>
                    $event['at']->timestamp
            )
            ->values();

        return view(
            'admin.items.show',
            compact(
                'item',
                'history',
                'claims'
            )
        );
    }
}
