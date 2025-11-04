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
    public function store(Request $request, channel $channel)
    {
        $request->validate([
        'content' => 'required|string|max:2000',
    ]);

    if (!auth()->user()->servers->contains($channel->server_id)) {
        abort(403);
    }

    $message = Message::create([
        'channel_id' => $channel->id,
        'user_id' => auth()->id(),
        'content' => $request->content,
    ]);

    $message->load('user');

    // Broadcast the message
    broadcast(new MessageSent($message))->toOthers();

    return response()->json($message);
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
}
