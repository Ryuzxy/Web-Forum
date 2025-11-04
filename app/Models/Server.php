<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Server extends Model
{
    use hasFactory;
    protected $fillable = ['name', 'description', 'owner_id', 'invite_code', 'icon_url', 'is_public'];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'server_user')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    public function channels()
    {
        return $this->hasMany(Channel::class)->orderBy('position');
    }

    // Generate unique invite code
    public static function generateInviteCode()
    {
        do {
            $code = strtolower(str()->random(8));
        } while (self::where('invite_code', $code)->exists());

        return $code;
    }
}
