@php($initial = mb_strtoupper(mb_substr(trim($account->name ?? ''), 0, 1)))
<span>{{ $initial !== '' ? $initial : '?' }}</span>
@if ($account->profile_photo_path)
    <img class="fb-account-photo-image" src="{{ route('admin.users.photo', ['user' => $account, 'v' => sha1($account->profile_photo_path)]) }}" alt="" width="48" height="48">
@endif
