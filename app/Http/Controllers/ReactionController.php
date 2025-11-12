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

        // Check if user can access this message
        if (!auth()->user()->servers->contains($message->channel->server_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $emoji = $request->emoji;
        $user = $request->user();

        $reaction = $message->reactions()->where('user_id', $user->id)->where('emoji', $emoji)->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            $message->reactions()->create([
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }

        $reactions = $message->reactions->groupBy('emoji')->map->count();
        $userReactions = $message->reactions()->where('user_id', $user->id)->pluck('emoji')->toArray();

        return response()->json([
            'success' => true,
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

        $reactionsCount = $message->getReactionsCount();
        $userReactions = $message->userReactions->pluck('emoji');

        return response()->json([
            'reactions_count' => $reactionsCount,
            'user_reactions' => $userReactions
        ]);
    }
}