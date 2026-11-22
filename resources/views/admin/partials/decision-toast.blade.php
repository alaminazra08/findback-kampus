    @if (session('success') || session('warning') || !empty($decisionError))
        @php $verificationToastWarning = (bool) session('warning') || !empty($decisionError);
            $decisionRejected = (bool) session('decision_rejected'); @endphp
        <div class="fb-verification-toast{{ $verificationToastWarning || $decisionRejected ? ' is-warning' : '' }}"
             id="fb-verification-toast" role="{{ $verificationToastWarning ? 'alert' : 'status' }}"
             aria-live="{{ $verificationToastWarning ? 'assertive' : 'polite' }}" aria-atomic="true">
            <span class="fb-verification-toast-icon" aria-hidden="true">{{ $decisionRejected ? '×' : ($verificationToastWarning ? '!' : '✓') }}</span>
            <span class="fb-verification-toast-copy">
                <strong>{{ $decisionRejected ? 'Penolakan tersimpan' : ($verificationToastWarning ? (!empty($decisionError) ? 'Keputusan tidak tersimpan' : 'Perlu perhatian') : ($decisionSuccessTitle ?? 'Verifikasi berhasil')) }}</strong>
                <small>{{ $decisionError ?? session('warning') ?? session('success') }}</small>
            </span>
            <button type="button" class="fb-verification-toast-close" aria-label="Tutup notifikasi">&times;</button>
        </div>
    @endif
