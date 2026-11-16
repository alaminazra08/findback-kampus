
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('recalled_at')->nullable();

            $table->boolean('hidden_for_user')
                ->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn([
                'edited_at',
                'recalled_at',
                'hidden_for_user',
            ]);
        });
    }
};
