<span
    class="cu-badge {{ in_array($status, ['verified', 'approved', 'returned']) ? 'green' : ($status === 'rejected' ? 'red' : 'amber') }}">{{ ['pending' => 'Menunggu verifikasi', 'verified' => 'Terverifikasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'returned' => 'Dikembalikan', 'claimed' => 'Dalam proses klaim'][$status] ?? $status }}</span>
