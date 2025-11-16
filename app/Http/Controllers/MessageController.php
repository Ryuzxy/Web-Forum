<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Channel;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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
    public function store(Request $request, Channel $channel)
    {
        $request->validate([
        'content' => 'required_without:file|string|max:2000',
        'file'    => 'nullable|file|max:10240', // 10MB
    ]);

    $filePath = null;

    if ($request->hasFile('file')) {
        $filePath = $request->file('file')->store('uploads/messages', 'public');
    }

    $msg = $channel->messages()->create([
        'user_id'   => auth()->id(),
        'content'   => $request->content,
        'file_path' => $filePath,
    ]);

   
    $msg->load('user'); 

    broadcast(new MessageSent($msg))->toOthers();

    return response()->json([
        'success' => true,
        'data' => $msg
    ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
        return response()->json([
            'success' => true,
            'data' => $message->load('user', 'reactions')
        ]);
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
