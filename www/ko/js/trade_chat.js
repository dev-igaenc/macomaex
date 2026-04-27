(function () {
    var pageData = {};
    var statusEl = null;
    var messagesEl = null;
    var formEl = null;
    var inputEl = null;
    var submitBtn = null;
    var MESSAGE_TEXT_MAX_LENGTH = 2000;

    function setStatus(message, type) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.dataset.type = type || 'info';
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatTime(value) {
        if (!value) return '';
        var date = new Date(value);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleString();
    }

    function normalizeMessageText(value) {
        return String(value || '')
            .replace(/[\u0000-\u0008\u000B\u000C\u000E-\u001F\u007F]/g, '')
            .trim();
    }

    function renderMessages(messages) {
        if (!messagesEl) return;
        if (!Array.isArray(messages) || messages.length === 0) {
            messagesEl.innerHTML = '<div class="trade-chat-empty">아직 메시지가 없습니다.</div>';
            return;
        }

        messagesEl.innerHTML = messages.map(function (message) {
            var mine = message.user && message.user.id === pageData.user.id;
            return '<article class="trade-chat-message' + (mine ? ' is-mine' : '') + '">' +
                '<div class="trade-chat-message-meta">' +
                    '<strong>' + escapeHtml((message.user && (message.user.name || message.user.id)) || 'unknown') + '</strong>' +
                    '<span>' + escapeHtml(formatTime(message.created_at)) + '</span>' +
                '</div>' +
                '<p>' + escapeHtml(message.text || '') + '</p>' +
            '</article>';
        }).join('');

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function enableForm(enabled) {
        if (inputEl) inputEl.disabled = !enabled;
        if (submitBtn) submitBtn.disabled = !enabled;
    }

    async function boot() {
        pageData = window.__TRADE_CHAT_PAGE_DATA || {};
        statusEl = document.getElementById('tradeChatStatus');
        messagesEl = document.getElementById('tradeChatMessages');
        formEl = document.getElementById('tradeChatForm');
        inputEl = document.getElementById('tradeChatInput');
        submitBtn = formEl ? formEl.querySelector('button[type="submit"]') : null;

        var StreamChatCtor = window.StreamChat && (window.StreamChat.StreamChat || window.StreamChat);
        if (!StreamChatCtor) {
            setStatus('Stream JS SDK를 불러오지 못했습니다.', 'error');
            return;
        }

        if (!pageData.apiKey || !pageData.token || !pageData.channelType || !pageData.channelId || !pageData.user || !pageData.user.id) {
            setStatus('채팅 초기화 정보가 부족합니다.', 'error');
            return;
        }

        try {
            var client = StreamChatCtor.getInstance
                ? StreamChatCtor.getInstance(pageData.apiKey)
                : new StreamChatCtor(pageData.apiKey);

            await client.connectUser(pageData.user, pageData.token);

            var channel = client.channel(pageData.channelType, pageData.channelId);
            await channel.watch();

            renderMessages(channel.state && channel.state.messages ? channel.state.messages : []);
            setStatus('채팅 서버에 연결되었습니다.', 'success');
            enableForm(true);

            channel.on('message.new', function () {
                renderMessages(channel.state && channel.state.messages ? channel.state.messages : []);
            });

            if (formEl) {
                formEl.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    var text = normalizeMessageText(inputEl ? inputEl.value : '');
                    if (!text) return;
                    if (text.length > MESSAGE_TEXT_MAX_LENGTH) {
                        setStatus('메시지는 2000자 이하로 입력해 주세요.', 'error');
                        return;
                    }

                    enableForm(false);
                    try {
                        await channel.sendMessage({ text: text });
                        inputEl.value = '';
                    } catch (error) {
                        setStatus('메시지 전송에 실패했습니다: ' + (error && error.message ? error.message : 'unknown'), 'error');
                    } finally {
                        enableForm(true);
                        if (inputEl) inputEl.focus();
                    }
                });
            }
        } catch (error) {
            setStatus('채팅 연결에 실패했습니다: ' + (error && error.message ? error.message : 'unknown'), 'error');
            enableForm(false);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
