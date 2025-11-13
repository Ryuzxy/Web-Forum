<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use App\Models\MessageReaction;

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
        'name', 'username', 'email', 'password', 'display_name', 
        'bio', 'avatar', 'status', 'last_seen_at', 'theme', 'settings'
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'settings' => 'array'
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

    public function getDisplayName()
    {
        return $this->display_name ?: $this->username;
    }



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
        return $this->status === 'online' && 
           $this->last_seen_at && 
           $this->last_seen_at->gt(now()->subMinutes(5));
    }

    public function getAvatarUrl()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        
        // Generate default avatar based on username
        return $this->generateDefaultAvatar();
    }

    private function generateDefaultAvatar()
    {
        $colors = ['FF6B6B', '4ECDC4', '45B7D1', '96CEB4', 'FFEAA7', 'DDA0DD', '98D8C8'];
        $color = $colors[hexdec(substr(md5($this->username), 0, 6)) % count($colors)];
        
        return "https://ui-avatars.com/api/?name=" . urlencode($this->getDisplayName()) . 
            "&color=FFFFFF&background=" . $color . "&size=128";
    }

    public function updateStatus($status)
    {
        $this->update([
            'status' => $status,
            'last_seen_at' => now()
        ]);
    }

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
    
    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }

}
