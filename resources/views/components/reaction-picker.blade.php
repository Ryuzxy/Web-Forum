<div id="reaction-picker-{{ $messageId }}" 
     class="absolute bg-gray-700 border border-gray-600 rounded-lg p-2 shadow-lg z-50 hidden reaction-picker h-20 w-40">
    <div class="grid grid-cols-6 gap-1">
        @foreach(['👍', '👎', '😄', '😍', '😮', '😢', '😡', '🎉', '🚀', '❤️', '🔥', '👀'] as $emoji)
        <button class="emoji-btn w-8 h-8 rounded hover:bg-gray-600 text-lg transition transform hover:scale-125"
                data-emoji="{{ $emoji }}"
                data-message-id="{{ $messageId }}">
            {{ $emoji }}
        </button>
        @endforeach
    </div>
</div>