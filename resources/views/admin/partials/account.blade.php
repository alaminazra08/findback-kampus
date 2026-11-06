<div class="fb-account">
    <span class="fb-avatar">
        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
    </span>
    <span class="fb-account-name">
        <strong>{{ auth()->user()->name }}</strong>
        <small>Administrator</small>
    </span>
</div>
