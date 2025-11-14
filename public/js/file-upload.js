class FileUploadManager {
    constructor() {
        this.currentFile = null;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        const fileInput = document.getElementById('file-input');
        if (fileInput) fileInput.addEventListener('change', (e) => this.handleFileSelect(e));

        const messageForm = document.getElementById('message-form');
        if (messageForm) {
            // If another module already bound submit, avoid double-bind.
            messageForm.addEventListener('submit', (e) => this.handleMessageSubmit(e));
        }
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (file.size > 10 * 1024 * 1024) {
            alert('File size too large. Maximum size is 10MB.');
            event.target.value = '';
            return;
        }

        this.currentFile = file;
        this.showFilePreview(file);
    }

    showFilePreview(file) {
        const preview = document.getElementById('file-preview');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const fileIcon = document.getElementById('file-icon');

        if (!preview || !fileName || !fileSize || !fileIcon) return;

        fileName.textContent = file.name;
        fileSize.textContent = this.formatFileSize(file.size);

        if (file.type.startsWith('image/')) fileIcon.textContent = '🖼️';
        else if (file.type === 'application/pdf') fileIcon.textContent = '📄';
        else if (file.type.includes('document')) fileIcon.textContent = '📝';
        else if (file.type.includes('zip') || file.type.includes('rar')) fileIcon.textContent = '📦';
        else fileIcon.textContent = '📎';

        preview.classList.remove('hidden');
    }

    removeFile() {
        this.currentFile = null;
        const preview = document.getElementById('file-preview');
        const fileInput = document.getElementById('file-input');
        if (preview) preview.classList.add('hidden');
        if (fileInput) fileInput.value = '';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes','KB','MB','GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    async handleMessageSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const channelId = form.dataset.channelId;
        const content = (form.querySelector('#message-input') || {}).value || '';
        const file = this.currentFile;

        if (!content && !file) {
            alert('Please enter a message or select a file.');
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : 'Send';
        if (submitBtn) { submitBtn.textContent = 'Sending...'; submitBtn.disabled = true; }

        try {
            let result;
            if (file) {
                result = await this.uploadFile(channelId, content, file);
            } else {
                result = await this.sendTextMessage(channelId, content);
            }

            if (result && result.success) {
                if (form.querySelector('#message-input')) form.querySelector('#message-input').value = '';
                this.removeFile();
                const charCount = document.getElementById('char-count');
                if (charCount) charCount.textContent = '0/2000';

                // If real-time is enabled, backend should broadcast MessageSent.
                // For immediate UI feedback, add message if returned.
                if (result.data) {
                    if (window.addMessageToChat) window.addMessageToChat(result.data);
                    if (window.scrollToBottom) window.scrollToBottom();
                } else {
                    location.reload(); // fallback
                }
            } else {
                throw new Error(result?.message || 'Failed to send');
            }
        } catch (err) {
            console.error('Error sending message:', err);
            alert('Failed to send message: ' + err.message);
        } finally {
            if (submitBtn) { submitBtn.textContent = originalText; submitBtn.disabled = false; }
        }
    }

    async uploadFile(channelId, content, file) {
        const formData = new FormData();
        formData.append('file', file);
        if (content) formData.append('content', content);

        const res = await fetch(`/channels/${channelId}/upload`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        if (!res.ok) throw new Error(`Upload failed: ${res.status}`);
        return await res.json();
    }

    async sendTextMessage(channelId, content) {
        const res = await fetch(`/channels/${channelId}/messages`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ content })
        });

        if (!res.ok) throw new Error(`Send failed: ${res.status}`);
        return await res.json();
    }
}

// Global helpers for blade
function removeFile() {
    if (window.fileUploadManager) window.fileUploadManager.removeFile();
}
function openImageModal(imageUrl, imageName) {
    const modalHtml = `
        <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50" onclick="closeImageModal()">
            <div class="bg-gray-800 rounded-lg max-w-4xl max-h-full p-4" onclick="event.stopPropagation()">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-white font-medium">${imageName}</span>
                    <button onclick="closeImageModal()" class="text-gray-400 hover:text-white text-2xl">&times;</button>
                </div>
                <img src="${imageUrl}" alt="${imageName}" class="max-w-full max-h-96 object-contain">
                <div class="mt-2 text-center">
                    <a href="${imageUrl}" download="${imageName}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 inline-block">Download Image</a>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}
function closeImageModal() {
    const modal = document.querySelector('.fixed.inset-0.bg-black');
    if (modal) modal.remove();
}

// Initialize manager
document.addEventListener('DOMContentLoaded', () => {
    window.fileUploadManager = new FileUploadManager();
});