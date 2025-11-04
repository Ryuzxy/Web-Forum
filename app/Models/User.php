<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isOnline()
    {
        // Untuk MVP, kita pakai simple logic dulu
        // Nanti bisa diganti dengan last_seen_at check
        return $this->status === 'online';
    }

    /**
     * Get initial for avatar
     */
    public function getInitial()
    {
        return strtoupper(substr($this->username, 0, 1));
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class, 'server_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function ownedServers()
    {
        return $this->hasMany(Server::class, 'owner_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
