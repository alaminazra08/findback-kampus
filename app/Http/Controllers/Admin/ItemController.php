<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ItemClaim;
use App\Models\LostFoundItem;
use App\Support\ReportAudit;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
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

    public function index(Request $request): View|RedirectResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:lost,found'],
            'status' => ['nullable', 'in:verified,claimed,returned'],
            'group' => ['nullable', 'in:aktif,selesai,semua'],
        ]);

        // FindBack: canonical URL filter default (tanpa mengubah hasil query).
        // Nilai `semua` sudah menjadi default; parameter kosong juga tidak dibutuhkan.
        $queryParams = $request->query();
        $normalized = $queryParams;
        foreach (['search', 'type', 'status', 'group'] as $key) {
            if (array_key_exists($key, $normalized)
                && ($normalized[$key] === '' || ($key === 'group' && $normalized[$key] === 'semua'))) {
                unset($normalized[$key]);
            }
        }
        if ($queryParams !== $normalized) {
            return redirect()->route('admin.items.index', $normalized, 302);
        }

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
        $counts = LostFoundItem::query()
            ->whereIn('status', [...$activeStatuses, 'pending'])
            ->selectRaw('status, type, COUNT(*) AS total')
            ->groupBy('status', 'type')
            ->get();
        $published = $counts->whereIn('status', $activeStatuses);

        return view('admin.items.index', [
            'items' => $query
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            'total' => $published->sum('total'),
            'lost' => $published->where('type', 'lost')->sum('total'),
            'found' => $published->where('type', 'found')->sum('total'),
            'pending' => $counts->where('status', 'pending')->sum('total'),
            'returned' => $counts->where('status', 'returned')->sum('total'),

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

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        /*
        | Validasi data
        */
        $validated = $request->validate([
            'type' => [
                'required',
                'in:found',
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

            $message = 'Laporan gagal disimpan. Silakan periksa kembali data atau hubungi administrator sistem.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            }

            return back()->withInput()->withErrors(['save' => $message]);
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
        $destination = route('admin.items.index', ['group' => 'semua']);
        $message = 'Laporan ' . $reportCode . ' berhasil ditambahkan dan langsung dipublikasikan.';

        if ($request->expectsJson()) {
            $request->session()->flash('success', $message);

            return response()->json(['destination' => $destination], 201);
        }

        return redirect()->to($destination)->with('success', $message);
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

        $history = \App\Support\ItemJourney::forItem($item);

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
