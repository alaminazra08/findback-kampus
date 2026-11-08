<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();

            // Pemilik ruang percakapan
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Pengirim pesan (user atau admin)
            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Isi pesan
            $table->text('message')->nullable();

            // Lampiran gambar
            $table->string('attachment_path')->nullable();

            // Waktu pesan dibaca
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['sender_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
