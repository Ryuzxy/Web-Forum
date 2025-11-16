import './bootstrap';

import './echo';

import './chat';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

window.scrollToBottom = function() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}