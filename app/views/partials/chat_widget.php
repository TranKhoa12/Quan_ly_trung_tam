<?php
/**
 * AI Chat Widget - Trợ lý nội bộ
 * Include trong modern.php, trước thẻ </body>
 * Yêu cầu: $buildUrl closure phải có sẵn trong scope (được định nghĩa trong modern.php)
 */
$chatApiUrl = $buildUrl('api/chat');
$chatUserRole = $_SESSION['role'] ?? 'staff';
$chatUserName = htmlspecialchars($_SESSION['full_name'] ?? '', ENT_QUOTES);
?>

<!-- ============================================================
     AI CHAT WIDGET
     ============================================================ -->
<div id="aiChatWidget" role="complementary" aria-label="Trợ lý AI">

    <!-- Floating toggle button -->
    <button id="chatToggleBtn" class="chat-toggle-btn" title="Trợ lý AI - Hỏi đáp nội bộ" aria-expanded="false">
        <i class="fas fa-comment-dots" id="chatOpenIcon"></i>
        <i class="fas fa-times"        id="chatCloseIcon" style="display:none"></i>
        <span class="chat-unread-badge" id="chatBadge" style="display:none" aria-label="Tin nhắn mới"></span>
    </button>

    <!-- Chat window -->
    <div id="chatWindow" class="chat-window" style="display:none" role="dialog" aria-label="Cửa sổ chat AI">

        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-bot-avatar">
                    <i class="fas fa-robot"></i>
                    <span class="chat-online-dot"></span>
                </div>
                <div>
                    <div class="chat-header-title">Trợ lý AI</div>
                    <div class="chat-header-sub">Hỏi đáp dữ liệu trung tâm</div>
                </div>
            </div>
            <div class="chat-header-actions">
                <button class="chat-action-btn" id="chatClearBtn" title="Xóa hội thoại">
                    <i class="fas fa-eraser"></i>
                </button>
                <button class="chat-action-btn" id="chatMinimizeBtn" title="Thu nhỏ">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <!-- Messages -->
        <div id="chatMessages" class="chat-messages" aria-live="polite">
            <div class="chat-msg bot" data-initial>
                <div class="msg-avatar"><i class="fas fa-robot"></i></div>
                <div class="msg-bubble">
                    Xin chào <strong><?= $chatUserName ?></strong>! 👋<br>
                    Tôi có thể giúp bạn tra cứu về:
                    <ul class="chat-intro-list">
                        <li>💰 Học phí, bảng giá khóa học</li>
                        <li>📊 Doanh thu, báo cáo học viên</li>
                        <li>🎓 Thông tin học viên cụ thể</li>
                        <li>📋 Thống kê tổng quan</li>
                    </ul>
                    Bạn muốn hỏi gì?
                </div>
            </div>
        </div>

        <!-- Suggestion chips -->
        <div class="chat-suggestions" id="chatSuggestions">
            <button class="chip" data-q="Doanh thu tháng này là bao nhiêu?">📊 Doanh thu tháng này</button>
            <button class="chip" data-q="Cho tôi xem bảng giá các khóa học">💰 Bảng giá</button>
            <button class="chip" data-q="Hôm nay có bao nhiêu khách đến?">👥 Báo cáo hôm nay</button>
            <button class="chip" data-q="Tổng quan học viên hiện tại">🎓 Tổng quan</button>
        </div>

        <!-- Input -->
        <div class="chat-input-wrap">
            <input
                type="text" id="chatInput" class="chat-input"
                placeholder="Nhập câu hỏi (Enter để gửi)..."
                maxlength="500" autocomplete="off"
                aria-label="Nhập câu hỏi"
            >
            <button id="chatSendBtn" class="chat-send-btn" title="Gửi">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
        <div class="chat-char-count"><span id="chatCharCount">0</span>/500</div>
    </div>
</div>

<!-- ============================================================
     STYLES
     ============================================================ -->
<style>
/* ── Container ─────────────────────────────────────────────── */
#aiChatWidget {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9000;
    font-family: 'Inter', sans-serif;
}

