<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js', ])
        <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
        <script>
            const SERVER_ID = {{ $server->id }};
            const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
            encrypted: true
        });

        const ch = pusher.subscribe('server.{{ $server->id }}');

        ch.bind('message.created', function(data) {
            appendMessage(data.message);
        });

        function copyInviteCode(code) {
            const inviteUrl = "{{ url('/join/') }}/" + code;
            navigator.clipboard.writeText(inviteUrl).then(function() {
                // Bisa tambahkan notifikasi sukses di sini
                console.log('Invite link copied to clipboard');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
        </script>
        <script src="{{ asset('js/reactions.js') }}" defer></script> 
        <script src="{{ asset('js/file-upload.js') }}" defer></script>
    </head>
    <body>

    <div class="flex h-screen bg-gray-900 text-white ">
        <!-- Servers Sidebar -->
        <div class="w-16 bg-gray-900 flex flex-col items-center py-4 space-y-3">
            <!-- Home Button -->
            <a href="{{ route('servers.index') }}" 
               class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center hover:bg-indigo-500 transition-colors">
                <span class="text-2xl">🏠</span>
            </a>

            <!-- Separator -->
            <div class="w-8 h-0.5 bg-gray-700 rounded-full mx-auto"></div>

            <!-- Joined Servers List -->
            <div class="space-y-2 overflow-y-auto flex-1">
                @foreach(auth()->user()->servers as $joinedServer)
                    <a href="{{ route('servers.show', $joinedServer) }}" 
                       class="w-12 h-12 flex items-center justify-center rounded-full hover:rounded-2xl transition-all duration-200 
                              {{ $server->id === $joinedServer->id ? 'bg-indigo-500' : 'bg-gray-800 hover:bg-indigo-500' }}">
                        <span class="font-bold text-sm text-white">
                            {{ substr($joinedServer->name, 0, 2) }}
                        </span>
                    </a>
                @endforeach
            </div>

            <!-- Create New Server Button -->
            <a href="{{ route('servers.create') }}" 
               class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center hover:bg-green-500 transition-colors">
                <span class="text-2xl">+</span>
            </a>
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
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-400">Online</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col" >
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
                <div class="relative flex space-x-3 hover:bg-gray-650 p-2 rounded message-item group" data-message-id="{{ $message->id }}">
                    <!-- User Avatar -->
                    <div class="w-10 h-10 bg-indigo-500 rounded-full flex-shrink-0 flex items-center justify-center">
                        {{ substr($message->user->name, 0, 1) }}
                    </div>
                    
                    <!-- Message Content -->
                    <div class="flex-1">
                        <div class="flex items-baseline space-x-2">
                            <span class="font-semibold text-white">{{ $message->user->name }}</span>
                            <span class="text-xs text-gray-400">{{ $message->created_at->format('M j, g:i A') }}</span>
                        </div>
                        
                        <!-- File Display -->
                        @if($message->hasFile())
                            <div class="mt-2">
                                @if($message->isImage())
                                    <!-- Image Preview -->
                                    <div class="max-w-md rounded-lg overflow-hidden border border-gray-600">
                                        <img src="{{ route('files.preview', $message) }}"  
                                             alt="{{ $message->file_name }}"
                                             class="max-h-64 w-auto cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openImageModal('{{ route('files.preview', $message) }}', '{{ $message->file_name }}')">
                                    </div>
                                    <div class="text-sm text-gray-400 mt-1">
                                        <a href="{{ route('files.download', $message) }}" 
                                           class="text-blue-400 hover:text-blue-300">
                                            📷 {{ $message->file_name }} ({{ $message->getFileSizeFormatted() }})
                                        </a>
                                    </div>
                                @else
                                    <!-- Document/File Display -->
                                    <div class="bg-gray-700 rounded-lg p-3 border border-gray-600 max-w-md">
                                        <div class="flex items-center space-x-3">
                                            <div class="text-2xl">
                                                @if($message->mime_type == 'application/pdf')
                                                    📄
                                                @elseif(in_array(pathinfo($message->file_name, PATHINFO_EXTENSION), ['doc', 'docx']))
                                                    📝
                                                @elseif(in_array(pathinfo($message->file_name, PATHINFO_EXTENSION), ['zip', 'rar']))
                                                    📦
                                                @else
                                                    📎
                                                @endif
                                            </div>
                                            <div class="flex-1">
                                                <div class="text-white font-medium truncate">{{ $message->file_name }}</div>
                                                <div class="text-gray-400 text-sm">{{ $message->getFileSizeFormatted() }}</div>
                                            </div>
                                            <a href="{{ route('files.download', $message) }}" 
                                               class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700 transition">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        
                        <!-- Text Content -->
                        @if($message->content)
                            <p class="text-gray-200 mt-2">{{ $message->content }}</p>
                        @endif
                        
                        <!-- Message Reactions -->
                        <div class="mt-2 flex flex-wrap gap-1" id="reactions-{{ $message->id }}">
                            @php
                                $grouped = $message->reactions->groupBy('emoji');
                                $userReactions = $message->reactions->where('user_id', auth()->id())->pluck('emoji')->toArray();
                            @endphp
                            @foreach($grouped as $emoji => $reactions)
                                <button 
                                    type="button"
                                    class="reaction-btn bg-gray-600 hover:bg-gray-500 px-2 py-1 rounded text-sm transition 
                                            {{ in_array($emoji, $userReactions) ? 'border border-indigo-400 bg-indigo-900' : '' }}"
                                    data-message-id="{{ $message->id }}"
                                    data-emoji="{{ $emoji }}"
                                    onclick="toggleReaction({{ $message->id }}, '{{ $emoji }}')">
                                    <span class="mr-1">{{ $emoji }}</span>
                                    <span class="text-gray-300">{{ $reactions->count() }}</span>
                                </button>
                            @endforeach
                        </div>

                        <!-- Reaction Picker Modal -->
                        <div id="reaction-picker-{{ $message->id }}" 
                             class="hidden absolute bg-gray-700 border border-gray-600 rounded-lg p-2 shadow-lg z-50 mt-2 reaction-picker">
                            <div class="grid grid-cols-6 gap-1">
                                @foreach(['👍', '👎', '😄', '😍', '😮', '😢', '😡', '🎉', '🚀', '❤️', '🔥', '👀'] as $emoji)
                                <button class="emoji-btn w-8 h-8 rounded hover:bg-gray-600 text-lg transition transform hover:scale-125"
                                        data-emoji="{{ $emoji }}"
                                        data-message-id="{{ $message->id }}"
                                        onclick="toggleReaction({{ $message->id }}, '{{ $emoji }}')">
                                    {{ $emoji }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Reaction Picker Button -->
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <button class="reaction-picker-btn text-gray-400 hover:text-gray-300 p-1 rounded hover:bg-gray-600"
                                data-message-id="{{ $message->id }}"
                                onclick="toggleReactionPicker({{ $message->id }})">
                            😊
                        </button>
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
            <div class="bg-gray-750 p-4 border-t border-gray-700">
                <form id="message-form" data-channel-id="{{ $activeChannel->id }}" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- File Upload Preview -->
                    <div id="file-preview" class="mb-3 hidden">
                        <div class="bg-gray-700 rounded-lg p-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span id="file-icon" class="text-2xl">📎</span>
                                <div>
                                    <div id="file-name" class="text-white font-medium"></div>
                                    <div id="file-size" class="text-gray-400 text-sm"></div>
                                </div>
                            </div>
                            <button type="button" onclick="removeFile()" class="text-red-400 hover:text-red-300">
                                ❌
                            </button>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <!-- File Upload Button -->
                        <div class="relative">
                            <input type="file" 
                                   id="file-input" 
                                   name="file" 
                                   class="hidden" 
                                   accept="image/*,.pdf,.doc,.docx,.txt,.zip,.rar">
                            <button type="button" 
                                    onclick="document.getElementById('file-input').click()"
                                    class="bg-gray-600 text-gray-300 px-3 py-3 rounded-lg hover:bg-gray-500 transition flex items-center"
                                    title="Upload file">
                                📎
                            </button>
                        </div>

                        <!-- Message Input -->
                        <div class="flex-1 relative">
                            <input type="text" 
                                   name="content" 
                                   placeholder="Message #{{ $activeChannel->name }}"
                                   class="w-full bg-gray-600 text-white px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 pr-28"
                                   id="message-input"
                                   maxlength="2000">
                            
                            <!-- Right side buttons -->
                            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-2">
                                <!-- Emoji Picker Button -->
                                <div class="relative">
                                    <button type="button" 
                                            class="text-gray-400 hover:text-gray-300 transition p-1 rounded hover:bg-gray-500"
                                            id="emoji-picker-btn"
                                            title="Add emoji">
                                        😊
                                    </button>
                                    
                                    <!-- Emoji Picker Modal -->
                                    <div id="emoji-picker-modal" 
                                         class="hidden absolute bottom-full right-0 mb-2 bg-gray-700 border border-gray-600 rounded-lg p-2 shadow-lg z-50">
                                        <div class="grid grid-cols-6 gap-1">
                                            @foreach(['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '😊', '😇', '🙂', '🙃', '😉', '😌', '😍', '🥰', '😘', '😗', '😚', '😙', '🥲', '😋', '😛', '😜', '🤪', '😌', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙁', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🤮', '🤧', '🤬', '🤡', '😈', '👿', '💀', '☠️', '💩', '🤓', '😎', '🤩', '🥳', '😕', '😟', '🙁', '☹️', '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨', '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱', '😤', '😡', '😠', '🤬', '😈', '👿', '💀'] as $emoji)
                                            <button type="button" 
                                                    class="emoji-insert w-8 h-8 rounded hover:bg-gray-600 text-lg transition transform hover:scale-125"
                                                    data-emoji="{{ $emoji }}">
                                                {{ $emoji }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Send Button -->
                                <button type="submit" 
                                        class="bg-indigo-600 text-white px-4 py-1 rounded text-sm hover:bg-indigo-700 transition"
                                        id="send-btn">
                                    Send
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-400 mt-2 flex justify-between">
                        <span>Press Enter to send • Max file size: 10MB</span>
                        <span id="char-count">0/2000</span>
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
        <div class="hidden md:flex flex-col  bg-gray-800 border-l border-gray-700">
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
    <div class="flex items-center justify-between p-2 rounded hover:bg-gray-700 transition group">
        <div class="flex items-center space-x-3">
            <div class="relative">
                <div class="w-8 h-8 bg-{{ $member->isOnline() ? 'green' : 'gray' }}-500 rounded-full flex items-center justify-center text-sm font-semibold">
                    {{ substr($member->name, 0, 1) }}
                </div>
                <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-{{ $member->isOnline() ? 'green' : 'gray' }}-500 rounded-full border-2 border-gray-800"></div>
            </div>
            <div class="flex-1">
                <div class="text-sm font-medium text-gray-200">{{ $member->name }}</div>
                <div class="text-xs text-gray-400 flex items-center">
                    @if($member->pivot->role === 'owner')
                    <span class="text-yellow-400">Owner</span>
                    @elseif($member->pivot->role === 'admin')
                    <span class="text-red-400">Admin</span>
                    @else
                    <span class="text-gray-400">Member</span>
                    @endif
                </div>
            </div>
        </div>
        
        @if($server->owner_id == auth()->id() && $member->id == auth()->id())
        <div class="relative" x-data="{ open: false }">
            <!-- Tombol titik tiga -->
            <button @click="open = !open" 
                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open"
                 @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 class="absolute right-0 mt-2 w-56 bg-gray-800 rounded-lg shadow-lg p-2 z-50 border border-gray-700">
                <div class="space-y-1">
                    <!-- Bagian Invite Link -->
                    <div class="px-3 py-2 text-xs font-medium text-gray-400 border-b border-gray-700">
                        Invite Link
                    </div>
                    
                    @if($server->invite_code)
                    <div class="px-3 py-2">
                        <div class="text-xs text-gray-300 mb-1">Server Invite Link</div>
                        <div class="flex items-center space-x-2 bg-gray-700 p-2 rounded text-xs">
                            <code class="text-green-400 flex-1 truncate">
                                {{ url('/join/' . $server->invite_code) }}
                            </code>
                            <button onclick="copyInviteCode('{{ $server->invite_code }}')"
                                    class="text-blue-400 hover:text-blue-300 bg-gray-600 px-2 py-1 rounded transition-colors text-xs">
                                Copy
                            </button>
                        </div>
                        
                        <!-- Revoke Button -->
                        <form action="{{ route('invites.revoke', $server) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-red-600 text-white px-3 py-1 rounded text-xs hover:bg-red-700 transition flex items-center justify-center space-x-1">
                                <span>🔒</span>
                                <span>Revoke Invite Link</span>
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="px-3 py-2">
                        <form action="{{ route('invites.generate', $server) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full bg-green-600 text-white px-3 py-1 rounded text-xs hover:bg-green-700 transition flex items-center justify-center space-x-1">
                                <span>🎯</span>
                                <span>Generate Invite</span>
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    <!-- Opsi tambahan lainnya bisa ditambahkan di sini -->
                    <div class="px-3 py-2 text-xs font-medium text-gray-400 border-t border-gray-700">
                        Server Settings
                    </div>
                    <a href="#" class="flex items-center space-x-2 px-3 py-2 text-xs text-gray-300 hover:bg-gray-700 rounded transition">
                        <span>⚙️</span>
                        <span>Edit Server</span>
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>

    </body>
</html>