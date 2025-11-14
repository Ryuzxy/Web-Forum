document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');

    if (!messagesContainer) return;

    const channelId = messageForm ? messageForm.dataset.channelId : null;

    // Expose helper for other modules
    window.scrollToBottom = function() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    };

    window.addMessageToChat = function(message) {
        const html = createMessageElement(message);
        const isNearBottom = isMessagesContainerNearBottom();
        messagesContainer.insertAdjacentHTML('beforeend', html);
        if (isNearBottom) scrollToBottom();
    };

    function createMessageElement(message) {
        const messageDate = new Date(message.created_at);
        const formattedTime = messageDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        const safeContent = message.content ? escapeHtml(message.content) : '';

        return `
            <div class="flex space-x-3 hover:bg-gray-650 p-2 rounded message-item" data-message-id="${message.id}">
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex-shrink-0 flex items-center justify-center">
                    ${message.user?.username ? message.user.username.charAt(0).toUpperCase() : ''}
                </div>
                <div class="flex-1">
                    <div class="flex items-baseline space-x-2">
                        <span class="font-semibold text-white">${message.user?.username || 'User'}</span>
                        <span class="text-xs text-gray-400">${formattedTime}</span>
                    </div>
                    ${safeContent ? `<p class="text-gray-200 mt-1">${safeContent}</p>` : ''}
                </div>
            </div>
        `;
    }

    function isMessagesContainerNearBottom() {
        return messagesContainer.scrollHeight - messagesContainer.clientHeight <= messagesContainer.scrollTop + 100;
    }

    function escapeHtml(unsafe = '') {
        return String(unsafe)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Real-time (Echo) init
    window.initializeRealTime = function(channelId) {
        if (typeof Echo === 'undefined' || !channelId) {
            console.warn('Echo not loaded or channelId missing, real-time disabled.');
            return;
        }

        // join or listen on private/channel as per app setup
        window.Echo.join(`channel.${channelId}`)
            .here(users => console.log('Users in channel:', users))
            .joining(user => {
                console.log('User joining:', user);
                if (messagesContainer) {
                    const note = `<div class="text-center text-gray-400 text-sm py-2"><span class="font-medium">${user.username}</span> joined the channel</div>`;
                    messagesContainer.insertAdjacentHTML('beforeend', note);
                    scrollToBottom();
                }
            })
            .leaving(user => {
                console.log('User leaving:', user);
            })
            .listen('MessageSent', e => {
                // e.message expected to be message object
                if (e?.message) window.addMessageToChat(e.message);
            })
            .error(err => console.error('Echo error:', err));
    };

    // typing whisper (guard for Echo & authUser)
    if (messageInput && typeof Echo !== 'undefined' && typeof authUser !== 'undefined' && channelId) {
        let typingTimer;
        const typingTimeout = 1000;
        messageInput.addEventListener('input', () => {
            try {
                window.Echo.private(`channel.${channelId}`).whisper('typing', { user: authUser, typing: true });
            } catch (e) {}
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                try {
                    window.Echo.private(`channel.${channelId}`).whisper('typing', { user: authUser, typing: false });
                } catch (e) {}
            }, typingTimeout);
        });
    }

    // initial scroll
    scrollToBottom();
});