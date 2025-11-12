<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Channel;
use App\Events\MessageSent;


class MessageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Channel $channel)
    {
        if (!auth()->user()->servers->contains($channel->server_id)) {
            abort(403);
        }

        $messages = $channel->messages()->with('user')->latest()->take(50)->get()->reverse();
        return response()->json($messages);
    }

    /**
     * Store a newly created resource in storage.
     */
    // app/Http/Controllers/MessageController.php - UPDATE store method
public function store(Request $request)
{
    $request->validate([
        'channel_id' => 'required|exists:channels,id',
        'content' => 'required|string|max:2000',
    ]);

    $channel = Channel::find($request->channel_id);

    if (!$channel || !auth()->user()->servers->contains($channel->server_id)) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $message = Message::create([
        'channel_id' => $request->channel_id,
        'user_id' => auth()->id(),
        'content' => $request->content,
    ]);

    $message->load('user');

    broadcast(new MessageSent($message))->toOthers();

    if ($request->expectsJson()) {
        return response()->json($message);
    } else {
        return redirect()->route('servers.show', [
            'server' => $channel->server_id,
            'channel' => $channel->id  // Tetap di channel yang sama
        ])->with('success', 'Message sent!');
    }

    // return response()->json($message); // Tetap return JSON untuk AJAX
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function toggleReaction(Request $request, Message $message)
    {
        $emoji = $request->emoji;
        $user = auth()->user();

        $reaction = $message->reactions()->where('user_id', $user->id)->where('emoji', $emoji)->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            $message->reactions()->create([
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }

        // Kirim data terbaru
        $reactions = $message->reactions->groupBy('emoji')->map->count();
        $userReactions = $message->reactions()->where('user_id', $user->id)->pluck('emoji')->toArray();

        return response()->json([
            'success' => true,
            'reactions_count' => $reactions,
            'user_reactions' => $userReactions,
        ]);
    }
}
