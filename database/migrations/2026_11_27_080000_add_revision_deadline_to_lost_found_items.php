<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table) {
            if (!Schema::hasColumn('lost_found_items', 'revision_expires_at')) {
                $table->timestamp('revision_expires_at')->nullable();
            }
            if (!Schema::hasColumn('lost_found_items', 'revision_reminder_sent_at')) {
                $table->timestamp('revision_reminder_sent_at')->nullable();
            }
            if (!Schema::hasColumn('lost_found_items', 'revision_final_warning_sent_at')) {
                $table->timestamp('revision_final_warning_sent_at')->nullable();
            }
        });

        // Beri semua laporan lama yang masih ditolak satu periode penuh sejak patch dipasang.
        // Ini mencegah laporan uji lama langsung terhapus saat scheduler pertama berjalan.
        $rolloutDeadline = now()->addDays(7)->endOfDay();
        DB::table('lost_found_items')->where('status', 'rejected')->whereNull('revision_expires_at')
            ->update(['revision_expires_at' => $rolloutDeadline]);
    }

    public function down(): void
    {
        Schema::table('lost_found_items', function (Blueprint $table) {
            foreach (['revision_expires_at', 'revision_reminder_sent_at', 'revision_final_warning_sent_at'] as $column) {
                if (Schema::hasColumn('lost_found_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
