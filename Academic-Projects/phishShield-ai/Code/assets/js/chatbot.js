// PhishShield AI — chatbot.js

document.addEventListener('DOMContentLoaded', function () {
    var chatForm    = document.getElementById('chatForm');
    var chatInput   = document.getElementById('chatInput');
    var chatWindow  = document.getElementById('chatWindow');
    var sendBtn     = document.getElementById('sendBtn');

    if (!chatForm || !chatInput || !chatWindow) return;

    chatForm.addEventListener('submit', function (e) {
        e.preventDefault();
        var msg = chatInput.value.trim();
        if (!msg) return;

        appendBubble('user', msg);
        chatInput.value = '';
        sendBtn.disabled = true;

        var typing = appendTyping();

        fetch('<?= defined("SITE_URL") ? SITE_URL : "" ?>/api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(msg)
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            typing.remove();
            appendBubble('bot', data.reply || 'Sorry, I could not get a response.');
            sendBtn.disabled = false;
        })
        .catch(function () {
            typing.remove();
            appendBubble('bot', 'Connection error. Please try again.');
            sendBtn.disabled = false;
        });
    });

    // Enter to send, Shift+Enter for new line
    chatInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    function appendBubble(role, text) {
        var div = document.createElement('div');
        div.className = 'd-flex ' + (role === 'user' ? 'justify-content-end' : 'justify-content-start');
        var bubble = document.createElement('div');
        bubble.className = 'chat-bubble ' + role;
        bubble.textContent = text;
        div.appendChild(bubble);
        chatWindow.appendChild(div);
        chatWindow.scrollTop = chatWindow.scrollHeight;
        return div;
    }

    function appendTyping() {
        var div = document.createElement('div');
        div.className = 'd-flex justify-content-start';
        div.innerHTML = '<div class="chat-bubble bot"><span class="typing-dots"><span></span><span></span><span></span></span></div>';
        chatWindow.appendChild(div);
        chatWindow.scrollTop = chatWindow.scrollHeight;
        return div;
    }
});
