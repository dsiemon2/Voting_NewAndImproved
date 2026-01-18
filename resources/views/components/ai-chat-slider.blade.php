@php
    $position = $position ?? 'bottom-right';
    $buttonColor = $buttonColor ?? '#1e40af';
    $panelWidth = $panelWidth ?? 380;
    $voiceInput = $voiceInput ?? true;
    $voiceOutput = $voiceOutput ?? true;
@endphp

<!-- AI Chat Slider -->
<div id="ai-chat-container" class="ai-chat-container {{ $position }}">
    <!-- Toggle Button -->
    <button id="ai-chat-toggle" class="ai-chat-toggle" style="background: {{ $buttonColor }};" onclick="toggleAiChat()">
        <i class="fas fa-robot" id="ai-chat-icon"></i>
        <i class="fas fa-times" id="ai-close-icon" style="display: none;"></i>
    </button>

    <!-- Chat Panel -->
    <div id="ai-chat-panel" class="ai-chat-panel" style="width: {{ $panelWidth }}px;">
        <!-- Header -->
        <div class="ai-chat-header" style="background: {{ $buttonColor }};">
            <div class="ai-chat-header-info">
                <div class="ai-chat-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div>
                    <strong>AI Assistant</strong>
                    <span class="ai-status"><i class="fas fa-circle"></i> Online</span>
                </div>
            </div>
            <button class="ai-chat-minimize" onclick="toggleAiChat()">
                <i class="fas fa-minus"></i>
            </button>
        </div>

        <!-- Messages -->
        <div id="ai-chat-messages" class="ai-chat-messages">
            <div class="ai-message ai-message-bot">
                <div class="ai-message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="ai-message-content">
                    <p>Hello! I'm your AI voting assistant. I can help you create events, add participants, and more. What would you like to do?</p>
                    <span class="ai-message-time">Just now</span>
                </div>
            </div>
        </div>

        <!-- Dynamic Quick Actions (updated based on response) -->
        <div id="ai-quick-actions" class="ai-quick-actions">
            <button onclick="sendQuickAction('create event')">Create Event</button>
            <button onclick="sendQuickAction('active events')">Active Events</button>
            <button onclick="sendQuickAction('show results')">View Results</button>
        </div>

        <!-- Wizard Options (shown during wizard flow) -->
        <div id="ai-wizard-options" class="ai-wizard-options" style="display: none;">
            <!-- Dynamically populated -->
        </div>

        <!-- Voice Mode Indicator (shown when listening) -->
        <div id="ai-voice-indicator" class="ai-voice-indicator" style="display: none;">
            <div class="ai-voice-visualizer">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
            <div class="ai-voice-status">
                <span id="ai-voice-status-text">Listening...</span>
            </div>
            <div id="ai-voice-transcript" class="ai-voice-transcript"></div>
            <button class="ai-voice-stop-btn" onclick="stopVoiceInput()">
                <i class="fas fa-stop"></i> Stop Listening
            </button>
        </div>

        <!-- Input -->
        <div class="ai-chat-input">
            @if($voiceInput)
            <button id="ai-voice-btn" class="ai-voice-btn" onclick="toggleVoiceInput()" title="Voice input">
                <i class="fas fa-microphone" id="ai-voice-icon"></i>
            </button>
            @endif
            <input type="text" id="ai-chat-input-field" placeholder="Type your message..." onkeypress="handleKeyPress(event)">
            <button class="ai-send-btn" onclick="sendMessage()" style="background: {{ $buttonColor }};">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .ai-chat-container {
        position: fixed;
        z-index: 9999;
    }
    .ai-chat-container.bottom-right {
        bottom: 20px;
        right: 20px;
    }
    .ai-chat-container.bottom-left {
        bottom: 20px;
        left: 20px;
    }
    .ai-chat-container.top-right {
        top: 20px;
        right: 20px;
    }
    .ai-chat-container.top-left {
        top: 20px;
        left: 20px;
    }

    .ai-chat-toggle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ai-chat-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(0,0,0,0.4);
    }

    .ai-chat-panel {
        position: absolute;
        bottom: 70px;
        right: 0;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        display: none;
        flex-direction: column;
        max-height: 550px;
        overflow: hidden;
        animation: slideUp 0.3s ease;
    }
    .ai-chat-container.bottom-left .ai-chat-panel,
    .ai-chat-container.top-left .ai-chat-panel {
        right: auto;
        left: 0;
    }
    .ai-chat-container.top-right .ai-chat-panel,
    .ai-chat-container.top-left .ai-chat-panel {
        bottom: auto;
        top: 70px;
    }
    .ai-chat-panel.open {
        display: flex;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ai-chat-header {
        padding: 15px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 16px 16px 0 0;
    }
    .ai-chat-header-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .ai-chat-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .ai-status {
        font-size: 12px;
        opacity: 0.9;
        display: block;
    }
    .ai-status i {
        font-size: 8px;
        color: #10b981;
        margin-right: 4px;
    }
    .ai-chat-minimize {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .ai-chat-minimize:hover {
        background: rgba(255,255,255,0.3);
    }

    .ai-chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background: #f9fafb;
        max-height: 300px;
    }

    .ai-message {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }
    .ai-message-bot .ai-message-avatar {
        width: 32px;
        height: 32px;
        background: #2563eb;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        flex-shrink: 0;
    }
    .ai-message-user {
        flex-direction: row-reverse;
    }
    .ai-message-user .ai-message-avatar {
        width: 32px;
        height: 32px;
        background: #6b7280;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
        flex-shrink: 0;
    }
    .ai-message-content {
        background: white;
        padding: 10px 14px;
        border-radius: 12px;
        border-top-left-radius: 4px;
        max-width: 85%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .ai-message-user .ai-message-content {
        background: #2563eb;
        color: white;
        border-top-left-radius: 12px;
        border-top-right-radius: 4px;
    }
    .ai-message-content p {
        margin: 0;
        line-height: 1.5;
        font-size: 14px;
    }
    .ai-message-time {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 5px;
        display: block;
    }
    .ai-message-user .ai-message-time {
        color: rgba(255,255,255,0.7);
    }

    .ai-quick-actions {
        padding: 10px 15px;
        background: white;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .ai-quick-actions button {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        color: #374151;
    }
    .ai-quick-actions button:hover {
        background: #e5e7eb;
        border-color: #d1d5db;
    }

    /* Wizard Options */
    .ai-wizard-options {
        padding: 10px 15px;
        background: #eff6ff;
        border-top: 1px solid #bfdbfe;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .ai-wizard-options button {
        background: #1e40af;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        color: white;
    }
    .ai-wizard-options button:hover {
        background: #1e3a8a;
    }
    .ai-wizard-options button.cancel-btn {
        background: #f3f4f6;
        color: #64748b;
        margin-left: auto;
    }
    .ai-wizard-options button.cancel-btn:hover {
        background: #e5e7eb;
        color: #374151;
    }
    .ai-wizard-progress {
        width: 100%;
        font-size: 11px;
        color: #64748b;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ai-wizard-progress .progress-bar {
        flex: 1;
        height: 4px;
        background: #dbeafe;
        border-radius: 2px;
        overflow: hidden;
    }
    .ai-wizard-progress .progress-bar-fill {
        height: 100%;
        background: #1e40af;
        transition: width 0.3s ease;
    }

    .ai-chat-input {
        padding: 15px;
        background: white;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .ai-chat-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 25px;
        outline: none;
        font-size: 14px;
    }
    .ai-chat-input input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    .ai-voice-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .ai-voice-btn:hover {
        background: #f3f4f6;
        color: #dc2626;
    }
    .ai-voice-btn.recording {
        background: #dc2626;
        color: white;
        border-color: #dc2626;
        animation: pulse 1.5s infinite;
    }
    .ai-voice-btn.permission-denied {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
        cursor: not-allowed;
    }

    /* Voice Mode Indicator */
    .ai-voice-indicator {
        padding: 15px;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        border-top: 1px solid #1e3a8a;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: white;
    }
    .ai-voice-visualizer {
        display: flex;
        gap: 4px;
        align-items: center;
        height: 30px;
    }
    .ai-voice-visualizer span {
        width: 4px;
        background: white;
        border-radius: 2px;
        animation: soundwave 0.5s ease-in-out infinite;
    }
    .ai-voice-visualizer span:nth-child(1) { height: 10px; animation-delay: 0s; }
    .ai-voice-visualizer span:nth-child(2) { height: 20px; animation-delay: 0.1s; }
    .ai-voice-visualizer span:nth-child(3) { height: 25px; animation-delay: 0.2s; }
    .ai-voice-visualizer span:nth-child(4) { height: 20px; animation-delay: 0.3s; }
    .ai-voice-visualizer span:nth-child(5) { height: 10px; animation-delay: 0.4s; }
    @keyframes soundwave {
        0%, 100% { transform: scaleY(1); }
        50% { transform: scaleY(1.5); }
    }
    .ai-voice-status {
        font-size: 14px;
        font-weight: 500;
    }
    .ai-voice-transcript {
        font-size: 13px;
        opacity: 0.9;
        text-align: center;
        min-height: 20px;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .ai-voice-stop-btn {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: white;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
    }
    .ai-voice-stop-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .ai-send-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .ai-send-btn:hover {
        transform: scale(1.1);
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    /* Typing indicator */
    .typing-dots {
        display: flex;
        gap: 4px;
        padding: 5px 0;
    }
    .typing-dots span {
        width: 8px;
        height: 8px;
        background: #9ca3af;
        border-radius: 50%;
        animation: bounce 1.4s infinite ease-in-out both;
    }
    .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
    .typing-dots span:nth-child(2) { animation-delay: -0.16s; }
    .typing-dots span:nth-child(3) { animation-delay: 0s; }

    @keyframes bounce {
        0%, 80%, 100% { transform: scale(0); }
        40% { transform: scale(1); }
    }

    /* Mobile responsive */
    @media screen and (max-width: 480px) {
        .ai-chat-panel {
            width: calc(100vw - 40px) !important;
            max-width: 100%;
        }
    }

    /* Visual Aid Components */
    .ai-step-card {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        margin-top: 10px;
    }
    .ai-step-card-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        color: #1e40af;
        font-weight: 600;
        font-size: 12px;
    }
    .ai-step-card-header i {
        font-size: 14px;
    }
    .ai-step-progress {
        display: flex;
        gap: 6px;
        margin-bottom: 12px;
    }
    .ai-step-progress-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #cbd5e1;
    }
    .ai-step-progress-dot.active {
        background: #1e40af;
    }
    .ai-step-progress-dot.completed {
        background: #10b981;
    }
    .ai-step-item {
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .ai-step-item:last-child {
        border-bottom: none;
    }
    .ai-step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        background: #1e40af;
        color: white;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 600;
        margin-right: 8px;
    }
    .ai-step-title {
        font-weight: 500;
        font-size: 13px;
        color: #1e293b;
    }
    .ai-step-content {
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
        padding-left: 28px;
    }

    /* Stats Card */
    .ai-stats-card {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    .ai-stat-item {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        padding: 10px 14px;
        border-radius: 10px;
        text-align: center;
        min-width: 70px;
        flex: 1;
    }
    .ai-stat-value {
        font-size: 20px;
        font-weight: 700;
    }
    .ai-stat-label {
        font-size: 10px;
        opacity: 0.9;
        text-transform: uppercase;
    }

    /* Ranking Card */
    .ai-ranking-card {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border: 1px solid #fcd34d;
        border-radius: 12px;
        padding: 12px;
        margin-top: 10px;
    }
    .ai-ranking-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        font-weight: 600;
        color: #92400e;
        font-size: 12px;
    }
    .ai-ranking-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0;
    }
    .ai-ranking-medal {
        font-size: 18px;
    }
    .ai-ranking-name {
        flex: 1;
        font-weight: 500;
        font-size: 13px;
    }
    .ai-ranking-points {
        font-weight: 600;
        color: #92400e;
        font-size: 13px;
    }

    /* AI indicator */
    .ai-powered-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: linear-gradient(135deg, #8b5cf6 0%, #6366f1 100%);
        color: white;
        font-size: 9px;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 6px;
    }
</style>

<script>
    let aiChatOpen = false;
    let currentWizardState = null;
    let conversationHistory = []; // Store conversation for AI context

    function toggleAiChat() {
        aiChatOpen = !aiChatOpen;
        const panel = document.getElementById('ai-chat-panel');
        const robotIcon = document.getElementById('ai-chat-icon');
        const closeIcon = document.getElementById('ai-close-icon');

        if (aiChatOpen) {
            panel.classList.add('open');
            robotIcon.style.display = 'none';
            closeIcon.style.display = 'block';
        } else {
            panel.classList.remove('open');
            robotIcon.style.display = 'block';
            closeIcon.style.display = 'none';
        }
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter') {
            sendMessage();
        }
    }

    // Get current event ID from cookie
    function getCurrentEventId() {
        const match = document.cookie.match(/managing_event_id=(\d+)/);
        return match ? match[1] : null;
    }

    async function sendMessage(overrideMessage = null) {
        const input = document.getElementById('ai-chat-input-field');
        const message = overrideMessage || input.value.trim();
        if (!message) return;

        // Add user message to history and UI
        conversationHistory.push({ role: 'user', content: message });
        addMessage(message, 'user');
        if (!overrideMessage) input.value = '';

        // Show typing indicator
        showTyping();

        // Get current event context
        const eventId = getCurrentEventId();

        try {
            const response = await fetch('/api/ai-chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    message: message,
                    event_id: eventId,
                    wizard_state: currentWizardState,
                    conversation_history: conversationHistory.slice(-10) // Last 10 messages for context
                })
            });

            hideTyping();

            if (response.ok) {
                const data = await response.json();

                // Handle event switching with page refresh
                if (data.switchToEvent) {
                    // Show the message first
                    conversationHistory.push({ role: 'assistant', content: data.message });
                    addMessage(data.message, 'bot', data.visualAids, data.type === 'ai');

                    // Set the managing event cookie
                    document.cookie = `managing_event_id=${data.switchToEvent.id}; path=/; max-age=31536000`;

                    // Show a brief message then refresh
                    if (data.switchToEvent.refreshPage) {
                        setTimeout(() => {
                            window.location.href = data.switchToEvent.url;
                        }, 1000); // Brief delay to show message
                    }
                    return;
                }

                // Handle clearing event context (back to main menu)
                if (data.clearEvent) {
                    // Show the message first
                    conversationHistory.push({ role: 'assistant', content: data.message });
                    addMessage(data.message, 'bot', data.visualAids, data.type === 'ai');

                    // Clear the managing event cookie
                    document.cookie = 'managing_event_id=; path=/; max-age=0';

                    // Redirect to dashboard after brief delay
                    if (data.redirectUrl) {
                        setTimeout(() => {
                            window.location.href = data.redirectUrl;
                        }, 1000);
                    }
                    return;
                }

                // Handle event options (clickable list of events)
                if (data.eventOptions && data.eventOptions.length > 0) {
                    conversationHistory.push({ role: 'assistant', content: data.message });
                    addMessage(data.message, 'bot', data.visualAids, data.type === 'ai');
                    showEventOptions(data.eventOptions);
                    return;
                }

                // Add assistant response to history
                conversationHistory.push({ role: 'assistant', content: data.message });

                // Render message with visual aids
                addMessage(data.message, 'bot', data.visualAids, data.type === 'ai');

                // Handle wizard state
                if (data.wizardState) {
                    currentWizardState = data.wizardState;
                    showWizardOptions(data.wizardState);
                } else {
                    currentWizardState = null;
                    hideWizardOptions();
                }

                // Handle suggested actions
                if (data.suggestedActions && data.suggestedActions.length > 0) {
                    updateQuickActions(data.suggestedActions);
                }
            } else {
                // Show more details about the error
                let errorMsg = "Sorry, I couldn't process your request.";
                try {
                    const errorData = await response.json();
                    if (errorData.message) {
                        errorMsg += ` (${response.status}: ${errorData.message})`;
                    }
                } catch (e) {
                    errorMsg += ` (HTTP ${response.status})`;
                }
                addMessage(errorMsg, 'bot');
                console.error('AI Chat Error:', response.status, response.statusText);
            }
        } catch (error) {
            hideTyping();
            addMessage("Sorry, there was an error connecting to the server: " + error.message, 'bot');
            console.error('AI Chat Error:', error);
        }
    }

    function sendQuickAction(action) {
        sendMessage(action);
    }

    function showWizardOptions(wizardState) {
        const container = document.getElementById('ai-wizard-options');
        const quickActions = document.getElementById('ai-quick-actions');

        quickActions.style.display = 'none';
        container.style.display = 'flex';
        container.innerHTML = '';

        // Progress indicator
        if (wizardState.totalSteps > 1) {
            const progress = ((wizardState.currentStep + 1) / wizardState.totalSteps) * 100;
            const progressHtml = `
                <div class="ai-wizard-progress">
                    <span>Step ${wizardState.currentStep + 1} of ${wizardState.totalSteps}</span>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: ${progress}%"></div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', progressHtml);
        }

        // Option buttons
        if (wizardState.options && wizardState.options.length > 0) {
            wizardState.options.forEach(option => {
                const btn = document.createElement('button');
                btn.textContent = option.label;
                btn.onclick = () => sendMessage(option.value.toString());
                container.appendChild(btn);
            });
        }

        // Skip button if allowed
        if (wizardState.canSkip) {
            const skipBtn = document.createElement('button');
            skipBtn.textContent = 'Skip';
            skipBtn.className = 'cancel-btn';
            skipBtn.onclick = () => sendMessage('skip');
            container.appendChild(skipBtn);
        }

        // Cancel button
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.className = 'cancel-btn';
        cancelBtn.onclick = () => sendMessage('cancel');
        container.appendChild(cancelBtn);
    }

    function hideWizardOptions() {
        const container = document.getElementById('ai-wizard-options');
        const quickActions = document.getElementById('ai-quick-actions');

        container.style.display = 'none';
        quickActions.style.display = 'flex';
    }

    function showEventOptions(events) {
        const container = document.getElementById('ai-wizard-options');
        const quickActions = document.getElementById('ai-quick-actions');

        quickActions.style.display = 'none';
        container.style.display = 'flex';
        container.innerHTML = '';

        // Header
        const header = document.createElement('div');
        header.className = 'ai-wizard-progress';
        header.innerHTML = '<span>Select an event to manage:</span>';
        container.appendChild(header);

        // Event buttons
        events.forEach(event => {
            const btn = document.createElement('button');
            btn.innerHTML = `<i class="fas fa-calendar-alt"></i> ${event.name}`;
            btn.style.cssText = 'display: flex; align-items: center; gap: 6px;';
            btn.onclick = () => {
                sendMessage(`Manage ${event.name}`);
            };
            container.appendChild(btn);
        });

        // Cancel button
        const cancelBtn = document.createElement('button');
        cancelBtn.textContent = 'Cancel';
        cancelBtn.className = 'cancel-btn';
        cancelBtn.onclick = () => hideWizardOptions();
        container.appendChild(cancelBtn);
    }

    function updateQuickActions(actions) {
        const container = document.getElementById('ai-quick-actions');
        container.innerHTML = '';

        actions.forEach(action => {
            const btn = document.createElement('button');
            btn.textContent = action.label;
            btn.onclick = () => sendQuickAction(action.prompt);
            container.appendChild(btn);
        });
    }

    function addMessage(text, type, visualAids = [], isAiPowered = false) {
        const messages = document.getElementById('ai-chat-messages');
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        // Convert markdown-style formatting to HTML
        let formattedText = formatMarkdown(text);

        // AI powered badge for OpenAI responses
        const aiBadge = (type === 'bot' && isAiPowered) ?
            '<span class="ai-powered-badge"><i class="fas fa-sparkles"></i> AI</span>' : '';

        const messageHtml = `
            <div class="ai-message ai-message-${type}">
                <div class="ai-message-avatar">
                    <i class="fas fa-${type === 'bot' ? 'robot' : 'user'}"></i>
                </div>
                <div class="ai-message-content">
                    <p>${formattedText}</p>
                    ${renderVisualAids(visualAids)}
                    <span class="ai-message-time">${time}${aiBadge}</span>
                </div>
            </div>
        `;

        messages.insertAdjacentHTML('beforeend', messageHtml);
        messages.scrollTop = messages.scrollHeight;
    }

    function formatMarkdown(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')  // **bold**
            .replace(/\*(.*?)\*/g, '<em>$1</em>')  // *italic*
            .replace(/`(.*?)`/g, '<code>$1</code>')  // `code`
            .replace(/### (.*?)$/gm, '<strong style="font-size:14px">$1</strong>')  // ### heading
            .replace(/## (.*?)$/gm, '<strong style="font-size:15px">$1</strong>')  // ## heading
            .replace(/\n/g, '<br>')  // newlines
            .replace(/• /g, '&bull; ')  // bullets
            .replace(/- /g, '&bull; ');  // dashes as bullets
    }

    function renderVisualAids(visualAids) {
        if (!visualAids || visualAids.length === 0) return '';

        let html = '';

        visualAids.forEach(aid => {
            switch (aid.type) {
                case 'stepCard':
                    html += renderStepCard(aid.content);
                    break;
                case 'statsCard':
                    html += renderStatsCard(aid.content);
                    break;
                case 'rankingCard':
                    html += renderRankingCard(aid.content);
                    break;
            }
        });

        return html;
    }

    function renderStepCard(content) {
        if (!content.steps || content.steps.length === 0) return '';

        const progressDots = content.showProgress ?
            `<div class="ai-step-progress">
                ${content.steps.map((_, i) =>
                    `<div class="ai-step-progress-dot ${i === 0 ? 'active' : ''}"></div>`
                ).join('')}
            </div>` : '';

        const steps = content.steps.map(step => `
            <div class="ai-step-item">
                <div class="ai-step-title">
                    <span class="ai-step-number">${step.number}</span>
                    ${step.title}
                </div>
                ${step.content ? `<div class="ai-step-content">${step.content}</div>` : ''}
            </div>
        `).join('');

        return `
            <div class="ai-step-card">
                <div class="ai-step-card-header">
                    <i class="fas fa-list-ol"></i>
                    Step-by-Step Guide
                </div>
                ${progressDots}
                ${steps}
            </div>
        `;
    }

    function renderStatsCard(content) {
        if (!content.stats || content.stats.length === 0) return '';

        const stats = content.stats.map(stat => `
            <div class="ai-stat-item">
                <div class="ai-stat-value">${stat.value}</div>
                <div class="ai-stat-label">${stat.label}</div>
            </div>
        `).join('');

        return `<div class="ai-stats-card">${stats}</div>`;
    }

    function renderRankingCard(content) {
        return `
            <div class="ai-ranking-card">
                <div class="ai-ranking-header">
                    <i class="fas fa-trophy"></i>
                    Current Rankings
                </div>
            </div>
        `;
    }

    function showTyping() {
        const messages = document.getElementById('ai-chat-messages');
        const typingHtml = `
            <div class="ai-message ai-message-bot ai-typing" id="typing-indicator">
                <div class="ai-message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="ai-message-content">
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `;
        messages.insertAdjacentHTML('beforeend', typingHtml);
        messages.scrollTop = messages.scrollHeight;
    }

    function hideTyping() {
        const typing = document.getElementById('typing-indicator');
        if (typing) typing.remove();
    }

    // Voice Recording Setup (using MediaRecorder + OpenAI Whisper)
    let mediaRecorder = null;
    let audioChunks = [];
    let isRecording = false;
    let voiceAvailable = false;
    let recordingStartTime = null;
    let recordingTimer = null;

    // Check if Whisper is available on load
    async function checkVoiceAvailability() {
        try {
            const response = await fetch('/api/ai-chat/voice-status', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();
            voiceAvailable = data.available;

            if (!voiceAvailable) {
                const btn = document.getElementById('ai-voice-btn');
                if (btn) {
                    btn.title = 'Voice input requires OpenAI API key';
                    btn.style.opacity = '0.5';
                }
            }
        } catch (error) {
            console.error('Error checking voice availability:', error);
        }
    }

    async function toggleVoiceInput() {
        if (isRecording) {
            stopVoiceInput();
            return;
        }

        // Check if voice is available
        if (!voiceAvailable) {
            addMessage("Voice input requires an OpenAI API key. Please configure OpenAI in AI Providers settings.", 'bot');
            return;
        }

        // Check if we've been denied before
        const btn = document.getElementById('ai-voice-btn');
        if (btn && btn.classList.contains('permission-denied')) {
            addMessage("Microphone access was previously denied. Please check your browser settings to enable it.", 'bot');
            return;
        }

        startVoiceInput();
    }

    async function startVoiceInput() {
        try {
            updateVoiceUI('requesting');

            // Request microphone access
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

            // Create MediaRecorder
            mediaRecorder = new MediaRecorder(stream, {
                mimeType: MediaRecorder.isTypeSupported('audio/webm') ? 'audio/webm' : 'audio/mp4'
            });

            audioChunks = [];

            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    audioChunks.push(event.data);
                }
            };

            mediaRecorder.onstop = async () => {
                // Stop all tracks
                stream.getTracks().forEach(track => track.stop());

                if (audioChunks.length > 0) {
                    await transcribeAudio();
                }
            };

            mediaRecorder.onerror = (event) => {
                console.error('MediaRecorder error:', event.error);
                updateVoiceUI('error');
                addMessage("Recording error occurred. Please try again.", 'bot');
            };

            // Start recording
            mediaRecorder.start(100); // Collect data every 100ms
            isRecording = true;
            recordingStartTime = Date.now();
            updateVoiceUI('listening');

            // Update timer every second
            recordingTimer = setInterval(updateRecordingTime, 1000);

            // Auto-stop after 60 seconds (Whisper works best with shorter clips)
            setTimeout(() => {
                if (isRecording) {
                    stopVoiceInput();
                }
            }, 60000);

        } catch (error) {
            console.error('Error starting voice input:', error);

            if (error.name === 'NotAllowedError' || error.name === 'PermissionDeniedError') {
                updateVoiceUI('denied');
                addMessage("Microphone access was denied. Please allow microphone access in your browser settings.", 'bot');
            } else {
                updateVoiceUI('error');
                addMessage("Could not access microphone: " + error.message, 'bot');
            }
        }
    }

    function stopVoiceInput() {
        if (mediaRecorder && isRecording) {
            isRecording = false;
            clearInterval(recordingTimer);
            updateVoiceUI('processing');
            mediaRecorder.stop();
        }
    }

    function updateRecordingTime() {
        if (!recordingStartTime) return;
        const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        const timeStr = `${minutes}:${seconds.toString().padStart(2, '0')}`;

        const transcriptEl = document.getElementById('ai-voice-transcript');
        if (transcriptEl) {
            transcriptEl.textContent = `Recording: ${timeStr}`;
        }
    }

    async function transcribeAudio() {
        const statusText = document.getElementById('ai-voice-status-text');
        if (statusText) {
            statusText.textContent = 'Transcribing with AI...';
        }

        try {
            // Create audio blob
            const audioBlob = new Blob(audioChunks, {
                type: mediaRecorder.mimeType
            });

            // Check if audio is too short (less than 0.5 seconds)
            if (audioBlob.size < 5000) {
                updateVoiceUI('idle');
                addMessage("Recording was too short. Please speak for at least a second.", 'bot');
                return;
            }

            // Create form data
            const formData = new FormData();
            const extension = mediaRecorder.mimeType.includes('webm') ? 'webm' : 'm4a';
            formData.append('audio', audioBlob, `recording.${extension}`);

            // Send to Whisper endpoint
            const response = await fetch('/api/ai-chat/transcribe', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const data = await response.json();

            updateVoiceUI('idle');

            if (data.success && data.text) {
                // Send the transcribed text as a message
                sendMessage(data.text);
            } else {
                addMessage("Could not transcribe audio: " + (data.error || 'Unknown error'), 'bot');
            }

        } catch (error) {
            console.error('Transcription error:', error);
            updateVoiceUI('idle');
            addMessage("Failed to transcribe audio. Please try again.", 'bot');
        }
    }

    function updateVoiceUI(state) {
        const btn = document.getElementById('ai-voice-btn');
        const icon = document.getElementById('ai-voice-icon');
        const indicator = document.getElementById('ai-voice-indicator');
        const statusText = document.getElementById('ai-voice-status-text');
        const transcriptEl = document.getElementById('ai-voice-transcript');
        const inputArea = document.querySelector('.ai-chat-input');

        if (!btn) return;

        // Reset classes
        btn.classList.remove('recording', 'permission-denied');

        switch (state) {
            case 'requesting':
                statusText.textContent = 'Requesting microphone access...';
                indicator.style.display = 'flex';
                inputArea.style.display = 'none';
                break;

            case 'listening':
                btn.classList.add('recording');
                icon.className = 'fas fa-microphone-slash';
                btn.title = 'Stop recording';
                statusText.textContent = 'Recording... Click Stop when done';
                transcriptEl.textContent = 'Recording: 0:00';
                indicator.style.display = 'flex';
                inputArea.style.display = 'none';
                // Show the stop button
                const listenStopBtn = indicator.querySelector('.ai-voice-stop-btn');
                if (listenStopBtn) listenStopBtn.style.display = 'flex';
                break;

            case 'denied':
                btn.classList.add('permission-denied');
                icon.className = 'fas fa-microphone-slash';
                btn.title = 'Microphone access denied';
                indicator.style.display = 'none';
                inputArea.style.display = 'flex';
                break;

            case 'processing':
                statusText.textContent = 'Transcribing with AI...';
                transcriptEl.textContent = '';
                indicator.style.display = 'flex';
                inputArea.style.display = 'none';
                // Hide the stop button during processing
                const stopBtn = indicator.querySelector('.ai-voice-stop-btn');
                if (stopBtn) stopBtn.style.display = 'none';
                break;

            case 'no-speech':
                statusText.textContent = 'No speech detected. Try again.';
                setTimeout(() => {
                    indicator.style.display = 'none';
                    inputArea.style.display = 'flex';
                    icon.className = 'fas fa-microphone';
                    btn.title = 'Voice input';
                }, 2000);
                break;

            case 'error':
                indicator.style.display = 'none';
                inputArea.style.display = 'flex';
                icon.className = 'fas fa-microphone';
                btn.title = 'Voice input';
                break;

            case 'idle':
            default:
                icon.className = 'fas fa-microphone';
                btn.title = 'Voice input';
                indicator.style.display = 'none';
                inputArea.style.display = 'flex';
                break;
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if MediaRecorder is supported
        if (!navigator.mediaDevices || !window.MediaRecorder) {
            const btn = document.getElementById('ai-voice-btn');
            if (btn) {
                btn.style.display = 'none';
            }
            console.warn('MediaRecorder not supported in this browser');
            return;
        }

        // Check if Whisper (OpenAI) is available
        checkVoiceAvailability();
    });
</script>
