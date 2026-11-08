<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            throw new RuntimeException('Kolom users.role belum tersedia. Periksa migration awal.');
        }

        DB::table('users')->where('role', 'mahasiswa')->update(['role' => 'user']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });
    }

    public function down(): void
    {
        // Data akun tidak diubah balik agar tidak merusak role pengguna.
    }
};