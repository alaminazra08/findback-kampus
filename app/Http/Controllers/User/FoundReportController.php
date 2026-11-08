<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Services\ReportSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FoundReportController extends Controller
{
    public function create(): View
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
