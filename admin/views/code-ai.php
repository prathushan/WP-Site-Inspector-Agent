<div class="wrap">
  <div id="wpsi-chat-container" class="wpsi-dark-container">
    <div class="wpsi-header">
      <div class="wpsi-title">AI Code Assistant</div>
      <button id="wpsi-clear-btn" class="wpsi-icon-btn" title="Clear conversation">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
      </button>
    </div>

    <div id="wpsi-chat-box" class="wpsi-dark-box">
      <div id="wpsi-placeholder">Welcome to Code AI
        <br>
        Magic Happens when AI Meets WordPress!
      </div>

    </div>

    <div class="wpsi-field-block">
      <input type="text" id="wpsi-user-input" placeholder="Type your message..." class="wpsi-dark-input" />
      <button id="wpsi-send-btn" class="button button-dark">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </button>
    </div>
  </div>
</div>

<style>
  /* Add these new styles */
  .wpsi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    background: #252525;
    border-bottom: 1px solid #333;
  }

  .wpsi-title {
    font-weight: 600;
    font-size: 16px;
    color: #e0e0e0;
  }

  .wpsi-icon-btn {
    background: transparent;
    border: none;
    color: #aaa;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.2s ease;
  }

  .wpsi-icon-btn:hover {
    color: #e0e0e0;
    background: rgba(255, 255, 255, 0.1);
  }

  .wpsi-typing {
    display: inline-flex;
    align-items: center;
  }

  .wpsi-typing-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #aaa;
    margin: 0 2px;
    animation: typingAnimation 1.4s infinite ease-in-out;
  }

  .wpsi-typing-dot:nth-child(1) {
    animation-delay: 0s;
  }

  .wpsi-typing-dot:nth-child(2) {
    animation-delay: 0.2s;
  }

  .wpsi-typing-dot:nth-child(3) {
    animation-delay: 0.4s;
  }

  @keyframes typingAnimation {

    0%,
    60%,
    100% {
      transform: translateY(0);
    }

    30% {
      transform: translateY(-5px);
    }
  }

  /* Existing styles remain the same */
  /* Container */
  .wpsi-dark-container {
    background: #1e1e1e;
    border-radius: 12px;
    padding: 0;
    margin-top: 20px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    color: #e0e0e0;
    overflow: hidden;
  }

  /* Chat Box */
  .wpsi-dark-box {
    height: 400px;
    overflow-y: auto;
    padding: 20px;
    background: #1e1e1e;
    position: relative;
    scrollbar-width: thin;
    scrollbar-color: #444 #2b2b2b;
  }

  .wpsi-dark-box::-webkit-scrollbar {
    width: 6px;
  }

  .wpsi-dark-box::-webkit-scrollbar-track {
    background: #2b2b2b;
  }

  .wpsi-dark-box::-webkit-scrollbar-thumb {
    background-color: #444;
    border-radius: 3px;
  }

  /* Placeholder */
  #wpsi-placeholder {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #666;
    font-size: 16px;
    text-align: center;
    pointer-events: none;
  }

  /* Input Area */
  .wpsi-field-block {
    display: flex;
    gap: 10px;
    padding: 16px;
    background: #252525;
    border-top: 1px solid #333;
    align-items: center;
  }

  .wpsi-dark-input {
    flex: 1;
    padding: 12px 20px;
    font-size: 14px;
    border: none;
    color: #eee;
    height: 48px;
    border-radius: 24px;
    background-color: #2b2b2b;
    outline: none;
    transition: all 0.2s ease;
  }

  .wpsi-dark-input:focus {
    box-shadow: 0 0 0 2px rgba(100, 149, 237, 0.5);
  }

  /* Send Button */
  #wpsi-send-btn {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #4d90fe;
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  }

  #wpsi-send-btn:hover {
    background: #357ae8;
    transform: translateY(-1px);
  }

  #wpsi-send-btn:active {
    transform: translateY(0);
  }

  #wpsi-send-btn svg {
    stroke: white;
  }

  #wpsi-send-btn:disabled {
    background: #666;
    cursor: not-allowed;
    transform: none;
  }

  /* Message Styles */
  .wpsi-msg {
    margin-bottom: 20px;
    animation: fadeIn 0.3s ease;
  }

  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(5px);
    }

    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  .wpsi-msg .sender {
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 14px;
    color: #aaa;
  }

  .wpsi-msg .text {
    background: #252525;
    padding: 14px;
    border-radius: 8px;
    white-space: pre-wrap;
    font-family: 'Inter', system-ui, sans-serif;
    color: #eee;
    line-height: 1.5;
    font-size: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .wpsi-msg.ai .text {
    background: #252525;
    border-top-left-radius: 0;
  }

  .wpsi-msg.user .text {
    background: #2f4f4f;
    border-top-right-radius: 0;
    margin-left: auto;
    max-width: 90%;
  }

  /* Code Block */
  .wpsi-code-block {
    background: #1a1a1a;
    color: #eee;
    font-family: 'Fira Code', 'Courier New', monospace;
    padding: 12px;
    border-radius: 6px;
    position: relative;
    margin-top: 12px;
    overflow-x: auto;
    border: 1px solid #333;
  }

  .wpsi-code-block code {
    display: block;
    white-space: pre;
    font-size: 13px;
    line-height: 1.4;
  }

  .wpsi-copy-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    background: #333;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    padding: 4px 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
  }

  .wpsi-copy-btn:hover {
    background: #444;
  }
