<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\Channel;
use Illuminate\Support\Str;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servers = auth()->user()->servers()->with('channels')->get();
        return view('servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $server = Server::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => auth()->id(),
            'invite_code' => Server::generateInviteCode(),
        ]);

        // Auto-create general channel
        Channel::create([
            'server_id' => $server->id,
            'name' => 'general',
            'type' => 'text',
            'position' => 1,
        ]);

        // Auto-join owner to server
        $server->members()->attach(auth()->id(), ['role' => 'owner']);

        return redirect()->route('servers.show', $server)
            ->with('success', 'Server created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server, Request $request)
    {
        if (!$server->members->contains(auth()->id())) {
        abort(403);
    }

    $server->load(['channels' => function($query) {
        $query->orderBy('position');
    }, 'members']);

    // Get active channel from URL parameter or default to first
    $activeChannel = $request->has('channel') 
        ? $server->channels->firstWhere('id', $request->channel)
        : $server->channels->first();
    
    if ($activeChannel) {
        $activeChannel->load(['messages.user']);
    }

    return view('servers.show', compact('server', 'activeChannel'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id, Server $server)
    {
        if (!$server->members->contains(auth()->id())) {
            abort(403, 'You are not a member of this server.');
        }

        $server->load('channels');
        return view('servers.show', compact('server'));
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
