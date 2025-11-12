{{-- resources/views/invites/join.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 flex items-center justify-center py-12 px-4">
        <div class="max-w-md w-full bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="bg-indigo-600 p-6 text-center">
                <div class="w-16 h-16 bg-white rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <span class="text-2xl">🎯</span>
                </div>
                <h2 class="text-2xl font-bold text-white">Server Invitation</h2>
                <p class="text-indigo-100 mt-2">You've been invited to join a server</p>
            </div>

            <!-- Server Info -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <h3 class="text-xl font-bold text-white mb-2">{{ $server->name }}</h3>
                    <p class="text-gray-300 text-sm mb-4">{{ $server->description ?? 'No description provided' }}</p>
                    
                    <div class="flex justify-center space-x-6 text-sm">
                        <div class="text-center">
                            <div class="font-bold text-white text-lg">{{ $server->members->count() }}</div>
                            <div class="text-gray-400">Members</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-white text-lg">{{ $server->channels->count() }}</div>
                            <div class="text-gray-400">Channels</div>
                        </div>
                        <div class="text-center">
                            <div class="font-bold text-white text-lg">{{ $server->owner->username }}</div>
                            <div class="text-gray-400">Owner</div>
                        </div>
                    </div>
                </div>

                <!-- Join Form -->
                <form action="{{ route('invites.join', $code) }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <button type="submit" 
                                class="w-full bg-green-600 text-white py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition duration-200 transform hover:scale-105 flex items-center justify-center space-x-2">
                            <span>✅</span>
                            <span>Join {{ $server->name }}</span>
                        </button>
                        
                        <a href="{{ route('dashboard') }}" 
                           class="w-full bg-gray-700 text-white py-3 px-4 rounded-lg font-semibold hover:bg-gray-600 transition duration-200 text-center block">
                            ⬅️ Back to Dashboard
                        </a>
                    </div>
                </form>

                <!-- Invite Code -->
                <div class="mt-6 text-center">
                    <div class="text-gray-400 text-sm mb-2">Invite Code</div>
                    <div class="bg-gray-900 rounded-lg p-3">
                        <code class="text-green-400 font-mono text-lg">{{ $code }}</code>
                    </div>
                    <p class="text-gray-500 text-xs mt-2">Share this code with friends to invite them</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>