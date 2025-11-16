document.addEventListener('click', function (e) {
    document.querySelectorAll('.reaction-picker').forEach(p => {
        if (!p.contains(e.target)) p.classList.add('hidden');
    });
});


document.addEventListener('DOMContentLoaded', function() {
    // Delegate clicks for reaction UI (works for dynamically added elements)
    document.body.addEventListener('click', function(e) {
        // open/close picker
        if (e.target.classList.contains('reaction-picker-btn')) {
            const messageId = e.target.dataset.messageId;
            toggleReactionPicker(messageId, e.target);
            return;
        }

        // emoji button inside picker
        if (e.target.classList.contains('emoji-btn')) {
            const messageId = e.target.dataset.messageId;
            const emoji = e.target.dataset.emoji;
            toggleReaction(messageId, emoji);
            hideAllReactionPickers();
            return;
        }

        // reaction button click (toggle)
        if (e.target.classList.contains('reaction-btn') || e.target.closest('.reaction-btn')) {
            const btn = e.target.closest('.reaction-btn');
            const messageId = btn.dataset.messageId;
            const emoji = btn.dataset.emoji;
            toggleReaction(messageId, emoji);
            return;
        }

        // click outside pickers -> hide
        if (!e.target.closest('.reaction-picker') && !e.target.classList.contains('reaction-picker-btn')) {
            hideAllReactionPickers();
        }
    });
});

function toggleReactionPicker(messageId, button) {
    const pickerId = `reaction-picker-${messageId}`;
    let picker = document.getElementById(pickerId);

    // hide others
    document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));

    if (!picker) {
        createReactionPicker(messageId, button);
        picker = document.getElementById(pickerId);
    } else {
        picker.classList.toggle('hidden');
    }

    if (picker && button) {
        const rect = button.getBoundingClientRect();
        picker.style.position = 'absolute';
        picker.style.top = `${rect.top - picker.offsetHeight - 8 + window.scrollY}px`;
        picker.style.left = `${rect.left + window.scrollX}px`;
    }
}

function createReactionPicker(messageId, button) {
    const emojis = ['👍','👎','😄','😍','😮','😢','😡','🎉','🚀','❤️','🔥','👀'];
    const pickerHtml = `
        <div id="reaction-picker-${messageId}" class="reaction-picker hidden bg-gray-700 border border-gray-600 rounded-lg p-2 shadow-lg z-50">
            <div class="grid grid-cols-6 gap-1">
                ${emojis.map(emoji => `
                    <button class="emoji-btn w-8 h-8 rounded hover:bg-gray-600 text-lg transition transform hover:scale-125"
                            data-emoji="${emoji}"
                            data-message-id="${messageId}">${emoji}</button>
                `).join('')}
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', pickerHtml);
}

function hideAllReactionPickers() {
    document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));
}

function toggleReaction(messageId, emoji) {
    fetch(`/messages/${messageId}/react`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ emoji })
    })
    .then(res => {
        if (!res.ok) return res.text().then(t => { throw new Error(`HTTP ${res.status}: ${t}`); });
        return res.json();
    })
    .then(data => {
        if (data.success) {
            updateReactionsUI(messageId, data.reactions_count || {}, data.user_reactions || []);
        }
    })
    .catch(err => console.error('Error toggling reaction:', err));
}

function updateReactionsUI(messageId, reactionsCount = {}, userReactions = []) {
    const container = document.getElementById(`reactions-${messageId}`);
    if (!container) return;

    const html = Object.entries(reactionsCount).map(([emoji, count]) => {
        const selected = userReactions.includes(emoji) ? 'border border-indigo-400 bg-indigo-900' : '';
        return `<button class="reaction-btn bg-gray-600 hover:bg-gray-500 px-2 py-1 rounded text-sm transition ${selected}"
                        data-message-id="${messageId}" data-emoji="${emoji}">
                    <span class="mr-1">${emoji}</span><span class="text-gray-300">${count}</span>
                </button>`;
    }).join('');

    container.innerHTML = html;
}