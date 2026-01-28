@extends('layouts.app')

@section('content')
<div>
    <h1 class="page-title">
        <i class="fas fa-comment-dots"></i> AI Greeting Configuration
    </h1>
    <p style="color: #6b7280; margin-bottom: 20px;">Configure the initial greeting message users hear when they open the AI chat.</p>

    <div class="grid grid-2">
        <!-- Greeting Editor -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Greeting Message
            </div>
            <div class="card-body">
                <form id="greetingForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Greeting Text</label>
                        <textarea class="form-control" name="greeting_message" rows="4" id="greetingText"
                                  placeholder="Enter the greeting message...">{{ $config->greeting_message ?? "Hello! I'm your AI voting assistant. How can I help you today?" }}</textarea>
                        <small style="color: #6b7280;">This message is displayed when users first open the AI chat panel.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Voice for Greeting</label>
                        <select class="form-control" name="greeting_voice" id="greetingVoice">
                            <option value="alloy" {{ ($config->greeting_voice ?? $config->selected_voice) === 'alloy' ? 'selected' : '' }}>Alloy (Neutral)</option>
                            <option value="ash" {{ ($config->greeting_voice ?? $config->selected_voice) === 'ash' ? 'selected' : '' }}>Ash (Male)</option>
                            <option value="ballad" {{ ($config->greeting_voice ?? $config->selected_voice) === 'ballad' ? 'selected' : '' }}>Ballad (Female)</option>
                            <option value="coral" {{ ($config->greeting_voice ?? $config->selected_voice) === 'coral' ? 'selected' : '' }}>Coral (Female)</option>
                            <option value="echo" {{ ($config->greeting_voice ?? $config->selected_voice) === 'echo' ? 'selected' : '' }}>Echo (Male)</option>
                            <option value="sage" {{ ($config->greeting_voice ?? $config->selected_voice) === 'sage' ? 'selected' : '' }}>Sage (Female)</option>
                            <option value="shimmer" {{ ($config->greeting_voice ?? $config->selected_voice) === 'shimmer' ? 'selected' : '' }}>Shimmer (Female)</option>
                            <option value="verse" {{ ($config->greeting_voice ?? $config->selected_voice) === 'verse' ? 'selected' : '' }}>Verse (Male)</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" onclick="previewGreeting()">
                            <i class="fas fa-play"></i> Preview
                        </button>
                        <button type="button" class="btn btn-success" onclick="saveGreeting()">
                            <i class="fas fa-check"></i> Save Greeting
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-eye"></i> Live Preview
            </div>
            <div class="card-body" style="background: #f3f4f6; min-height: 300px;">
                <div class="chat-preview">
                    <div class="chat-header">
                        <div class="chat-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div>
                            <strong>AI Assistant</strong>
                            <small style="display: block; color: #10b981;">Online</small>
                        </div>
                    </div>
                    <div class="chat-messages">
                        <div class="chat-bubble" id="previewBubble">
                            {{ $config->greeting_message ?? "Hello! I'm your AI voting assistant. How can I help you today?" }}
                        </div>
                    </div>
                    <div class="chat-input">
                        <input type="text" placeholder="Type a message..." disabled>
                        <button disabled><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Suggestions -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-lightbulb"></i> Greeting Templates
        </div>
        <div class="card-body">
            <p style="color: #6b7280; margin-bottom: 15px;">Click a template to use it as your greeting:</p>
            <div class="grid grid-2">
                <div class="template-card" onclick="useTemplate(this)">
                    <h6><i class="fas fa-hand-wave" style="color: #f59e0b;"></i> Friendly Welcome</h6>
                    <p class="template-text">Hello! I'm your AI voting assistant. I'm here to help you navigate events, find information about entries, and answer any questions about the voting process. What can I help you with today?</p>
                </div>

                <div class="template-card" onclick="useTemplate(this)">
                    <h6><i class="fas fa-info-circle" style="color: #0d7a3e;"></i> Informative</h6>
                    <p class="template-text">Welcome to the Voting System! I can help you with: viewing event details, checking voting results, finding participant information, and understanding voting rules. How may I assist you?</p>
                </div>

                <div class="template-card" onclick="useTemplate(this)">
                    <h6><i class="fas fa-bolt" style="color: #8b5cf6;"></i> Quick & Direct</h6>
                    <p class="template-text">Hi there! Need help with voting? Just ask me anything about events, entries, or results.</p>
                </div>

                <div class="template-card" onclick="useTemplate(this)">
                    <h6><i class="fas fa-user-tie" style="color: #10b981;"></i> Professional</h6>
                    <p class="template-text">Good day. I'm the AI assistant for this voting platform. I'm equipped to provide information about ongoing events, voting procedures, and result summaries. Please let me know how I can be of service.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips -->
    <div class="alert alert-info mt-4">
        <h6><i class="fas fa-lightbulb"></i> Tips for Great Greetings</h6>
        <ul style="margin: 0; padding-left: 20px;">
            <li>Keep it concise - users want to get to their question quickly</li>
            <li>Mention what the AI can help with to set expectations</li>
            <li>Use a friendly, approachable tone</li>
            <li>Consider your audience - adjust formality as needed</li>
        </ul>
    </div>
</div>
@endsection

@push('styles')
<style>
    .chat-preview {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .chat-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        background: linear-gradient(135deg, #0d6e38 0%, #0d7a3e 100%);
        color: white;
    }
    .chat-avatar {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chat-messages {
        padding: 20px;
        min-height: 150px;
    }
    .chat-bubble {
        background: #f3f4f6;
        padding: 12px 16px;
        border-radius: 18px;
        border-top-left-radius: 4px;
        max-width: 90%;
        color: #374151;
        line-height: 1.5;
    }
    .chat-input {
        display: flex;
        padding: 15px;
        border-top: 1px solid #e5e7eb;
        gap: 10px;
    }
    .chat-input input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        outline: none;
    }
    .chat-input button {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: none;
        background: #0d7a3e;
        color: white;
        cursor: pointer;
    }

    .template-card {
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        border: 2px solid transparent;
    }
    .template-card:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
    }
    .template-card h6 {
        margin: 0 0 10px 0;
        color: #1a3a5c;
    }
    .template-card .template-text {
        margin: 0;
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
    }
</style>
@endpush

@push('scripts')
<script>
    // Update preview as user types
    document.getElementById('greetingText').addEventListener('input', function() {
        document.getElementById('previewBubble').textContent = this.value || "Your greeting will appear here...";
    });

    function useTemplate(element) {
        const text = element.querySelector('.template-text').textContent;
        document.getElementById('greetingText').value = text;
        document.getElementById('previewBubble').textContent = text;
    }

    function previewGreeting() {
        const text = document.getElementById('greetingText').value;
        const voice = document.getElementById('greetingVoice').value;

        // Use browser speech synthesis for preview
        const utterance = new SpeechSynthesisUtterance(text);
        window.speechSynthesis.speak(utterance);

        alert(`Playing greeting with voice: ${voice}`);
    }

    async function saveGreeting() {
        const form = document.getElementById('greetingForm');

        try {
            const res = await fetch('{{ route("admin.ai-settings.greeting.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    greeting_message: document.getElementById('greetingText').value,
                    greeting_voice: document.getElementById('greetingVoice').value
                })
            });

            if (res.ok) {
                alert('Greeting saved successfully!');
            } else {
                alert('Failed to save greeting');
            }
        } catch (err) {
            alert('Error saving greeting');
        }
    }
</script>
@endpush
