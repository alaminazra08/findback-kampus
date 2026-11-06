<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->foreignId('claim_id')->nullable()->after('user_id')
                ->constrained('item_claims')->nullOnDelete();
            $table->index(['claim_id', 'id']);
        });
    }
    public function down(): void {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropForeign(['claim_id']);
            $table->dropIndex(['claim_id', 'id']);
            $table->dropColumn('claim_id');
        });
    }
};
