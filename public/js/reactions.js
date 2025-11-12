document.addEventListener('DOMContentLoaded', function() {
    // Reaction picker toggle
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('reaction-picker-btn')) {
            const messageId = e.target.dataset.messageId;
            toggleReactionPicker(messageId, e.target);
        }
        
        if (e.target.classList.contains('emoji-btn')) {
            const messageId = e.target.dataset.messageId;
            const emoji = e.target.dataset.emoji;
            toggleReaction(messageId, emoji);
            hideAllReactionPickers();
        }
        
        if (e.target.classList.contains('reaction-btn')) {
            const messageId = e.target.dataset.messageId;
            const emoji = e.target.dataset.emoji;
            toggleReaction(messageId, emoji);
        }
    });

    // Close reaction picker when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.reaction-picker') && !e.target.classList.contains('reaction-picker-btn')) {
            hideAllReactionPickers();
        }
    });
});

function toggleReactionPicker(messageId, button) {
    const picker = document.getElementById(`reaction-picker-${messageId}`);
    const allPickers = document.querySelectorAll('.reaction-picker');
    
    // Hide all other pickers
    allPickers.forEach(p => p.classList.add('hidden'));
    
    // Toggle current picker
    if (picker) {
        picker.classList.toggle('hidden');
        
        // Position picker near the button
        const rect = button.getBoundingClientRect();
        picker.style.top = `${rect.top - 120}px`;
        picker.style.left = `${rect.left}px`;
    } else {
        createReactionPicker(messageId, button);
    }
}

function createReactionPicker(messageId, button) {
    const pickerHtml = `
        <div id="reaction-picker-${messageId}" class="absolute bg-gray-700 border border-gray-600 rounded-lg p-2 shadow-lg z-50 reaction-picker">
            <div class="grid grid-cols-6 gap-1">
                ${['👍', '👎', '😄', '😍', '😮', '😢', '😡', '🎉', '🚀', '❤️', '🔥', '👀'].map(emoji => `
                    <button class="emoji-btn w-8 h-8 rounded hover:bg-gray-600 text-lg transition transform hover:scale-125"
                            data-emoji="${emoji}"
                            data-message-id="${messageId}">
                        ${emoji}
                    </button>
                `).join('')}
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', pickerHtml);
    
    // Position the new picker
    const picker = document.getElementById(`reaction-picker-${messageId}`);
    const rect = button.getBoundingClientRect();
    picker.style.top = `${rect.top - 120}px`;
    picker.style.left = `${rect.left}px`;
}

function hideAllReactionPickers() {
    document.querySelectorAll('.reaction-picker').forEach(picker => {
        picker.classList.add('hidden');
    });
}

function toggleReaction(messageId, emoji) {
    fetch(`/api/messages/${messageId}/react`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ emoji: emoji })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateReactionsUI(messageId, data.reactions_count, data.user_reactions);
        }
    })
    .catch(error => {
        console.error('Error toggling reaction:', error);
    });
}

function updateReactionsUI(messageId, reactionsCount, userReactions) {
    const reactionsContainer = document.getElementById(`reactions-${messageId}`);
    
    if (!reactionsContainer) return;
    
    let reactionsHtml = '';
    
    Object.entries(reactionsCount).forEach(([emoji, count]) => {
        const isUserReacted = userReactions.includes(emoji);
        reactionsHtml += `
            <button class="reaction-btn bg-gray-600 hover:bg-gray-500 px-2 py-1 rounded text-sm transition 
                         ${isUserReacted ? 'border border-indigo-400 bg-indigo-900' : ''}"
                    data-message-id="${messageId}"
                    data-emoji="${emoji}">
                <span class="mr-1">${emoji}</span>
                <span class="text-gray-300">${count}</span>
            </button>
        `;
    });
    
    reactionsContainer.innerHTML = reactionsHtml;
}