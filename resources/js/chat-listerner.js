window.Echo.private(`chat.${userId}`)
    .listen('MessageSent', (e) => {
        const messageBox = document.getElementById('messages');
        messageBox.innerHTML += `
            <div class="p-2 bg-green-200 rounded mb-1">
                <strong>${e.message.sender_id}:</strong> ${e.message.message}
            </div>
        `;
    });
