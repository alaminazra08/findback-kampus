<?php

namespace App\Services;

use App\Models\{LostFoundItem, User};
use App\Support\ReportAudit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Storage};

class ReportSubmissionService
{
    /** Store a new report with the existing disk, fields and pending status. */
    public function submit(
        User $reporter,
        string $type,
        array $data,
        ?UploadedFile $photo,
    ): LostFoundItem {
        $photoPath = null;
        try {
            if ($photo) {
                $photoPath = $photo->store('reports/' . $type, 'public');
            }
            if (!$photoPath) {
                throw new \RuntimeException('Foto gagal disimpan.');
            }
            return DB::transaction(function () use ($reporter, $type, $data, $photoPath) {
                $item = LostFoundItem::create([
                    'user_id' => $reporter->id,
                    'title' => $data['title'],
                    'category' => $data['category'] ?? 'Lainnya',
                    'type' => $type,
                    'location' => $data['location'],
                    'event_date' => $data['event_date'],
                    'description' => $data['description'],
                    'photo_path' => $photoPath,
                    'status' => 'pending',
                ]);
                ReportAudit::record($item, $reporter, 'created');
                return $item;
            });
        } catch (\Throwable $exception) {
            if ($photoPath) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }
    }
}
