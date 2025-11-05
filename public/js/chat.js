// public/js/chat.js
document.addEventListener('DOMContentLoaded', function() {
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message-input');
    const messagesContainer = document.getElementById('messages-container');
    
    if (!messageForm || !messageInput) return;
    
    const channelId = messageForm.dataset.channelId;

    // Initialize Echo/Pusher for real-time
    initializeRealTime(channelId);

    // Handle message form submission
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });

    // Enter key to send message
    messageInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    function initializeRealTime(channelId) {
        // Check if Echo is available (from resources/js/echo.js)
        if (typeof Echo === 'undefined') {
            console.warn('Echo is not loaded. Real-time features disabled.');
            return;
        }

        // Join the channel for real-time updates
        window.Echo.join(`channel.${channelId}`)
            .here((users) => {
                console.log('Users in channel:', users);
            })
            .joining((user) => {
                console.log('User joining:', user);
                showUserJoinNotification(user);
            })
            .leaving((user) => {
                console.log('User leaving:', user);
                showUserLeaveNotification(user);
            })
            .listen('MessageSent', (e) => {
                console.log('New message received:', e);
                addMessageToChat(e.message);
            })
            .error((error) => {
                console.error('Echo error:', error);
            });
    }

    // public/js/chat.js - UPDATE sendMessage function
    function sendMessage() {
    const content = messageInput.value.trim();
    if (!content) return;

    const channelId = messageForm.dataset.channelId;
    
    console.log('🟡 Sending message to channel:', channelId);

    // Show loading state
    const submitBtn = messageForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Sending...';
    submitBtn.disabled = true;

    // ✅ PERBAIKI URL - sesuaikan dengan route yang ada
    fetch(`/api/channels/${channelId}/messages`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ content: content })
    })
    .then(response => {
        console.log('🟡 Response status:', response.status);
        if (!response.ok) {
            return response.text().then(text => { 
                throw new Error(`HTTP ${response.status}: ${text}`); 
            });
        }
        return response.json();
    })
    .then(data => {
        messageInput.value = '';
        updateCharCount(0);
        
        // Auto-add message ke UI tanpa refresh
        addMessageToChat(data);
        
        // Optional: Scroll to bottom
        scrollToBottom();
    })
    .catch(error => {
        console.error('❌ Error sending message:', error);
        alert('Failed to send message: ' + error.message);
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

    function addMessageToChat(message) {
        const messageElement = createMessageElement(message);
        
        if (messagesContainer) {
            // Check if we're near the bottom to auto-scroll
            const isNearBottom = isMessagesContainerNearBottom();
            
            messagesContainer.insertAdjacentHTML('beforeend', messageElement);
            
            if (isNearBottom) {
                scrollToBottom();
            }
        }
    }

    function createMessageElement(message) {
        const messageDate = new Date(message.created_at);
        const formattedTime = messageDate.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });

        return `
            <div class="flex space-x-3 hover:bg-gray-650 p-2 rounded message-item" data-message-id="${message.id}">
                <div class="w-10 h-10 bg-indigo-500 rounded-full flex-shrink-0 flex items-center justify-center">
                    ${message.user.username.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1">
                    <div class="flex items-baseline space-x-2">
                        <span class="font-semibold text-white">${message.user.username}</span>
                        <span class="text-xs text-gray-400">${formattedTime}</span>
                    </div>
                    <p class="text-gray-200 mt-1">${escapeHtml(message.content)}</p>
                </div>
            </div>
        `;
    }

    function showUserJoinNotification(user) {
        if (messagesContainer) {
            const notification = `
                <div class="text-center text-gray-400 text-sm py-2">
                    <span class="font-medium">${user.username}</span> joined the channel
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', notification);
            scrollToBottom();
        }
    }

    function showUserLeaveNotification(user) {
        if (messagesContainer) {
            const notification = `
                <div class="text-center text-gray-400 text-sm py-2">
                    <span class="font-medium">${user.username}</span> left the channel
                </div>
            `;
            messagesContainer.insertAdjacentHTML('beforeend', notification);
            scrollToBottom();
        }
    }

    function isMessagesContainerNearBottom() {
        if (!messagesContainer) return true;
        return messagesContainer.scrollHeight - messagesContainer.clientHeight <= messagesContainer.scrollTop + 100;
    }

    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    function updateCharCount(length) {
        const charCount = document.getElementById('char-count');
        if (charCount) {
            charCount.textContent = `${length}/2000`;
        }
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Initialize character counter
    messageInput.addEventListener('input', function(e) {
        updateCharCount(e.target.value.length);
    });

    scrollToBottom();

    let typingTimer;
    const typingTimeout = 1000; // 1 second

    messageInput.addEventListener('input', function() {
        // Broadcast typing start
        window.Echo.private(`channel.${channelId}`)
            .whisper('typing', {
                user: authUser,
                typing: true
            });
        
    // Clear previous timer
    clearTimeout(typingTimer);
    typingTimer = setTimeout(() => {
        // Broadcast typing stop
        window.Echo.private(`channel.${channelId}`)
            .whisper('typing', {
                user: authUser,
                typing: false
            });
    }, typingTimeout);
});
});