
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    const channelId = messageForm ? messageForm.dataset.channelId : null;

    if (!channelId) return;

    // Listen for new messages
    window.Echo.join(`channel.${channelId}`)
        .listen('MessageSent', (e) => {
            addMessageToChat(e.message);
        });

    // Handle message form submission
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const content = messageInput.value.trim();
            if (!content) return;

            // Send message via AJAX
            fetch(`/api/channels/${channelId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ content: content })
            })
            .then(response => response.json())
            .then(data => {
                messageInput.value = '';
                messageInput.focus();
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
        });
    }

    // Add message to chat UI
    function addMessageToChat(message) {
        const messageElement = `
            <div class="flex space-x-3 hover:bg-gray-650 p-2 rounded message-item">
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex-shrink-0 flex items-center justify-center">
                    ${message.user.username.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1">
                    <div class="flex items-baseline space-x-2">
                        <span class="font-semibold text-white">${message.user.username}</span>
                        <span class="text-xs text-gray-400">${new Date(message.created_at).toLocaleString()}</span>
                    </div>
                    <p class="text-gray-200 mt-1">${message.content}</p>
                </div>
            </div>
        `;

        if (messagesContainer) {
            // Check if we're near the bottom to auto-scroll
            const isNearBottom = messagesContainer.scrollHeight - messagesContainer.clientHeight <= messagesContainer.scrollTop + 100;
            
            messagesContainer.insertAdjacentHTML('beforeend', messageElement);
            
            if (isNearBottom) {
                scrollToBottom();
            }
        }
    }

    // Enter key to send message
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                messageForm.dispatchEvent(new Event('submit'));
            }
        });
    }

    // Initial scroll to bottom
    scrollToBottom();
});