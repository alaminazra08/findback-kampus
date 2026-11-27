<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('lost_found_items') || !Schema::hasColumn('lost_found_items', 'revision_expires_at')) {
            return;
        }

        // Tenggat lama yang lebih dari 7 x 24 jam dipangkas menjadi maksimal 7 hari
        // sejak patch ini dipasang. Tenggat yang lebih dekat atau sudah lewat tidak diperpanjang.
        $now = now();
        $maximumDeadline = $now->copy()->addDays(7);

        DB::table('lost_found_items')
            ->where('status', 'rejected')
            ->whereNotNull('revision_expires_at')
            ->where('revision_expires_at', '>', $maximumDeadline)
            ->update(['revision_expires_at' => $maximumDeadline]);
    }

    public function down(): void
    {
        // Sengaja tidak mengembalikan tenggat lama agar rollback tidak memperpanjang
        // masa penyimpanan laporan yang sedang menunggu perbaikan.
    }
};
