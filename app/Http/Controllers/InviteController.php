<?php
namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    /**
     * Generate new invite code for a server
     */
    public function generate(Server $server)
    {
        // Check if user is server owner or admin
        if ($server->owner_id !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Only server owner can generate invites'], 403);
            }
            return redirect()->back()->with('error', 'Only server owner can generate invites.');
        }

        // Generate new invite code
        $server->update([
            'invite_code' => Str::lower(Str::random(8))
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'invite_code' => $server->invite_code,
                'invite_url' => url("/join/{$server->invite_code}")
            ]);
        }

        return redirect()->back()->with('success', 'Invite code generated!')->with('invite_code', $server->invite_code);
    }

    /**
     * Show join page for invite code
     */
    public function showJoinForm($code)
    {
        $server = Server::where('invite_code', $code)->first();

        if (!$server) {
            return redirect()->route('dashboard')->with('error', 'Invalid invite code or link has expired.');
        }

        return view('invites.join', compact('server', 'code'));
    }

    /**
     * Join server using invite code
     */
    public function join(Request $request, $code)
    {
        $server = Server::where('invite_code', $code)->first();

        if (!$server) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid invite code'], 404);
            }
            return redirect()->route('dashboard')->with('error', 'Invalid invite code or link has expired.');
        }

        // Check if user is already a member
        if ($server->members()->where('user_id', auth()->id())->exists()) {
            if ($request->expectsJson()) {
                return response()->json(['info' => 'You are already a member of this server']);
            }
            return redirect()->route('servers.show', $server)->with('info', 'You are already a member of this server.');
        }

        // Add user to server
        $server->members()->attach(auth()->id(), [
            'role' => 'member',
            'joined_at' => now()
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Successfully joined ' . $server->name,
                'server' => $server
            ]);
        }

        return redirect()->route('servers.show', $server)
            ->with('success', 'Successfully joined ' . $server->name . '!');
    }

    /**
     * Revoke invite code
     */
    public function revoke(Server $server)
    {
        if ($server->owner_id !== auth()->id()) {
            return redirect()->back()->with('error', 'Only server owner can revoke invites.');
        }

        $server->update(['invite_code' => null]);

        return redirect()->back()->with('success', 'Invite code revoked!');
    }

    public function showInviteForm()
{
    return view('invites.form');
}

/**
 * Show join confirmation page (WITH code parameter)
 */
public function showJoinConfirmation($code)
{
    \Log::info('ShowJoinConfirmation called with code:', ['code' => $code]);

    $server = Server::where('invite_code', $code)->first();

    if (!$server) {
        return redirect()->route('dashboard')->with('error', 'Invalid invite code or link has expired.');
    }

    return view('invites.join', compact('server', 'code'));
}

/**
 * Process invite code from form input
 */
public function processInviteCode(Request $request)
{
    $request->validate([
        'code' => 'required|string|size:8'
    ]);

    $code = $request->input('code');

    // Redirect to join confirmation page
    return redirect()->route('invites.confirm', $code);
}

/**
 * Join server (form submission from confirmation page)
 */
public function joinServer(Request $request, $code)
{
    $server = Server::where('invite_code', $code)->first();

    if (!$server) {
        return redirect()->route('dashboard')->with('error', 'Invalid invite code or link has expired.');
    }

    // Check if user is already a member
    if ($server->members()->where('user_id', auth()->id())->exists()) {
        return redirect()->route('servers.show', $server)->with('info', 'You are already a member of this server.');
    }

    // Add user to server
    $server->members()->attach(auth()->id(), [
        'role' => 'member',
        'joined_at' => now()
    ]);

    return redirect()->route('servers.show', $server)
        ->with('success', 'Successfully joined ' . $server->name . '!');
}
}