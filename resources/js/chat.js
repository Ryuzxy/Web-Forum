import './echo';

const channelId = window.CONVERSATION_ID; // set ini di blade view
window.Echo.join(`channel.${channelId}`)
    .here(users => {
        console.log('current users in presence channel: ', users);
    })
    .joining(user => {
        console.log(user.name + ' joined');
    })
    .leaving(user => {
        console.log(user.name + ' left');
    })
    .listen('MessageSent', (e) => {
        console.log('MessageSent payload:', e);
        // tambahkan ke DOM
    });

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
    function escapeAttr(s = '') {
        return String(s).replace(/'/g, "\\'");
    }

    function switchChannel(id) {
        const url = `/servers/${SERVER_ID}?channel=${id}`;
        window.location.href = url;
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
    
    function appendMessage(message) {
        const container = document.getElementById('messages') || document.getElementById('messages-container');
        if (!container) return;

    const avatar = (message.user && (message.user.avatar || message.user.avatar_url)) || '/default.png';
    const username = message.user?.username || message.user?.name || 'User';
    const createdAt = formatTimestamp(message.created_at);

    const wrapper = document.createElement('div');
    wrapper.classList.add('message-item', 'p-2', 'rounded', 'flex', 'space-x-3');
    wrapper.dataset.messageId = message.id;

    wrapper.innerHTML = `
        <div class="flex gap-3 items-start">
            <img src="${avatar}" class="w-10 h-10 rounded-full" alt="${username}"/>
            <div class="flex-1">
                <div class="flex items-baseline gap-2">
                    <span class="font-bold text-white">${escapeHtml(username)}</span>
                    <span class="text-xs text-gray-400">${escapeHtml(createdAt)}</span>
                </div>
                <div class="text-white mt-1">${escapeHtml(message.content ?? '')}</div>
                <div class="flex gap-2 mt-1" id="reactions-${message.id}">
                    ${renderReactions(message.reactions)}
                </div>
            </div>
        </div>
    `;

    container.appendChild(wrapper);

    // optional scroll helper already provided in chat.js
    if (typeof window.scrollToBottom === 'function') window.scrollToBottom();
}

/* renderReactions expects message.reactions as array of {emoji, count, users?} */
function renderReactions(reactions) {
    if (!reactions || reactions.length === 0) return '';
    // reactions could be object/grouped or array; normalize to array of {emoji, count}
    let items = [];

    if (Array.isArray(reactions)) {
        // array of reaction models
        const map = {};
        reactions.forEach(r => {
            const emoji = r.emoji ?? r;
            map[emoji] = (map[emoji] || 0) + 1;
        });
        items = Object.entries(map).map(([emoji, count]) => ({ emoji, count }));
    } else if (typeof reactions === 'object') {
        // grouped object like { '👍': 2, '❤️': 1 }
        items = Object.entries(reactions).map(([emoji, count]) => ({ emoji, count }));
    }

    return items.map(r => {
        // onclick uses global toggleReaction if available
        return `<button type="button" class="reaction-btn bg-gray-600 px-2 py-1 rounded text-sm" data-emoji="${escapeAttr(r.emoji)}" onclick="event.stopPropagation(); if(typeof toggleReaction === 'function'){ toggleReaction(${r.message_id || ''}, '${escapeAttr(r.emoji)}') }">${escapeHtml(r.emoji)} <span class="text-gray-300 ml-1">${r.count}</span></button>`;
    }).join('');
}

function formatTimestamp(ts) {
    if (!ts) return '';
    const d = new Date(ts);
    if (isNaN(d)) return ts;
    return d.toLocaleString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

/* small helpers reused above */
function escapeHtml(unsafe = '') {
    return String(unsafe)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
function escapeAttr(s = '') {
    return String(s).replace(/'/g, "\\'");
}
    
    
});