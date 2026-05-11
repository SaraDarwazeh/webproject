/**
 * StreamHive Chatbot - Gemini Flash powered
 */

(function() {
    const CHAT_API = '/streamhive/app/api/chat.php';
    let chatHistory = [];
    let isOpen = false;

    // Create chatbot UI
    function createChatUI() {
        const widget = document.getElementById('chatbot-widget');
        if (!widget) return;

        widget.innerHTML = `
            <button class="chat-toggle" id="chat-toggle" onclick="toggleChat()" title="Chat with HiveBot">
                <i class="fas fa-comment-dots" id="chat-toggle-icon"></i>
            </button>
            <div class="chat-window" id="chat-window">
                <div class="chat-header">
                    <div class="chat-header-info">
                        <div class="chat-avatar"><i class="fas fa-robot"></i></div>
                        <div>
                            <strong>HiveBot</strong>
                            <small class="d-block" style="opacity: 0.7;">Movie & Series Assistant</small>
                        </div>
                    </div>
                    <button class="chat-close" onclick="toggleChat()"><i class="fas fa-times"></i></button>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <div class="chat-msg bot">
                        <div class="chat-msg-avatar"><i class="fas fa-robot"></i></div>
                        <div class="chat-msg-bubble">
                            Hey! 👋 I'm <strong>HiveBot</strong>, your movie & series assistant. I can help you with:
                            <ul style="margin: 8px 0 0 0; padding-left: 18px;">
                                <li>Movie & TV series recommendations 🎬</li>
                                <li>Movie & show info, trivia 🎯</li>
                                <li>Using StreamHive 📖</li>
                            </ul>
                            What can I help you with?
                        </div>
                    </div>
                </div>
                <div class="chat-input-area">
                    <input type="text" id="chat-input" placeholder="Ask about movies & series..." autocomplete="off">
                    <button id="chat-send" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>
        `;

        // Enter key
        document.getElementById('chat-input').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') sendMessage();
        });
    }

    // Toggle chat window
    window.toggleChat = function() {
        isOpen = !isOpen;
        const window_ = document.getElementById('chat-window');
        const icon = document.getElementById('chat-toggle-icon');
        window_.classList.toggle('open', isOpen);
        icon.className = isOpen ? 'fas fa-times' : 'fas fa-comment-dots';
        if (isOpen) {
            document.getElementById('chat-input').focus();
        }
    };

    // Send message
    window.sendMessage = async function() {
        const input = document.getElementById('chat-input');
        const message = input.value.trim();
        if (!message) return;

        input.value = '';
        addMessage('user', message);

        // Show typing indicator
        const typingId = showTyping();

        try {
            const response = await fetch(CHAT_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message: message,
                    history: chatHistory
                })
            });

            removeTyping(typingId);

            const data = await response.json();

            if (response.ok && data.reply) {
                addMessage('bot', data.reply);
                chatHistory.push({ role: 'user', text: message });
                chatHistory.push({ role: 'model', text: data.reply });

                // Keep history manageable
                if (chatHistory.length > 20) {
                    chatHistory = chatHistory.slice(-16);
                }
            } else if (data.error) {
                // Parse specific API errors
                let errorMsg = 'Sorry, I ran into an issue. ';
                const details = typeof data.details === 'string' ? data.details : '';
                
                if (data.http_code === 429 || details.includes('quota') || details.includes('429')) {
                    errorMsg += 'My AI service has hit its usage limit. Please try again in a minute or two. ⏳';
                } else if (data.http_code === 403 || details.includes('forbidden') || details.includes('API key')) {
                    errorMsg += 'There\'s an API configuration issue. Please contact the admin. 🔑';
                } else if (details.includes('cURL') || details.includes('curl')) {
                    errorMsg += 'I can\'t reach my AI service right now. Please check your internet connection. 🔧';
                } else {
                    errorMsg += 'Could you try again in a moment? 😅';
                }
                addMessage('bot', errorMsg);
            } else {
                addMessage('bot', 'Sorry, I had trouble understanding that. Could you try again? 😅');
            }
        } catch (e) {
            removeTyping(typingId);
            addMessage('bot', 'Oops! I can\'t reach the server right now. Please check your connection and try again. 🔧');
        }
    };

    function addMessage(role, text) {
        const container = document.getElementById('chat-messages');
        const isBot = role === 'bot';

        // Simple markdown-like formatting
        let formatted = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');

        const msg = document.createElement('div');
        msg.className = `chat-msg ${role}`;
        msg.innerHTML = `
            <div class="chat-msg-avatar"><i class="fas fa-${isBot ? 'robot' : 'user'}"></i></div>
            <div class="chat-msg-bubble">${formatted}</div>
        `;
        container.appendChild(msg);
        container.scrollTop = container.scrollHeight;
    }

    function showTyping() {
        const container = document.getElementById('chat-messages');
        const id = 'typing-' + Date.now();
        const el = document.createElement('div');
        el.className = 'chat-msg bot';
        el.id = id;
        el.innerHTML = `
            <div class="chat-msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="chat-msg-bubble typing-indicator">
                <span></span><span></span><span></span>
            </div>
        `;
        container.appendChild(el);
        container.scrollTop = container.scrollHeight;
        return id;
    }

    function removeTyping(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createChatUI);
    } else {
        createChatUI();
    }
})();
