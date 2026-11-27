<?php

namespace App\Console\Commands;

use App\Models\LostFoundItem;
use App\Notifications\ReportRevisionDeadlineNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpireRejectedReports extends Command
{
    protected $signature = 'reports:expire-rejected {--dry-run : Tampilkan laporan yang akan dihapus tanpa menghapusnya}';
    protected $description = 'Kirim pengingat tenggat revisi dan hapus permanen laporan ditolak yang melewati 7 hari.';

    public function handle(): int
    {
        $now = now();
        $deleted = 0;
        $reminded = 0;

        LostFoundItem::query()
            ->where('status', 'rejected')
            ->whereNotNull('revision_expires_at')
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($now, &$deleted, &$reminded) {
                foreach ($items as $item) {
                    $deadline = Carbon::parse($item->revision_expires_at);
                    // Pengingat pertama saat tersisa 24 jam; peringatan terakhir saat tersisa 1 jam.
                    $reminderAt = $deadline->copy()->subDay();
                    $finalWarningAt = $deadline->copy()->subHour();

                    if ($item->reporter && $now->greaterThanOrEqualTo($reminderAt)
                        && $now->lessThan($deadline) && !$item->revision_reminder_sent_at) {
                        $item->reporter->notify(new ReportRevisionDeadlineNotification(
                            $item->id,
                            $item->title,
                            $deadline->locale('id')->translatedFormat('j F Y, H:i'),
                            false,
                        ));
                        $item->forceFill(['revision_reminder_sent_at' => now()])->save();
                        $reminded++;
                    }

                    if ($item->reporter && $now->greaterThanOrEqualTo($finalWarningAt)
                        && $now->lessThan($deadline) && !$item->revision_final_warning_sent_at) {
                        $item->reporter->notify(new ReportRevisionDeadlineNotification(
                            $item->id,
                            $item->title,
                            $deadline->locale('id')->translatedFormat('j F Y, H:i'),
                            true,
                        ));
                        $item->forceFill(['revision_final_warning_sent_at' => now()])->save();
                        $reminded++;
                    }

                    if ($now->greaterThanOrEqualTo($deadline)) {
                        if ($this->option('dry-run')) {
                            $this->line("Akan dihapus: {$item->id} - {$item->title}");
                            continue;
                        }

                        $photoPath = $item->photo_path;
                        DB::transaction(function () use ($item) {
                            // Hapus notifikasi pada admin dan user yang merujuk laporan ini.
                            DB::table('notifications')->whereRaw(
                                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.item_id')) = ?",
                                [(string) $item->id],
                            )->delete();

                            // Audit event terkait dihapus karena pemilik meminta penghapusan permanen.
                            if (DB::getSchemaBuilder()->hasTable('report_audit_events')) {
                                DB::table('report_audit_events')->where('lost_found_item_id', $item->id)->delete();
                            }

                            // Foreign key cascade menghapus revisi, pencocokan, dan klaim terkait.
                            $item->delete();
                        });

                        if ($photoPath) {
                            Storage::disk('public')->delete($photoPath);
                        }
                        $deleted++;
                        $this->line("Dihapus permanen: {$item->id} - {$item->title}");
                    }
                }
            });

        $this->info("Selesai. Pengingat dikirim: {$reminded}; laporan dihapus permanen: {$deleted}.");
        return self::SUCCESS;
    }
}
