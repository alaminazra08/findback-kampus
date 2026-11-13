<a href="{{ route('admin.dashboard') }}" class="fb-brand"
    aria-label="Kembali ke Dashboard Admin FindBack Kampus" title="Kembali ke Dashboard">
    <span class="fb-brand-mark" aria-hidden="true">
        <img src="{{ asset('images/logo-findback.png') }}" alt="" width="44" height="44">
    </span>
    <span class="fb-brand-copy">
        <strong>FindBack</strong>
        <small>
            {{ $adminSettings['institution_name'] }}
            <span>· Admin</span>
        </small>
    </span>
</a>
