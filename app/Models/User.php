<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = ['name', 'email', 'password', 'google_id', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isRegularUser(): bool
    {
        return $this->role === 'user';
    }

    public function dashboardRoute(): string
    {
        return $this->isAdmin() ? 'admin.dashboard' : 'dashboard';
    }

    // Pesan dalam ruang percakapan milik pengguna
    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'user_id');
    }
}
