<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory;

    protected $fillable = ['channel_id', 'user_id', 'content', 'metadata', 'reply_to_id', 'content', 'file_path', 'file_name', 
    'file_size', 'file_type', 'mime_type'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    public function parent()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }
    public function reactions()
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function userReactions()
    {
        return $this->hasMany(MessageReaction::class)->where('user_id', auth()->id());
    }
    public function getReactionsCount()
    {
        return $this->reactions()
            ->selectRaw('emoji, COUNT(*) as count')
            ->groupBy('emoji')
            ->get()
            ->pluck('count', 'emoji');
    }

    public function hasFile()
{
    return !empty($this->file_path) && !empty($this->file_name);
}

public function isImage()
{
    return $this->hasFile() && $this->file_type === 'image';
}

public function isDocument()
{
    return $this->hasFile() && $this->file_type === 'document';
}

public function getFileSizeFormatted()
{
    if (!$this->file_size) return null;
    
    $size = (int)$this->file_size;
    $units = ['B', 'KB', 'MB', 'GB'];
    $unitIndex = 0;
    
    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }
    
    return round($size, 2) . ' ' . $units[$unitIndex];
}
}
