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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @push('scripts')
            <script src="{{ asset('js/chat.js') }}"></script>
            <script src="{{ asset('js/reactions.js') }}"></script> 
            <script src="{{ asset('js/file-upload.js') }}"></script>
        @endpush
    </head>
    <body>

        <div class="bg-gray-750 border-b border-gray-700 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                <h2 class="font-bold text-lg text-white">{{ $server->name }}</h2>
                <p class="text-gray-400 text-sm">{{ $server->description }}</p>
            </div>
            
            <!-- Invite Section -->
            <div class="flex items-center space-x-4">
                @if($server->owner_id == auth()->id())
                    <div class="flex items-center space-x-3">
                        @if($server->invite_code)
                            <!-- Dropdown for Invite Code -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition flex items-center space-x-2">
                                    <span>🔗</span>
                                    <span>Invite Link</span>
                                </button>

                                <!-- Dropdown Content -->
                                <div x-show="open"
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 transform scale-95"
                                     x-transition:enter-end="opacity-100 transform scale-100"
                                     class="absolute right-0 mt-2 w-72 bg-gray-800 rounded-lg shadow-lg p-4 z-50">
                                    <div class="space-y-3">
                                        <div class="text-sm text-gray-300">Server Invite Link</div>
                                        <div class="flex items-center space-x-2 bg-gray-700 p-2 rounded">
                                            <code class="text-xs text-green-400 flex-1 overflow-x-auto">
                                                {{ url('/join/' . $server->invite_code) }}
                                            </code>
                                            <button onclick="copyInviteCode('{{ $server->invite_code }}')"
                                                    class="text-blue-400 hover:text-blue-300 text-sm bg-gray-600 px-3 py-1 rounded transition-colors">
                                                Copy
                                            </button>
                                        </div>
                                        
                                        <!-- Revoke Button -->
                                        <form action="{{ route('invites.revoke', $server) }}" method="POST" class="mt-2">
                                            @csrf
                                            <button type="submit" 
                                                    class="w-full bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition">
                                                🔒 Revoke Invite Link
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Generate Invite -->
                            <form action="{{ route('invites.generate', $server) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition flex items-center space-x-2">
                                    <span>🎯</span>
                                    <span>Generate Invite</span>
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <!-- For non-owners, show if they can invite -->
                    @if($server->invite_code)
                        <div class="bg-gray-800 px-4 py-2 rounded-lg border border-gray-600">
                            <div class="text-gray-300 text-sm">Invite Code:</div>
                            <code class="text-green-400 font-mono text-sm">{{ $server->invite_code }}</code>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    
    <div class="flex h-screen bg-gray-900 text-white">
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
                <div class="flex space-x-3 hover:bg-gray-650 p-2 rounded message-item group" data-message-id="{{ $message->id }}">
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
                        
                        <!-- File Display -->
                        @if($message->hasFile())
                            <div class="mt-2">
                                @if($message->isImage())
                                    <!-- Image Preview -->
                                    <div class="max-w-md rounded-lg overflow-hidden border border-gray-600">
                                        <img src="{{ Storage::url($message->file_path) }}" 
                                             alt="{{ $message->file_name }}"
                                             class="max-h-64 w-auto cursor-pointer hover:opacity-90 transition-opacity"
                                             onclick="openImageModal('{{ Storage::url($message->file_path) }}', '{{ $message->file_name }}')">
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
                <div class="flex h-screen bg-gray-900 text-white">
            <!-- Server Sidebar - Hidden on mobile -->
            {{-- <div class="hidden md:flex w-16 bg-gray-800 flex-col items-center py-4">
                <!-- ... server sidebar content ... -->
            </div> --}}

            <!-- Channels Sidebar - Collapsible on mobile -->
            {{-- <div class="w-60 bg-gray-800 flex flex-col md:relative absolute inset-y-0 left-0 z-40 transform md:transform-none transition-transform duration-300 ease-in-out"
                id="channels-sidebar">
                <!-- ... channels sidebar content ... -->
            </div> --}}

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

    </body>
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
    function copyInviteCode(inviteCode) {
    const inviteUrl = `${window.location.protocol}//${window.location.host}/join/${inviteCode}`;
    navigator.clipboard.writeText(inviteUrl).then(() => {
        const button = event.target;
        const originalText = button.innerText;
        
        // Change button text to show success
        button.innerText = 'Copied!';
        button.classList.add('bg-green-600');
        
        // Reset button after 2 seconds
        setTimeout(() => {
            button.innerText = originalText;
            button.classList.remove('bg-green-600');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy invite link');
    });
}

    // Share invite link (for mobile)
    function shareInviteLink(inviteCode) {
        const inviteUrl = `${window.location.protocol}//${window.location.host}/join/${inviteCode}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Join our server!',
                text: `Join our server using this invite link!`,
                url: inviteUrl
            }).catch(console.error);
        } else {
            copyInviteCode(inviteCode);
        }
    }

    function toggleReactionPicker(messageId) {
    const picker = document.getElementById(`reaction-picker-${messageId}`);
    const allPickers = document.querySelectorAll('.reaction-picker');
    
    // Hide all other pickers
    allPickers.forEach(p => {
        if (p.id !== `reaction-picker-${messageId}`) {
            p.classList.add('hidden');
        }
    });
    
    // Toggle current picker
    picker.classList.toggle('hidden');
}

// Close reaction picker when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.reaction-picker') && 
        !event.target.matches('button[onclick^="toggleReactionPicker"]')) {
        document.querySelectorAll('.reaction-picker').forEach(picker => {
            picker.classList.add('hidden');
        });
    }
});

// Handle emoji selection
document.querySelectorAll('.emoji-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const messageId = this.dataset.messageId;
        const emoji = this.dataset.emoji;
        
        // Send reaction to server
        fetch(`/messages/${messageId}/react`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ emoji: emoji })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hide the picker
                document.getElementById(`reaction-picker-${messageId}`).classList.add('hidden');
                // Optionally refresh the messages or update the UI
                location.reload();
            }
        });
    });
});

    // File upload handler
    document.getElementById('file-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const preview = document.getElementById('file-preview');
            document.getElementById('file-name').textContent = file.name;
            document.getElementById('file-size').textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            
            // Set icon based on file type
            const ext = file.name.split('.').pop().toLowerCase();
            const icons = {
                'pdf': '📄',
                'doc': '📝',
                'docx': '📝',
                'txt': '📄',
                'zip': '📦',
                'rar': '📦'
            };
            
            if (file.type.startsWith('image/')) {
                document.getElementById('file-icon').textContent = '🖼️';
            } else {
                document.getElementById('file-icon').textContent = icons[ext] || '📎';
            }
            
            preview.classList.remove('hidden');
        }
    });

    // Remove file from preview
    function removeFile() {
        document.getElementById('file-input').value = '';
        document.getElementById('file-preview').classList.add('hidden');
    }

    // Character count
    document.getElementById('message-input').addEventListener('input', function(e) {
        document.getElementById('char-count').textContent = e.target.value.length + '/2000';
    });

    // Emoji picker toggle
    document.getElementById('emoji-picker-btn').addEventListener('click', function(e) {
        e.preventDefault();
        const modal = document.getElementById('emoji-picker-modal');
        modal.classList.toggle('hidden');
    });

    // Close emoji picker when clicking outside
    document.addEventListener('click', function(e) {
        const picker = document.getElementById('emoji-picker-modal');
        const btn = document.getElementById('emoji-picker-btn');
        if (!picker.contains(e.target) && !btn.contains(e.target)) {
            picker.classList.add('hidden');
        }
    });

    // Insert emoji into message
    document.querySelectorAll('.emoji-insert').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const emoji = this.dataset.emoji;
            const input = document.getElementById('message-input');
            input.value += emoji;
            input.focus();
            document.getElementById('emoji-picker-modal').classList.add('hidden');
            // Update char count
            document.getElementById('char-count').textContent = input.value.length + '/2000';
        });
    });

    // Send message on Enter key
    document.getElementById('message-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('message-form').dispatchEvent(new Event('submit'));
        }
    });

    // Form submit handler
    document.getElementById('message-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const channelId = this.dataset.channelId;
    
    fetch(`/channels/${channelId}/messages`, {  // ✅ Ubah dari /api/channels ke /channels
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response:', data);
        if (data.success) {
            document.getElementById('message-input').value = '';
            document.getElementById('char-count').textContent = '0/2000';
            removeFile();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to send message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error sending message: ' + error.message);
    });
});

// Toggle reaction
function toggleReaction(messageId, emoji) {
    fetch(`/messages/${messageId}/react`, {  // ✅ Ubah dari /api/messages ke /messages
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ emoji: emoji })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
    </script>
</html>