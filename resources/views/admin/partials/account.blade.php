<a class="fb-account fb-account-profile-link" href="{{ route('admin.profile.index') }}" aria-label="Buka Profil Admin">
    <span class="fb-avatar" data-account-photo="{{ auth()->id() }}" aria-hidden="true">
        @include('admin.users.profile-photo', ['account' => auth()->user()])
    </span>
    <span class="fb-account-name">
        <strong>{{ auth()->user()->name }}</strong>
        <small>Administrator</small>
    </span>
</a>
