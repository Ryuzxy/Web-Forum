<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Channel;
use Illuminate\Http\Request;
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
        // Validasi
        $validated = $request->validate([
            'content' => 'nullable|string|max:2000',
            'file' => 'nullable|file|max:10240', // 10MB
        ]);

        // Check if at least content or file is provided
        if (!$validated['content'] && !$request->hasFile('file')) {
            return response()->json([
                'success' => false, 
                'message' => 'Message or file required'
            ], 422);
        }

        $message = new Message();
        $message->channel_id = $channel->id;
        $message->user_id = auth()->id();
        $message->content = $validated['content'] ?? '';

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('messages', 'public');
            
            $message->file_path = $path;
            $message->file_name = $file->getClientOriginalName();
            $message->mime_type = $file->getMimeType();
            $message->file_size = $file->getSize();
        }

        $message->save();

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message->load('user', 'reactions')
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
