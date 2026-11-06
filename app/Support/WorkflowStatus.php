<?php

namespace App\Support;

final class WorkflowStatus
{
    public static function reports(): array
    {
        return [
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Disetujui',
            'rejected' => 'Laporan Ditolak',
            'claimed' => 'Dalam Proses Klaim (status lama)',
            'returned' => 'Barang Dikembalikan',
        ];
    }

    public static function claims(): array
    {
        return [
            'pending' => 'Menunggu Pemeriksaan Klaim',
            'approved' => 'Klaim Disetujui',
            'rejected' => 'Klaim Ditolak',
            'returned' => 'Selesai Dikembalikan',
        ];
    }

    public static function matches(): array
    {
        return [
            'pending' => 'Perlu Pemeriksaan',
            'confirmed' => 'Cocok',
            'rejected' => 'Tidak Cocok',
        ];
    }

    public static function report(?string $status): string
    {
        return self::reports()[$status] ?? ($status ?: 'Status tidak tersedia');
    }

    public static function claim(?string $status): string
    {
        return self::claims()[$status] ?? ($status ?: 'Status tidak tersedia');
    }

    public static function matching(?string $status): string
    {
        return self::matches()[$status] ?? ($status ?: 'Status tidak tersedia');
    }

    public static function code(int $id): string
    {
        return 'BRG-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }
}
