<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $strings = [
            'handover_code'=>60, 'recipient_name'=>150, 'recipient_identifier'=>80,
            'handover_location'=>200, 'handover_evidence_path'=>255,
            'handover_evidence_mime'=>80, 'handover_officer_name'=>150,
            'handover_item_title'=>255,
        ];
        foreach ($strings as $column=>$length) {
            if (!Schema::hasColumn('item_claims', $column)) {
                Schema::table('item_claims', fn (Blueprint $t) => $t->string($column, $length)->nullable());
            }
        }
        if (!Schema::hasColumn('item_claims', 'handover_notes')) {
            Schema::table('item_claims', fn (Blueprint $t) => $t->text('handover_notes')->nullable());
        }
        if (!Schema::hasColumn('item_claims', 'returned_by')) {
            Schema::table('item_claims', fn (Blueprint $t) => $t->foreignId('returned_by')
                ->nullable()->constrained('users')->nullOnDelete());
        }
        if (!Schema::hasIndex('item_claims', ['handover_code'], 'unique')) {
            Schema::table('item_claims', fn (Blueprint $t) => $t->unique('handover_code', 'item_claims_handover_code_unique'));
        }
    }

    public function down(): void
    {
        // Retain receipt history and evidence references; restore backup for a full rollback.
    }
};
