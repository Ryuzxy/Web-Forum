<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    public function generate(Server $server)
    {
        if ($server->owner_id !== auth()->id()) {
            abort(403);
        }

        // Generate new invite code
        $server->update(['invite_code' => Server::generateInviteCode()]);

        return redirect()->back()->with('success', 'Invite link generated!');
    }

    public function join($code)
    {
        $server = Server::where('invite_code', $code)->firstOrFail();

        // Check if user already member
        if (!$server->members->contains(auth()->id())) {
            $server->members()->attach(auth()->id(), ['role' => 'member']);
        }

        return redirect()->route('servers.show', $server)
            ->with('success', 'Joined server successfully!');
    }
}