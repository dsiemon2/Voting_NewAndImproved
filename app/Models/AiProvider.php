<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiProvider extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'api_key',
        'api_base_url',
        'available_models',
        'default_model',
        'is_active',
        'is_configured',
        'is_selected',
        'display_order',
        'temperature',
        'max_tokens',
        'settings',
    ];

    protected $casts = [
        'available_models' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_configured' => 'boolean',
        'is_selected' => 'boolean',
        'temperature' => 'float',
        'max_tokens' => 'integer',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Get all provider definitions
     */
    public static function getProviderDefinitions(): array
    {
        return [
            'openai' => [
                'name' => 'OpenAI',
                'description' => 'ChatGPT models including GPT-4o and GPT-4.5',
                'models' => [
                    ['id' => 'gpt-4.5-preview', 'name' => 'GPT-4.5 Preview', 'description' => 'Latest preview model', 'recommended' => false],
                    ['id' => 'gpt-4o', 'name' => 'GPT-4o', 'description' => 'Most capable multimodal', 'recommended' => true],
                    ['id' => 'gpt-4o-mini', 'name' => 'GPT-4o Mini', 'description' => 'Fast and affordable', 'recommended' => false],
                    ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo', 'description' => 'GPT-4 with vision', 'recommended' => false],
                    ['id' => 'gpt-4', 'name' => 'GPT-4', 'description' => 'Original GPT-4', 'recommended' => false],
                    ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo', 'description' => 'Legacy fast model', 'recommended' => false],
                    ['id' => 'o1-preview', 'name' => 'o1 Preview', 'description' => 'Advanced reasoning model', 'recommended' => false],
                    ['id' => 'o1-mini', 'name' => 'o1 Mini', 'description' => 'Fast reasoning model', 'recommended' => false],
                ],
                'default_model' => 'gpt-4o',
                'api_base_url' => 'https://api.openai.com/v1',
            ],
            'anthropic' => [
                'name' => 'Anthropic Claude',
                'description' => 'Claude 4 Opus, Claude 3.5 Sonnet, and other Claude models',
                'models' => [
                    ['id' => 'claude-opus-4-5-20251101', 'name' => 'Claude Opus 4.5', 'description' => 'Most powerful Claude model', 'recommended' => true],
                    ['id' => 'claude-sonnet-4-20250514', 'name' => 'Claude Sonnet 4', 'description' => 'Balanced power and speed', 'recommended' => false],
                    ['id' => 'claude-3-5-sonnet-20241022', 'name' => 'Claude 3.5 Sonnet', 'description' => 'Previous gen, still excellent', 'recommended' => false],
                    ['id' => 'claude-3-5-haiku-20241022', 'name' => 'Claude 3.5 Haiku', 'description' => 'Fast and efficient', 'recommended' => false],
                    ['id' => 'claude-3-opus-20240229', 'name' => 'Claude 3 Opus', 'description' => 'Previous flagship', 'recommended' => false],
                    ['id' => 'claude-3-haiku-20240307', 'name' => 'Claude 3 Haiku', 'description' => 'Fastest Claude model', 'recommended' => false],
                ],
                'default_model' => 'claude-opus-4-5-20251101',
                'api_base_url' => 'https://api.anthropic.com/v1',
            ],
            'gemini' => [
                'name' => 'Google Gemini',
                'description' => 'Gemini 2.0 and Gemini 1.5 models from Google',
                'models' => [
                    ['id' => 'gemini-2.0-flash-exp', 'name' => 'Gemini 2.0 Flash', 'description' => 'Latest Gemini model', 'recommended' => true],
                    ['id' => 'gemini-1.5-pro', 'name' => 'Gemini 1.5 Pro', 'description' => 'Most capable 1.5 model', 'recommended' => false],
                    ['id' => 'gemini-1.5-flash', 'name' => 'Gemini 1.5 Flash', 'description' => 'Fast and efficient', 'recommended' => false],
                    ['id' => 'gemini-pro', 'name' => 'Gemini Pro', 'description' => 'General purpose', 'recommended' => false],
                ],
                'default_model' => 'gemini-2.0-flash-exp',
                'api_base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            ],
            'deepseek' => [
                'name' => 'DeepSeek',
                'description' => 'DeepSeek V3 and specialized coding models',
                'models' => [
                    ['id' => 'deepseek-chat', 'name' => 'DeepSeek V3', 'description' => 'Latest chat model', 'recommended' => true],
                    ['id' => 'deepseek-reasoner', 'name' => 'DeepSeek R1', 'description' => 'Advanced reasoning', 'recommended' => false],
                    ['id' => 'deepseek-coder', 'name' => 'DeepSeek Coder', 'description' => 'Specialized for code', 'recommended' => false],
                ],
                'default_model' => 'deepseek-chat',
                'api_base_url' => 'https://api.deepseek.com/v1',
            ],
            'groq' => [
                'name' => 'Groq',
                'description' => 'Ultra-fast inference with Groq LPU',
                'models' => [
                    ['id' => 'llama-3.3-70b-versatile', 'name' => 'Llama 3.3 70B', 'description' => 'Latest Llama model', 'recommended' => true],
                    ['id' => 'llama-3.1-70b-versatile', 'name' => 'Llama 3.1 70B', 'description' => 'Powerful open model', 'recommended' => false],
                    ['id' => 'llama-3.1-8b-instant', 'name' => 'Llama 3.1 8B', 'description' => 'Fast and efficient', 'recommended' => false],
                    ['id' => 'mixtral-8x7b-32768', 'name' => 'Mixtral 8x7B', 'description' => 'MoE architecture', 'recommended' => false],
                    ['id' => 'gemma2-9b-it', 'name' => 'Gemma 2 9B', 'description' => 'Google open model', 'recommended' => false],
                ],
                'default_model' => 'llama-3.3-70b-versatile',
                'api_base_url' => 'https://api.groq.com/openai/v1',
            ],
            'mistral' => [
                'name' => 'Mistral AI',
                'description' => 'Mistral Large 2, Pixtral, and Codestral models',
                'models' => [
                    ['id' => 'mistral-large-latest', 'name' => 'Mistral Large 2', 'description' => 'Flagship model, 128k context', 'recommended' => true],
                    ['id' => 'pixtral-large-latest', 'name' => 'Pixtral Large', 'description' => 'Multimodal with vision', 'recommended' => false],
                    ['id' => 'mistral-small-latest', 'name' => 'Mistral Small', 'description' => 'Fast and affordable', 'recommended' => false],
                    ['id' => 'codestral-latest', 'name' => 'Codestral', 'description' => 'Specialized for code', 'recommended' => false],
                    ['id' => 'open-mixtral-8x22b', 'name' => 'Mixtral 8x22B', 'description' => 'Open-weight MoE', 'recommended' => false],
                    ['id' => 'ministral-8b-latest', 'name' => 'Ministral 8B', 'description' => 'Compact efficient model', 'recommended' => false],
                ],
                'default_model' => 'mistral-large-latest',
                'api_base_url' => 'https://api.mistral.ai/v1',
            ],
            'grok' => [
                'name' => 'Grok (xAI)',
                'description' => 'Elon Musk\'s xAI models with real-time X data',
                'models' => [
                    ['id' => 'grok-2-latest', 'name' => 'Grok 2', 'description' => 'Latest flagship model', 'recommended' => true],
                    ['id' => 'grok-2-vision-latest', 'name' => 'Grok 2 Vision', 'description' => 'Multimodal with vision', 'recommended' => false],
                    ['id' => 'grok-beta', 'name' => 'Grok Beta', 'description' => 'Previous version', 'recommended' => false],
                    ['id' => 'grok-vision-beta', 'name' => 'Grok Vision Beta', 'description' => 'Previous vision model', 'recommended' => false],
                ],
                'default_model' => 'grok-2-latest',
                'api_base_url' => 'https://api.x.ai/v1',
            ],
            'huggingface' => [
                'name' => 'Hugging Face',
                'description' => 'Open-source models via Hugging Face Inference API',
                'models' => [
                    ['id' => 'meta-llama/Llama-3.3-70B-Instruct', 'name' => 'Llama 3.3 70B', 'description' => 'Latest Meta Llama model', 'recommended' => true],
                    ['id' => 'meta-llama/Llama-3.1-8B-Instruct', 'name' => 'Llama 3.1 8B', 'description' => 'Fast and efficient', 'recommended' => false],
                    ['id' => 'mistralai/Mistral-7B-Instruct-v0.3', 'name' => 'Mistral 7B', 'description' => 'Efficient Mistral model', 'recommended' => false],
                    ['id' => 'microsoft/Phi-3-mini-4k-instruct', 'name' => 'Phi-3 Mini', 'description' => 'Microsoft small model', 'recommended' => false],
                    ['id' => 'HuggingFaceH4/zephyr-7b-beta', 'name' => 'Zephyr 7B', 'description' => 'Fine-tuned Mistral', 'recommended' => false],
                    ['id' => 'Qwen/Qwen2.5-72B-Instruct', 'name' => 'Qwen 2.5 72B', 'description' => 'Alibaba flagship model', 'recommended' => false],
                ],
                'default_model' => 'meta-llama/Llama-3.3-70B-Instruct',
                'api_base_url' => 'https://api-inference.huggingface.co/models',
            ],
        ];
    }

    /**
     * Set API key (encrypt before storing)
     */
    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        } else {
            $this->attributes['api_key'] = null;
        }
    }

    /**
     * Get decrypted API key
     */
    public function getDecryptedApiKey(): ?string
    {
        if ($this->attributes['api_key']) {
            try {
                return Crypt::decryptString($this->attributes['api_key']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Check if provider has a valid API key
     */
    public function hasApiKey(): bool
    {
        return !empty($this->attributes['api_key']);
    }

    /**
     * Get masked API key for display
     */
    public function getMaskedApiKey(): ?string
    {
        $key = $this->getDecryptedApiKey();
        if (!$key) {
            return null;
        }

        $length = strlen($key);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($key, 0, 4) . str_repeat('*', $length - 8) . substr($key, -4);
    }

    /**
     * Get the currently selected provider
     */
    public static function getSelected(): ?self
    {
        return self::where('is_selected', true)->first();
    }

    /**
     * Select this provider
     */
    public function select(): void
    {
        // Deselect all others
        self::where('is_selected', true)->update(['is_selected' => false]);

        // Select this one
        $this->update(['is_selected' => true]);
    }

    /**
     * Get recommended model for this provider
     */
    public function getRecommendedModel(): ?array
    {
        $models = $this->available_models ?? [];
        foreach ($models as $model) {
            if ($model['recommended'] ?? false) {
                return $model;
            }
        }
        return $models[0] ?? null;
    }

    /**
     * Scope for active providers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for configured providers
     */
    public function scopeConfigured($query)
    {
        return $query->where('is_configured', true);
    }
}