</style>

<script>
  const chatBox = document.getElementById('wpsi-chat-box');
  const userInput = document.getElementById('wpsi-user-input');
  const sendBtn = document.getElementById('wpsi-send-btn');
  const placeholder = document.getElementById('wpsi-placeholder');
  const clearBtn = document.getElementById('wpsi-clear-btn');

  let isProcessing = false;
  let typingInterval;
  let currentTypingMessage = '';
  let typingIndex = 0;

  // Load saved messages from localStorage
  function loadMessages() {
    const savedMessages = localStorage.getItem('wpsi_chat_messages');
    if (savedMessages) {
      chatBox.innerHTML = savedMessages;
      placeholder.style.display = 'none';
      chatBox.scrollTop = chatBox.scrollHeight;
    }
  }

  // Save messages to localStorage
  function saveMessages() {
    localStorage.setItem('wpsi_chat_messages', chatBox.innerHTML);
  }

  // Clear chat history
  function clearChat() {
    if (confirm('Are you sure you want to clear the conversation?')) {
      chatBox.innerHTML = '';
      placeholder.style.display = 'block';
      localStorage.removeItem('wpsi_chat_messages');
    }
  }

  function escapeHtml(str) {
    return str.replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;',
    })[m]);
  }

  function formatMessage(text) {
    return escapeHtml(text).replace(/```([\s\S]*?)```/g, (match, code) => {
      return `<div class="wpsi-code-block"><button class="wpsi-copy-btn">Copy</button><code>${code.trim()}</code></div>`;
    });
  }

  function appendMessage(sender, text, isTyping = false) {
    if (isTyping) {
      const wrapper = document.createElement('div');
      wrapper.className = `wpsi-msg ${sender === 'AI' ? 'ai' : 'user'}`;
      wrapper.innerHTML = `
      <div class="sender">${sender === 'AI' ? 'AI' : 'You'}</div>
      <div class="text">
        <div class="wpsi-typing">
          <div class="wpsi-typing-dot"></div>
          <div class="wpsi-typing-dot"></div>
          <div class="wpsi-typing-dot"></div>
        </div>
      </div>
    `;
      chatBox.appendChild(wrapper);
      chatBox.scrollTop = chatBox.scrollHeight;
      return wrapper;
    } else {
      const wrapper = document.createElement('div');
      wrapper.className = `wpsi-msg ${sender === 'AI' ? 'ai' : 'user'}`;
      wrapper.innerHTML = `
      <div class="sender">${sender === 'AI' ? 'AI' : 'You'}</div>
      <div class="text">${formatMessage(text)}</div>
    `;
      chatBox.appendChild(wrapper);
      chatBox.scrollTop = chatBox.scrollHeight;

      // Hide placeholder
      if (placeholder) placeholder.style.display = 'none';

      wrapper.querySelectorAll('.wpsi-copy-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const code = btn.nextElementSibling.textContent;
          navigator.clipboard.writeText(code).then(() => {
            btn.textContent = 'Copied!';
            setTimeout(() => (btn.textContent = 'Copy'), 1500);
          });
        });
      });

      return wrapper;
    }
  }

  // Simulate typing animation
  function typeMessage(element, message, callback) {
    clearInterval(typingInterval);
    currentTypingMessage = message;
    typingIndex = 0;

    const textElement = element.querySelector('.text');
    textElement.innerHTML = '';

    typingInterval = setInterval(() => {
      if (typingIndex < currentTypingMessage.length) {
        const char = currentTypingMessage.charAt(typingIndex);
        textElement.innerHTML = formatMessage(currentTypingMessage.substring(0, typingIndex + 1));
        typingIndex++;
        chatBox.scrollTop = chatBox.scrollHeight;

        // Scroll code blocks into view if they're at the end
        const codeBlocks = textElement.querySelectorAll('.wpsi-code-block');
        if (codeBlocks.length > 0) {
          codeBlocks[codeBlocks.length - 1].scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
          });
        }
      } else {
        clearInterval(typingInterval);
        if (callback) callback();
      }
    }, 20); // Typing speed (lower = faster)
  }

  function sendMessage() {
    const message = userInput.value.trim();
    if (!message || isProcessing) return;

    isProcessing = true;
    sendBtn.disabled = true;
    userInput.disabled = true;

    appendMessage('You', message);
    userInput.value = '';
    const typingElement = appendMessage('AI', '', true);

    fetch(ajaxurl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          action: 'wpsi_ask_ai',
          message: message
        })
      })
      .then(res => res.json())
      .then(data => {
        const lastAI = chatBox.querySelectorAll('.wpsi-msg.ai');
        const aiElement = lastAI[lastAI.length - 1];

        if (data.success && data.data.response) {
          typeMessage(aiElement, data.data.response, () => {
            isProcessing = false;
            sendBtn.disabled = false;
            userInput.disabled = false;
            saveMessages();

            // Reattach copy button handlers after typing completes
            aiElement.querySelectorAll('.wpsi-copy-btn').forEach(btn => {
              btn.addEventListener('click', () => {
                const code = btn.nextElementSibling.textContent;
                navigator.clipboard.writeText(code).then(() => {
                  btn.textContent = 'Copied!';
                  setTimeout(() => (btn.textContent = 'Copy'), 1500);
                });
              });
            });
          });
        } else {
          aiElement.querySelector('.text').innerHTML = `<span style="color:red;">❌ Error: ${data.data?.error || 'Unknown error.'}</span>`;
          isProcessing = false;
          sendBtn.disabled = false;
          userInput.disabled = false;
          saveMessages();
        }
      })
      .catch(err => {
        const lastAI = chatBox.querySelectorAll('.wpsi-msg.ai');
        lastAI[lastAI.length - 1].querySelector('.text').innerHTML = `<span style="color:red;">❌ Request failed: ${err.message}</span>`;
        isProcessing = false;
        sendBtn.disabled = false;
        userInput.disabled = false;
        saveMessages();
      });
  }

  // Event listeners
  sendBtn.addEventListener('click', sendMessage);
  clearBtn.addEventListener('click', clearChat);
  userInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
  });

  // Initialize
  loadMessages();
</script>