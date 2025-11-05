@push('scripts')
<script src="{{ asset('js/chat.js') }}"></script>
@endpush
<x-app-layout>
    <!-- Tambahkan di Server Header section -->
        <div class="p-4 border-b border-gray-700">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-bold text-lg">{{ $server->name }}</h2>
                    <p class="text-gray-400 text-sm">{{ $server->description }}</p>
                </div>
                
                @if($server->owner_id == auth()->id())
                <div class="flex space-x-2">
                    <form action="{{ route('invites.generate', $server) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                            Generate Invite
                        </button>
                    </form>
                    
                    @if($server->invite_code)
                    <div class="bg-gray-700 px-3 py-1 rounded text-sm">
                        <span class="text-gray-300">Invite: </span>
                        <code class="text-green-400">{{ $server->invite_code }}</code>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    <div class="flex h-screen bg-gray-900 text-white">
        <!-- Server Sidebar -->
        <div class="w-16 bg-gray-800 flex flex-col items-center py-4">
            <!-- Server Icon -->
            <div class="w-12 h-12 bg-indigo-500 rounded-2xl mb-4 flex items-center justify-center cursor-pointer hover:rounded-2xl transition-all">
                {{ substr($server->name, 0, 2) }}
            </div>
            
            <!-- Separator -->
            <div class="w-8 h-0.5 bg-gray-700 mb-4"></div>
            
            <!-- Channels List -->
            @foreach($server->channels as $channel)
            <div class="mb-2 group relative">
                <div class="w-12 h-8 bg-gray-700 hover:bg-gray-600 rounded-lg flex items-center justify-center cursor-pointer transition-all"
                     onclick="switchChannel({{ $channel->id }})">
                    <span class="text-lg">#</span>
                </div>
                <div class="absolute left-14 top-1/2 transform -translate-y-1/2 bg-gray-800 px-2 py-1 rounded text-sm whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-50">
                    {{ $channel->name }}
                </div>
            </div>
            @endforeach
        </div>

        <!-- Channels Sidebar -->
        <div class="w-60 bg-gray-800 flex flex-col">
            <!-- Server Header -->
            <div class="p-4 border-b border-gray-700">
                <h2 class="font-bold text-lg">{{ $server->name }}</h2>
                <p class="text-gray-400 text-sm">{{ $server->description }}</p>
            </div>
            
            <!-- Channels List -->
            <div class="flex-1 p-4">
                <h3 class="text-gray-400 text-sm font-semibold mb-2">CHANNELS</h3>
                <div class="space-y-1">
                    @foreach($server->channels as $channel)
                    <div class="flex items-center px-2 py-1 rounded hover:bg-gray-700 cursor-pointer channel-item 
                                {{ $activeChannel && $activeChannel->id == $channel->id ? 'bg-gray-700' : '' }}"
                         onclick="switchChannel({{ $channel->id }})">
                        <span class="text-gray-400 mr-1">#</span>
                        <span class="text-gray-300">{{ $channel->name }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- User Profile -->
            <div class="p-4 bg-gray-750 border-t border-gray-700">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-indigo-500 rounded-full flex items-center justify-center">
                        {{ substr(auth()->user()->username, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold">{{ auth()->user()->username }}</div>
                        <div class="text-xs text-gray-400">Online</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Channel Header -->
            @if($activeChannel)
            <div class="bg-gray-750 border-b border-gray-700 px-6 py-3">
                <div class="flex items-center">
                    <span class="text-gray-400 mr-2">#</span>
                    <h3 class="font-semibold text-gray-200">{{ $activeChannel->name }}</h3>
                    @if($activeChannel->topic)
                    <span class="text-gray-400 text-sm ml-4">{{ $activeChannel->topic }}</span>
                    @endif
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 bg-gray-700 overflow-y-auto p-4 space-y-4" id="messages-container">
                @foreach($activeChannel->messages as $message)
                <div class="flex space-x-3 hover:bg-gray-650 p-2 rounded">
                    <!-- User Avatar -->
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex-shrink-0 flex items-center justify-center">
                        {{ substr($message->user->username, 0, 1) }}
                    </div>
                    
                    <!-- Message Content -->
                    <div class="flex-1">
                        <div class="flex items-baseline space-x-2">
                            <span class="font-semibold text-white">{{ $message->user->username }}</span>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, g:i A') }}</span>
                        </div>
                        <p class="text-gray-200 mt-1">{{ $message->content }}</p>
                    </div>
                </div>
                @endforeach

                @if($activeChannel->messages->isEmpty())
                <div class="text-center text-gray-400 py-8">
                    <p class="text-lg">No messages yet</p>
                    <p class="text-sm">Be the first to send a message in #{{ $activeChannel->name }}</p>
                </div>
                @endif
            </div>

            <!-- Message Input -->
            <div class="bg-gray-750 p-4">
                <form action="{{ route('messages.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="channel_id" value="{{ $activeChannel->id }}">
                    
                    <div class="relative">
                        <input type="text" 
                            name="content" 
                            placeholder="Message #{{ $activeChannel->name }}"
                            class="w-full bg-gray-600 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-20"
                            required
                            maxlength="2000">
                        <div class="absolute right-2 top-1/2 transform -translate-y-1/2">
                            <button type="submit" 
                                    class="bg-green-600 text-white px-4 py-1 rounded text-sm hover:bg-green-700 transition">
                                    SEND
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="flex-1 flex items-center justify-center text-gray-400">
                <p>No channels available</p>
            </div>
            @endif
        </div>

        <!-- Members Sidebar -->
                <div class="flex h-screen bg-gray-900 text-white">
            <!-- Server Sidebar - Hidden on mobile -->
            <div class="hidden md:flex w-16 bg-gray-800 flex-col items-center py-4">
                <!-- ... server sidebar content ... -->
            </div>

            <!-- Channels Sidebar - Collapsible on mobile -->
            <div class="w-60 bg-gray-800 flex flex-col md:relative absolute inset-y-0 left-0 z-40 transform md:transform-none transition-transform duration-300 ease-in-out"
                id="channels-sidebar">
                <!-- ... channels sidebar content ... -->
            </div>

            <!-- Mobile menu button -->
            <button class="md:hidden fixed top-4 left-4 z-50 bg-gray-800 p-2 rounded" 
                    onclick="toggleSidebar()">
                ☰
            </button>
        </div>

        <div class="w-60 bg-gray-800 border-l border-gray-700">
            <div class="p-4 border-b border-gray-700">
                <h3 class="font-semibold text-gray-200">MEMBERS — {{ $server->members->count() }}</h3>
            </div>
            
            <div class="p-4 space-y-2">
                @foreach($server->members as $member)
                <div class="flex items-center space-x-3 p-2 rounded hover:bg-gray-700 transition">
                    <div class="relative">
                        <div class="w-8 h-8 bg-{{ $member->isOnline() ? 'green' : 'gray' }}-500 rounded-full flex items-center justify-center text-sm font-semibold">
                            {{ substr($member->username, 0, 1) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-{{ $member->isOnline() ? 'green' : 'gray' }}-500 rounded-full border-2 border-gray-800"></div>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-200">{{ $member->username }}</div>
                        <div class="text-xs text-gray-400 flex items-center">
                            @if($member->pivot->role === 'owner')
                            <span class="text-yellow-400">👑 Owner</span>
                            @elseif($member->pivot->role === 'admin')
                            <span class="text-red-400">⚡ Admin</span>
                            @else
                            <span class="text-gray-400">Member</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    function switchChannel(channelId) {
        window.location.href = `{{ route('servers.show', $server) }}?channel=${channelId}`;
    }

    document.getElementById('message-input').addEventListener('input', function(e) {
    document.getElementById('char-count').textContent = e.target.value.length + '/2000';
            });

    // Auto scroll to bottom of messages
    document.addEventListener('DOMContentLoaded', function() {
        const messagesContainer = document.getElementById('messages-container');
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
    </script>
</x-app-layout>