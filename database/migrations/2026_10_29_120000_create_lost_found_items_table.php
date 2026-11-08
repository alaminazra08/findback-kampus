
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lost_found_items')) {
            return;
        }

        Schema::create('lost_found_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('name');
            $table->string('category')->nullable();

            $table->enum('type', ['lost', 'found']);

            $table->string('location')->nullable();
            $table->date('event_date')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lost_found_items');
    }
};
