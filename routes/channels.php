<?php
use Illuminate\Support\Facades\Broadcast;
use App\Models\Channel;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('channel.{channelId}', function ($user, $channelId) {
    // Pastikan user tergabung di channel (sesuaikan model Channel milikmu)
    // Contoh asumsi: Channel punya relation members atau fields
    $channel = Channel::find($channelId);
    if (!$channel) {
        return false;
    }

    // Ubah logika ini sesuai struktur data (owner/participants/member)
    // Contoh: jika channel->users() menyimpan anggota
    if (method_exists($channel, 'users')) {
        return $channel->users()->where('users.id', $user->id)->exists()
            ? ['id' => $user->id, 'name' => $user->name] // presence requires returning user info
            : false;
    }

    // fallback: if channel has fields user_one_id/user_two_id
    if (isset($channel->user_one_id) && isset($channel->user_two_id)) {
        if ($channel->user_one_id == $user->id || $channel->user_two_id == $user->id) {
            return ['id' => $user->id, 'name' => $user->name];
        }
    }

    return false;
});

Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId || true;
});