/* ── Toggle button ──────────────────────────────────────────── */
.chat-toggle-btn {
    position: relative;
    width: 54px;
    height: 54px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    font-size: 21px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 20px rgba(79, 70, 229, .45), 0 0 0 0 rgba(79,70,229,.3);
    transition: transform .2s, box-shadow .2s;
    animation: chatPulse 3s infinite;
}
.chat-toggle-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 28px rgba(79,70,229,.55);
    animation: none;
}
@keyframes chatPulse {
    0%, 100% { box-shadow: 0 4px 20px rgba(79,70,229,.45), 0 0 0 0 rgba(79,70,229,.3); }
    50%       { box-shadow: 0 4px 20px rgba(79,70,229,.45), 0 0 0 10px rgba(79,70,229,0); }
}

.chat-unread-badge {
    position: absolute;
    top: 3px; right: 3px;
    width: 14px; height: 14px;
    background: #ef4444;
    border-radius: 50%;
    border: 2px solid #fff;
    animation: badgePop .3s ease;
}
@keyframes badgePop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

/* ── Window ─────────────────────────────────────────────────── */
.chat-window {
    position: absolute;
    bottom: 66px;
    right: 0;
    width: 370px;
    max-height: 560px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(0,0,0,.16), 0 0 0 1px rgba(0,0,0,.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: chatSlideUp .25s cubic-bezier(.16,1,.3,1);
}
@keyframes chatSlideUp {
    from { opacity: 0; transform: translateY(16px) scale(.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ── Header ─────────────────────────────────────────────────── */
.chat-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-shrink: 0;
}
.chat-header-left { display: flex; align-items: center; gap: 10px; }
.chat-bot-avatar {
    position: relative;
    width: 38px; height: 38px;
    background: rgba(255,255,255,.2);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.chat-online-dot {
    position: absolute;
    bottom: 1px; right: 1px;
    width: 10px; height: 10px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid #6d28d9;
}
.chat-header-title  { font-weight: 700; font-size: 14px; line-height: 1.2; }
.chat-header-sub    { font-size: 11px; opacity: .8; }
.chat-header-actions { display: flex; gap: 4px; }
.chat-action-btn {
    background: rgba(255,255,255,.15);
    border: none; color: #fff; cursor: pointer;
    border-radius: 8px; width: 30px; height: 30px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; transition: background .2s;
}
.chat-action-btn:hover { background: rgba(255,255,255,.28); }

/* ── Messages ───────────────────────────────────────────────── */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 14px 14px 8px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f8fafc;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}
.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

/* Each message */
.chat-msg {
    display: flex;
    align-items: flex-end;
    gap: 7px;
    animation: msgFadeIn .2s ease;
}
@keyframes msgFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}
.chat-msg.bot  { flex-direction: row; }
.chat-msg.user { flex-direction: row-reverse; }

.msg-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: #4f46e5;
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    flex-shrink: 0;
    margin-bottom: 2px;
}
.chat-msg.user .msg-avatar { background: #10b981; }

.msg-bubble {
    max-width: 80%;
    padding: 10px 13px;
    border-radius: 16px;
    font-size: 13.5px;
    line-height: 1.55;
    word-break: break-word;
}
.chat-msg.bot .msg-bubble {
    background: #fff;
    color: #1f2937;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,.07);
}
.chat-msg.user .msg-bubble {
    background: #4f46e5;
    color: #fff;
    border-bottom-right-radius: 4px;
}

/* Intro list */
.chat-intro-list {
    margin: 6px 0 0 0;
    padding-left: 4px;
    list-style: none;
    font-size: 13px;
}
.chat-intro-list li { margin: 3px 0; }

/* Timestamp */
.msg-time {
    font-size: 10px;
    opacity: .55;
    margin-top: 3px;
    display: block;
    text-align: right;
}
.chat-msg.bot  .msg-time { text-align: left; }

/* Typing */
.typing-bubble .dot {
    display: inline-block;
    width: 7px; height: 7px;
    background: #9ca3af;
    border-radius: 50%;
    margin: 0 2px;
    animation: typingBounce 1.3s infinite ease-in-out;
}
.typing-bubble .dot:nth-child(2) { animation-delay: .18s; }
.typing-bubble .dot:nth-child(3) { animation-delay: .36s; }
@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0);   opacity: .6; }
    30%           { transform: translateY(-7px); opacity: 1;  }
}

