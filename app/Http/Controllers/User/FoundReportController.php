<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\LostFoundItem;
use App\Services\ReportSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FoundReportController extends Controller
{
    public function create(): View
    {
        $items = LostFoundItem::where('status', 'verified')
            ->where('type', 'found')
            ->latest()
            ->paginate(12);

        return view('user.reports.gallery', ['items' => $items, 'kind' => 'found']);
    }
    public function form(): View
    {
        return view('user.reports.found-create');
    }

    public function store(
        StoreReportRequest $request,
        ReportSubmissionService $submissions,
    ): RedirectResponse {
        try {
            $submissions->submit(
                $request->user(),
                'found',
                $request->validated(),
                $request->file('photo'),
            );
        } catch (\Throwable $exception) {
            report($exception);
            return back()
                ->withInput()
                ->with('error', 'Laporan gagal disimpan. Silakan coba kembali.');
        }
        return redirect()
            ->route('user.reports.index')
            ->with(
                'success',
                'Laporan barang ditemukan berhasil dikirim dan menunggu verifikasi Admin.',
            );
    }
}
