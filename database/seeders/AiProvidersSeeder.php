<?php

namespace Database\Seeders;

use App\Models\AiProvider;
use Illuminate\Database\Seeder;

class AiProvidersSeeder extends Seeder
{
    public function run(): void
    {
        $definitions = AiProvider::getProviderDefinitions();
        $order = 1;

        foreach ($definitions as $code => $definition) {
            AiProvider::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'available_models' => $definition['models'],
                    'default_model' => $definition['default_model'],
                    'api_base_url' => $definition['api_base_url'],
                    'is_active' => true,
                    'is_configured' => false,
                    'is_selected' => $code === 'openai', // Default to OpenAI
                    'display_order' => $order++,
                ]
            );
        }

        // Check if OpenAI key exists in config and set it
        $openaiKey = config('services.openai.api_key');
        if ($openaiKey) {
            $openai = AiProvider::where('code', 'openai')->first();
            if ($openai) {
                $openai->api_key = $openaiKey;
                $openai->is_configured = true;
                $openai->save();
            }
        }
    }
}
