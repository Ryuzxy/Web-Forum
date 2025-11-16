<?php
namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageReaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    /**
     * Add or remove reaction to message
     */
    public function toggleReaction(Request $request, Message $message)
    {
        $request->validate([
            'emoji' => 'required|string|max:10'
        ]);

        $emoji = $request->emoji ?? $request->input('emoji');
        $user = auth()->user();

        // Check if user can access this message
        if (!auth()->user()->servers->contains($message->channel->server_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if reaction already exists
        $reaction = $message->reactions()
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->first();

        if ($reaction) {
            // Delete if exists
            $reaction->delete();
            $action = 'removed';
        } else {
            // Create new reaction
            MessageReaction::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
            $action = 'added';
        }

        // Get updated reactions count
        $reactions = $message->reactions->groupBy('emoji')->map->count();
        $userReactions = $message->reactions()
            ->where('user_id', $user->id)
            ->pluck('emoji')
            ->toArray();

        return response()->json([
            'success' => true,
            'action' => $action,
            'reactions_count' => $reactions,
            'user_reactions' => $userReactions,
        ]);
    }

    /**
     * Get reactions for a message
     */
    public function getReactions(Message $message)
    {
        if (!auth()->user()->servers->contains($message->channel->server_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reactions = $message->reactions->groupBy('emoji')->map->count();
        $userReactions = $message->reactions()
            ->where('user_id', auth()->id())
            ->pluck('emoji')
            ->toArray();

        return response()->json([
            'success' => true,
            'reactions_count' => $reactions,
            'user_reactions' => $userReactions,
        ]);
    }
    public function react(Request $request, Message $message)
    {
        $request->validate([
            'emoji' => 'required|string|max:10'
        ]);

        $reaction = $message->reactions()->updateOrCreate(
            [
                'user_id' => auth()->id(),
                'emoji'   => $request->emoji
            ],
            []
        );

        broadcast(new MessageReacted($reaction->load('user')))->toOthers();

        return response()->json(['success' => true]);
    }

}