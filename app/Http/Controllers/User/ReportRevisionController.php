<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\{LostFoundItem, User};
use App\Notifications\AdminMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;

class ReportRevisionController extends Controller
{
    public function update(
        Request $request,
        LostFoundItem $report,
    ): \Illuminate\Http\RedirectResponse {
        abort_unless((int) $report->user_id === (int) $request->user()->id, 404);
        $data = $request->validate([
            'revision_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'location' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:3000'],
            'response_note' => ['required', 'string', 'min:10', 'max:2000'],
            'confirmation' => ['accepted'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $path = null;
        try {
            DB::transaction(function () use ($request, $report, $data, &$path) {
                // All decisions, cancellations and responses lock the same parent first.
                $item = LostFoundItem::lockForUpdate()->findOrFail($report->id);
                abort_unless((int) $item->user_id === (int) $request->user()->id, 404);
                $rejected = $item->status === 'rejected';
                $revision = $rejected
                    ? null
                    : $item
                        ->revisions()
                        ->whereKey($data['revision_id'])
                        ->whereNull('closed_at')
                        ->first();
                if (!$rejected && ($item->status !== 'pending' || !$revision)) {
                    throw ValidationException::withMessages([
                        'revision_id' =>
                            'Permintaan tidak aktif. Muat ulang laporan sebelum mengirim ulang.',
                    ]);
                }
                if (
                    $rejected &&
                    (!trim((string) $item->rejection_reason) ||
                        $item->revisions()->whereNull('closed_at')->exists())
                ) {
                    throw ValidationException::withMessages([
                        'revision_id' => 'Laporan tidak dapat diperbaiki saat ini. Hubungi admin.',
                    ]);
                }
                $fields = [
                    'title',
                    'category',
                    'location',
                    'event_date',
                    'description',
                    'photo_path',
                ];
                $before = $item->only($fields);
                $item->fill(
                    collect($data)
                        ->only(['title', 'location', 'event_date', 'description'])
                        ->all(),
                );
                if ($request->hasFile('photo')) {
                    $path = $request->file('photo')->store('reports/' . $item->type, 'public');
                    if (!$path) {
                        throw new \RuntimeException('Foto gagal disimpan.');
                    }
                    $item->photo_path = $path;
                }
                if (!$item->photo_path) {
                    throw ValidationException::withMessages([
                        'photo' => 'Foto laporan wajib dilengkapi.',
                    ]);
                }
                if ($rejected) {
                    $revision = $item->revisions()->create([
                        'requested_by' => $item->reviewed_by ?? $request->user()->id,
                        'requester_name' => 'Admin (penolakan)',
                        'note' => $item->rejection_reason,
                    ]);
                    $item->status = 'pending';
                    $item->rejection_reason = null;
                    $item->reviewed_by = null;
                    $item->reviewed_at = null;
                }
                $item->save();
                $revision->update([
                    'closed_at' => now(),
                    'closed_by' => $request->user()->id,
                    'closer_name' => $request->user()->name,
                    'closure_kind' => 'resubmitted',
                    'response_note' => $data['response_note'],
                    'closure_note' => null,
                    'before_data' => $before,
                    'after_data' => $item->only($fields),
                ]);
                // Required operational notification, in the same transaction (not optional alerts).
                User::where('role', 'admin')->chunkById(100, function ($admins) use ($item) {
                    foreach ($admins as $admin) {
                        $admin->notify(
                            new AdminMessageNotification(
                                'Perbaikan laporan dikirim ulang',
                                $item->title . ' siap diperiksa ulang.',
                                'reports',
                                $item->id,
                            ),
                        );
                    }
                });
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            if (
                $e instanceof ValidationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
            ) {
                throw $e;
            }
            report($e);
            return back()
                ->withInput()
                ->with(
                    'error',
                    'Perbaikan gagal disimpan. Data belum diubah; silakan coba kembali.',
                );
        }
        return redirect()
            ->route('user.reports.show', $report)
            ->with('success', 'Perbaikan telah dikirim ulang. Menunggu pemeriksaan admin.');
    }
}
