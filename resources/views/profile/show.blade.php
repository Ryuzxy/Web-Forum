{{-- resources/views/profile/show.blade.php --}}
<x-app-layout>
    <div class="min-h-screen bg-gray-900 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Profile Header -->
            <div class="bg-gray-800 rounded-2xl shadow-xl overflow-hidden mb-6">
                <!-- Cover Photo -->
                <div class="h-32 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
                
                <!-- Profile Info -->
                <div class="relative px-6 pb-6">
                    <!-- Avatar -->
                    <div class="absolute -top-12 left-6">
                        <img src="{{ $user->getAvatarUrl() }}" 
                             alt="{{ $user->getDisplayName() }}"
                             class="w-24 h-24 rounded-2xl border-4 border-gray-800 bg-gray-700">
                        <!-- Online Status Indicator -->
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full border-4 border-gray-800 
                                    {{ $user->isOnline() ? 'bg-green-500' : 'bg-gray-500' }}"></div>
                    </div>

                    <!-- User Info -->
                    <div class="pt-12 ml-32">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-2xl font-bold text-white">{{ $user->getDisplayName() }}</h1>
                                <p class="text-gray-400">@{{ $user->username }}</p>
                                
                                @if($user->id === auth()->id())
                                    <div class="flex items-center space-x-2 mt-1">
                                        <span class="text-sm px-2 py-1 rounded-full 
                                                    {{ $user->isOnline() ? 'bg-green-900 text-green-300' : 'bg-gray-700 text-gray-300' }}">
                                            {{ $user->isOnline() ? 'Online' : 'Offline' }}
                                        </span>
                                        <span class="text-gray-500 text-sm">•</span>
                                        <span class="text-gray-400 text-sm">
                                            Last seen: {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never' }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            @if($user->id === auth()->id())
                                <a href="{{ route('profile.edit') }}" 
                                   class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                                    Edit Profile
                                </a>
                            @endif
                        </div>

                        <!-- Bio -->
                        @if($user->bio)
                            <div class="mt-4">
                                <p class="text-gray-300">{{ $user->bio }}</p>
                            </div>
                        @endif

                        <!-- Stats -->
                        <div class="flex space-x-6 mt-4 text-sm">
                            <div class="text-center">
                                <div class="text-white font-bold">{{ $user->servers->count() }}</div>
                                <div class="text-gray-400">Servers</div>
                            </div>
                            <div class="text-center">
                                <div class="text-white font-bold">{{ $user->messages->count() }}</div>
                                <div class="text-gray-400">Messages</div>
                            </div>
                            <div class="text-center">
                                <div class="text-white font-bold">{{ $user->created_at->diffForHumans() }}</div>
                                <div class="text-gray-400">Member since</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shared Servers -->
            @if($user->id !== auth()->id())
                @php
                    $sharedServers = auth()->user()->servers->intersect($user->servers);
                @endphp
                
                @if($sharedServers->count() > 0)
                    <div class="bg-gray-800 rounded-2xl p-6 mb-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Shared Servers</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($sharedServers as $server)
                                <a href="{{ route('servers.show', $server) }}" 
                                   class="bg-gray-700 rounded-lg p-4 hover:bg-gray-600 transition">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center">
                                            {{ substr($server->name, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-medium">{{ $server->name }}</div>
                                            <div class="text-gray-400 text-sm">{{ $server->members->count() }} members</div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>