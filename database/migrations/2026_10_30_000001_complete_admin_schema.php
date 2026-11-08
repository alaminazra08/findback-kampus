<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Preserve existing reports; older projects used name instead of title.
        if (!Schema::hasColumn('lost_found_items', 'title')) {
            if (!Schema::hasColumn('lost_found_items', 'name')) {
                throw new RuntimeException('lost_found_items tidak memiliki title/name. Periksa skema sebelum melanjutkan.');
            }
            Schema::table('lost_found_items', fn (Blueprint $t) => $t->renameColumn('name', 'title'));
        } elseif (Schema::hasColumn('lost_found_items', 'name')) {
            DB::table('lost_found_items')->where(fn ($q) => $q->whereNull('title')->orWhere('title', ''))
                ->update(['title' => DB::raw('name')]);
            // Legacy name stays available but must not block new title-only inserts.
            Schema::table('lost_found_items', fn (Blueprint $t) => $t->string('name')->nullable()->change());
        }

        if (!Schema::hasColumn('lost_found_items', 'reviewed_by')) {
            Schema::table('lost_found_items', fn (Blueprint $t) => $t->foreignId('reviewed_by')
                ->nullable()->constrained('users')->nullOnDelete());
        }
        if (!Schema::hasColumn('lost_found_items', 'reviewed_at')) {
            Schema::table('lost_found_items', fn (Blueprint $t) => $t->timestamp('reviewed_at')->nullable());
        }
        if (!Schema::hasColumn('lost_found_items', 'rejection_reason')) {
            Schema::table('lost_found_items', fn (Blueprint $t) => $t->text('rejection_reason')->nullable());
        }

        if (!Schema::hasTable('item_claims')) {
            Schema::create('item_claims', function (Blueprint $t): void {
                $t->id();
                $t->foreignId('lost_found_item_id')->constrained('lost_found_items')->cascadeOnDelete();
                $t->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $t->text('proof_description');
                $t->string('proof_photo_path')->nullable();
                $t->string('status')->default('pending')->index();
                $t->text('rejection_reason')->nullable();
                $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('reviewed_at')->nullable();
                $t->timestamp('returned_at')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('item_matches')) {
            Schema::create('item_matches', function (Blueprint $t): void {
                $t->id();
                $t->foreignId('lost_item_id')->constrained('lost_found_items')->cascadeOnDelete();
                $t->foreignId('found_item_id')->constrained('lost_found_items')->cascadeOnDelete();
                $t->decimal('match_score', 5, 2);
                $t->text('match_reason')->nullable();
                $t->string('status')->default('pending')->index();
                $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $t->timestamp('reviewed_at')->nullable();
                $t->timestamps();
            });
        }
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $t): void {
                $t->id();
                $t->string('setting_key', 100);
                $t->text('setting_value')->nullable();
                $t->timestamps();
            });
        }

        // Fail explicitly on incompatible manual tables, never guess or discard data.
        foreach ([
            'item_claims' => ['id','lost_found_item_id','user_id','proof_description','proof_photo_path','status','rejection_reason','reviewed_by','reviewed_at','returned_at','created_at','updated_at'],
            'item_matches' => ['id','lost_item_id','found_item_id','match_score','match_reason','status','reviewed_by','reviewed_at','created_at','updated_at'],
            'app_settings' => ['id','setting_key','setting_value','created_at','updated_at'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    throw new RuntimeException("Kolom {$table}.{$column} belum tersedia. Hentikan pemasangan dan sesuaikan tabel lama tanpa menghapus data.");
                }
            }
        }

        foreach ([['item_matches', ['lost_item_id','found_item_id'], 'item_matches_pair_unique'],
                  ['app_settings', ['setting_key'], 'app_settings_key_unique']] as [$table,$columns,$name]) {
            if (!Schema::hasIndex($table, $columns, 'unique')) {
                if (DB::table($table)->select($columns)->groupBy($columns)->havingRaw('COUNT(*) > 1')->exists()) {
                    throw new RuntimeException("Data duplikat ditemukan pada {$table}. Periksa duplikat sebelum memasang indeks unik; tidak ada baris yang dihapus otomatis.");
                }
                Schema::table($table, fn (Blueprint $t) => $t->unique($columns, $name));
            }
        }
    }

    public function down(): void
    {
        // Compatibility migration intentionally retains operational data on rollback.
    }
};
