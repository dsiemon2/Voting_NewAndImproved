<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProvider;
use App\Models\AiConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiConfigController extends Controller
{
    /**
     * Show the AI configuration page
     */
    public function index()
    {
        $providers = AiProvider::orderBy('display_order')->get();
        $config = AiConfig::first() ?? new AiConfig();
        $selectedProvider = AiProvider::getSelected();

        return view('admin.ai-providers.index', [
            'providers' => $providers,
            'config' => $config,
            'selectedProvider' => $selectedProvider,
        ]);
    }

    /**
     * Update a provider's API key
     */
    public function updateApiKey(Request $request, AiProvider $provider)
    {
        $validator = Validator::make($request->all(), [
            'api_key' => 'nullable|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $apiKey = $request->input('api_key');

        if ($apiKey) {
            $provider->api_key = $apiKey;
            $provider->is_configured = true;
        } else {
            $provider->api_key = null;
            $provider->is_configured = false;
        }

        $provider->save();

        return response()->json([
            'success' => true,
            'message' => $apiKey ? 'API key saved successfully' : 'API key removed',
            'masked_key' => $provider->getMaskedApiKey(),
            'is_configured' => $provider->is_configured,
        ]);
    }

    /**
     * Select a provider as the active one
     */
    public function selectProvider(Request $request, AiProvider $provider)
    {
        if (!$provider->is_configured) {
            return response()->json([
                'success' => false,
                'message' => 'Please configure an API key first',
            ], 422);
        }

        $provider->select();

        // Update selected model if provided
        if ($request->has('model')) {
            $provider->update(['default_model' => $request->input('model')]);
        }

        return response()->json([
            'success' => true,
            'message' => "{$provider->name} is now the active AI provider",
            'provider' => $provider,
        ]);
    }

    /**
     * Update provider settings (model, temperature, max_tokens, is_active)
     */
    public function updateProviderSettings(Request $request, AiProvider $provider)
    {
        $validator = Validator::make($request->all(), [
            'default_model' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:100|max:128000',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $provider->update($request->only(['default_model', 'temperature', 'max_tokens', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
            'provider' => $provider,
        ]);
    }

    /**
     * Test provider connection
     */
    public function testConnection(AiProvider $provider)
    {
        if (!$provider->is_configured) {
            return response()->json([
                'success' => false,
                'message' => 'No API key configured',
            ]);
        }

        try {
            $result = $this->testProviderConnection($provider);

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Test connection based on provider type
     */
    protected function testProviderConnection(AiProvider $provider): array
    {
        $apiKey = $provider->getDecryptedApiKey();

        switch ($provider->code) {
            case 'openai':
                return $this->testOpenAI($apiKey, $provider->default_model);

            case 'anthropic':
                return $this->testAnthropic($apiKey, $provider->default_model);

            case 'gemini':
                return $this->testGemini($apiKey, $provider->default_model);

            case 'deepseek':
            case 'groq':
            case 'mistral':
            case 'grok':
                // These use OpenAI-compatible API
                return $this->testOpenAICompatible($apiKey, $provider->api_base_url, $provider->default_model);

            default:
                return ['success' => false, 'message' => 'Unknown provider'];
        }
    }

    protected function testOpenAI(string $apiKey, string $model): array
    {
        $client = \OpenAI::client($apiKey);

        try {
            $response = $client->chat()->create([
                'model' => $model ?? 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "OK" if you can hear me.'],
                ],
                'max_tokens' => 10,
            ]);

            return [
                'success' => true,
                'message' => 'Connection successful! Model: ' . ($response->model ?? $model),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function testAnthropic(string $apiKey, string $model): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => $model ?? 'claude-3-5-sonnet-20241022',
                'max_tokens' => 10,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "OK" if you can hear me.'],
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Model: ' . ($response->json('model') ?? $model),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('error.message') ?? 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function testGemini(string $apiKey, string $model): array
    {
        try {
            $modelName = $model ?? 'gemini-1.5-pro';
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key={$apiKey}";

            $response = \Illuminate\Support\Facades\Http::post($url, [
                'contents' => [
                    ['parts' => [['text' => 'Say "OK" if you can hear me.']]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 10,
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Model: ' . $modelName,
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('error.message') ?? 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    protected function testOpenAICompatible(string $apiKey, string $baseUrl, string $model): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post(rtrim($baseUrl, '/') . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Say "OK" if you can hear me.'],
                ],
                'max_tokens' => 10,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Connection successful! Model: ' . ($response->json('model') ?? $model),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('error.message') ?? 'Connection failed',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get all providers as JSON (for AJAX)
     */
    public function getProviders()
    {
        $providers = AiProvider::orderBy('display_order')->get()->map(function ($provider) {
            return [
                'id' => $provider->id,
                'code' => $provider->code,
                'name' => $provider->name,
                'description' => $provider->description,
                'available_models' => $provider->available_models,
                'default_model' => $provider->default_model,
                'is_active' => $provider->is_active,
                'is_configured' => $provider->is_configured,
                'is_selected' => $provider->is_selected,
                'has_api_key' => $provider->hasApiKey(),
                'masked_api_key' => $provider->getMaskedApiKey(),
                'temperature' => $provider->temperature,
                'max_tokens' => $provider->max_tokens,
            ];
        });

        return response()->json($providers);
    }
}