/* ── Suggestions ────────────────────────────────────────────── */
.chat-suggestions {
    padding: 8px 12px 0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    background: #f8fafc;
    flex-shrink: 0;
}
.chip {
    background: #ede9fe;
    color: #4f46e5;
    border: 1px solid #c4b5fd;
    border-radius: 20px;
    padding: 4px 11px;
    font-size: 12px;
    cursor: pointer;
    transition: background .15s, transform .1s;
    white-space: nowrap;
    font-family: inherit;
}
.chip:hover  { background: #c4b5fd; transform: translateY(-1px); }
.chip:active { transform: translateY(0); }

/* ── Input area ─────────────────────────────────────────────── */
.chat-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px 4px;
    background: #fff;
    border-top: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.chat-input {
    flex: 1;
    border: 1.5px solid #e5e7eb;
    border-radius: 22px;
    padding: 9px 15px;
    font-size: 13.5px;
    outline: none;
    transition: border-color .2s;
    font-family: inherit;
    background: #f8fafc;
}
.chat-input:focus { border-color: #4f46e5; background: #fff; }
.chat-send-btn {
    width: 38px; height: 38px;
    background: #4f46e5;
    color: #fff;
    border: none; cursor: pointer;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px;
    transition: background .2s, transform .1s;
    flex-shrink: 0;
}
.chat-send-btn:hover    { background: #4338ca; transform: scale(1.05); }
.chat-send-btn:disabled { background: #d1d5db; cursor: not-allowed; transform: none; }
.chat-char-count {
    text-align: right;
    font-size: 10px;
    color: #9ca3af;
    padding: 2px 14px 8px;
    flex-shrink: 0;
}

/* ── Mobile ─────────────────────────────────────────────────── */
@media (max-width: 480px) {
    .chat-window { width: calc(100vw - 24px); right: 0; max-height: 75vh; }
    #aiChatWidget { bottom: 16px; right: 12px; }
}
</style>

<!-- ============================================================
     SCRIPT
     ============================================================ -->
<script>
(function () {
    'use strict';

    const API_URL  = <?= json_encode($chatApiUrl) ?>;
    const USER_ROLE = <?= json_encode($chatUserRole) ?>;

    // ── DOM refs ────────────────────────────────────────────────
    const widget      = document.getElementById('aiChatWidget');
    const toggleBtn   = document.getElementById('chatToggleBtn');
    const openIcon    = document.getElementById('chatOpenIcon');
    const closeIcon   = document.getElementById('chatCloseIcon');
    const chatWindow  = document.getElementById('chatWindow');
    const messagesEl  = document.getElementById('chatMessages');
    const inputEl     = document.getElementById('chatInput');
    const sendBtn     = document.getElementById('chatSendBtn');
    const clearBtn    = document.getElementById('chatClearBtn');
    const minimizeBtn = document.getElementById('chatMinimizeBtn');
    const suggestEl   = document.getElementById('chatSuggestions');
    const badge       = document.getElementById('chatBadge');
    const charCount   = document.getElementById('chatCharCount');

    // ── State ───────────────────────────────────────────────────
    let isOpen    = false;
    let isLoading = false;
    // Lịch sử gửi kèm API (không bao gồm tin chào mừng ban đầu)
    let history   = [];

    // ── Toggle ──────────────────────────────────────────────────
    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen;
        chatWindow.style.display = isOpen ? 'flex' : 'none';
        openIcon.style.display   = isOpen ? 'none'   : 'inline';
        closeIcon.style.display  = isOpen ? 'inline' : 'none';
        toggleBtn.setAttribute('aria-expanded', String(isOpen));
        badge.style.display = 'none';

        if (isOpen) {
            inputEl.focus();
            scrollBottom();
        }
    });

    minimizeBtn.addEventListener('click', () => {
        isOpen = false;
        chatWindow.style.display = 'none';
        openIcon.style.display   = 'inline';
        closeIcon.style.display  = 'none';
        toggleBtn.setAttribute('aria-expanded', 'false');
    });

    // ── Input ────────────────────────────────────────────────────
    inputEl.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
    inputEl.addEventListener('input', () => {
        charCount.textContent = inputEl.value.length;
    });
    sendBtn.addEventListener('click', sendMessage);

    // ── Suggestion chips ─────────────────────────────────────────
    suggestEl.querySelectorAll('.chip').forEach(btn => {
        btn.addEventListener('click', () => {
            inputEl.value = btn.dataset.q;
            charCount.textContent = inputEl.value.length;
            sendMessage();
        });
    });

    // ── Clear ────────────────────────────────────────────────────
    clearBtn.addEventListener('click', () => {
        if (!confirm('Xóa toàn bộ lịch sử hội thoại?')) return;
        history = [];
        // Giữ lại tin chào mừng ban đầu
        const initial = messagesEl.querySelector('[data-initial]');
        messagesEl.innerHTML = '';
        if (initial) messagesEl.appendChild(initial);
        suggestEl.style.display = 'flex';
    });

    // ── Send ─────────────────────────────────────────────────────
    function sendMessage() {
        if (isLoading) return;
        const text = inputEl.value.trim();
        if (!text) return;

        inputEl.value = '';
        charCount.textContent = '0';
        suggestEl.style.display = 'none'; // Ẩn gợi ý sau lần đầu

        appendUserMsg(text);
        history.push({ role: 'user', text });

        showTyping();
        isLoading   = true;
        sendBtn.disabled = true;

        fetch(API_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ message: text, history }),
        })
        .then(r => r.ok ? r.json() : Promise.reject('HTTP ' + r.status))
        .then(data => {
            removeTyping();
            const reply = data.reply || 'Không nhận được phản hồi.';
            appendBotMsg(reply);
            history.push({ role: 'bot', text: reply });
        })
        .catch(err => {
            removeTyping();
            appendBotMsg('❌ Có lỗi xảy ra. Vui lòng thử lại hoặc liên hệ trực tiếp.');
            console.error('[Chat]', err);
        })
        .finally(() => {
            isLoading = false;
            sendBtn.disabled = false;
            inputEl.focus();
        });
    }

    // ── Render helpers ───────────────────────────────────────────
    function appendUserMsg(text) {
        const now = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const el  = document.createElement('div');
        el.className = 'chat-msg user';
        el.innerHTML = `
            <div class="msg-bubble">${esc(text)}<span class="msg-time">${now}</span></div>
            <div class="msg-avatar"><i class="fas fa-user"></i></div>`;
        messagesEl.appendChild(el);
        scrollBottom();
    }

    function appendBotMsg(text) {
        const now = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
        const el  = document.createElement('div');
        el.className = 'chat-msg bot';
        el.innerHTML = `
            <div class="msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="msg-bubble">${formatBot(text)}<span class="msg-time">${now}</span></div>`;
        messagesEl.appendChild(el);
        scrollBottom();

        // Nếu cửa sổ đang đóng → hiện badge
        if (!isOpen) badge.style.display = 'block';
    }

    function showTyping() {
        const el = document.createElement('div');
        el.id = 'typingDots';
        el.className = 'chat-msg bot';
        el.innerHTML = `
            <div class="msg-avatar"><i class="fas fa-robot"></i></div>
            <div class="msg-bubble typing-bubble">
                <span class="dot"></span><span class="dot"></span><span class="dot"></span>
            </div>`;
        messagesEl.appendChild(el);
        scrollBottom();
    }

    function removeTyping() {
        const el = document.getElementById('typingDots');
        if (el) el.remove();
    }

    function scrollBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    // Escape HTML để chống XSS từ user input
    function esc(str) {
        return str
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // Format text từ AI: **bold**, newline → <br>
    function formatBot(str) {
        return esc(str)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g,     '<em>$1</em>')
            .replace(/\n/g,            '<br>');
    }
})();
</script>
