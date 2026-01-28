<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiVoice;
use App\Models\AiLanguage;
use App\Models\AiConfig;
use App\Models\FeatureSettings;
use App\Models\SystemSettings;
use Illuminate\Http\Request;

class AiSettingsController extends Controller
{
    /**
     * Voices & Languages page
     */
    public function voices()
    {
        $config = AiConfig::firstOrCreate(
            ['id' => 1],
            [
                'selected_voice' => 'alloy',
                'assistant_mode' => 'hybrid',
                'intensity' => 'medium',
                'default_model' => 'gpt-4o',
                'temperature' => 0.7,
                'max_tokens' => 4096,
            ]
        );

        $voices = AiVoice::getAvailableVoices();
        $languages = AiLanguage::orderBy('name')->get();

        return view('admin.ai-settings.voices', [
            'config' => $config,
            'voices' => $voices,
            'languages' => $languages,
        ]);
    }

    /**
     * Update voice selection
     */
    public function updateVoice(Request $request)
    {
        $request->validate([
            'voice' => 'required|string|in:alloy,ash,ballad,coral,echo,sage,shimmer,verse',
        ]);

        $config = AiConfig::firstOrFail();
        $config->update(['selected_voice' => $request->voice]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'voice' => $request->voice]);
        }

        return back()->with('success', 'Voice updated successfully.');
    }

    /**
     * Update assistant mode
     */
    public function updateMode(Request $request)
    {
        $request->validate([
            'mode' => 'required|string|in:ai_only,hybrid',
        ]);

        $config = AiConfig::firstOrFail();
        $config->update(['assistant_mode' => $request->mode]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'mode' => $request->mode]);
        }

        return back()->with('success', 'Assistant mode updated.');
    }

    /**
     * Update intensity level
     */
    public function updateIntensity(Request $request)
    {
        $request->validate([
            'intensity' => 'required|string|in:gentle,moderate,persistent,full',
        ]);

        $config = AiConfig::firstOrFail();
        $config->update(['intensity' => $request->intensity]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'intensity' => $request->intensity]);
        }

        return back()->with('success', 'Intensity updated.');
    }

    /**
     * Toggle language
     */
    public function toggleLanguage(Request $request, AiLanguage $language)
    {
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $language->update(['enabled' => $request->enabled]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Language updated.');
    }

    /**
     * Add a new language
     */
    public function addLanguage(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:10|unique:ai_languages,code',
        ]);

        $languageData = AiLanguage::getLanguageData($request->code);

        AiLanguage::create([
            'code' => $request->code,
            'name' => $languageData['name'],
            'native_name' => $languageData['native_name'],
            'flag' => $languageData['flag'],
            'enabled' => true,
        ]);

        return back()->with('success', 'Language added successfully.');
    }

    /**
     * AI Configuration page
     */
    public function config()
    {
        $config = AiConfig::firstOrCreate(
            ['id' => 1],
            [
                'selected_voice' => 'alloy',
                'assistant_mode' => 'hybrid',
                'intensity' => 'medium',
                'default_model' => 'gpt-4o',
                'temperature' => 0.7,
                'max_tokens' => 4096,
            ]
        );

        return view('admin.ai-settings.config', [
            'config' => $config,
        ]);
    }

    /**
     * Update AI configuration
     */
    public function updateConfig(Request $request)
    {
        $request->validate([
            'default_model' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:100|max:32000',
            'speech_speed' => 'nullable|numeric|min:0.5|max:2',
            'enable_tts' => 'nullable|boolean',
            'content_filter' => 'nullable|boolean',
            'pii_detection' => 'nullable|boolean',
            'transcript_logging' => 'nullable|boolean',
            'record_calls' => 'nullable|boolean',
        ]);

        $config = AiConfig::firstOrFail();
        $config->update($request->only([
            'default_model',
            'temperature',
            'max_tokens',
            'speech_speed',
            'enable_tts',
            'content_filter',
            'pii_detection',
            'transcript_logging',
            'record_calls',
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'AI configuration updated.');
    }

    /**
     * AI Tools page
     */
    public function tools()
    {
        $tools = [
            [
                'id' => 'voting_lookup',
                'name' => 'Voting Lookup',
                'description' => 'Look up voting information for events, entries, and results',
                'icon' => 'fa-search',
                'enabled' => true,
            ],
            [
                'id' => 'results_query',
                'name' => 'Results Query',
                'description' => 'Query and analyze voting results by division, category, or participant',
                'icon' => 'fa-chart-bar',
                'enabled' => true,
            ],
            [
                'id' => 'event_management',
                'name' => 'Event Management',
                'description' => 'Create, update, and manage events through conversation',
                'icon' => 'fa-calendar-alt',
                'enabled' => false,
            ],
            [
                'id' => 'participant_search',
                'name' => 'Participant Search',
                'description' => 'Search and find participant information',
                'icon' => 'fa-users',
                'enabled' => true,
            ],
            [
                'id' => 'report_generation',
                'name' => 'Report Generation',
                'description' => 'Generate reports and summaries of voting data',
                'icon' => 'fa-file-pdf',
                'enabled' => false,
            ],
        ];

        return view('admin.ai-settings.tools', [
            'tools' => $tools,
        ]);
    }

    /**
     * Knowledge Base page
     */
    public function knowledgeBase()
    {
        $documents = [
            [
                'id' => 1,
                'title' => 'Voting Rules & Guidelines',
                'description' => 'General rules for voting procedures',
                'size' => '12 KB',
                'updated_at' => now()->subDays(3),
            ],
            [
                'id' => 2,
                'title' => 'Event Templates Documentation',
                'description' => 'How to use and configure event templates',
                'size' => '8 KB',
                'updated_at' => now()->subDays(7),
            ],
        ];

        return view('admin.ai-settings.knowledge-base', [
            'documents' => $documents,
        ]);
    }

    /**
     * Features Configuration page
     */
    public function features()
    {
        $features = FeatureSettings::firstOrCreate(
            ['id' => 1],
            [
                'public_voting' => true,
                'live_results' => true,
                'judging_panel' => false,
                'import_export' => true,
                'pdf_reports' => false,
                'categories' => true,
                'ai_chat_enabled' => true,
                'ai_show_on_voting' => true,
                'ai_show_on_results' => true,
                'ai_show_on_admin' => true,
                'ai_show_on_dashboard' => true,
                'ai_show_on_landing' => false,
                'ai_show_on_mobile' => true,
                'ai_voice_input' => true,
                'ai_voice_output' => true,
                'ai_charts' => true,
                'ai_data_modification' => false,
                'ai_chat_position' => 'bottom-right',
                'ai_button_color' => '#1e40af',
                'ai_panel_width' => 380,
                'notifications_enabled' => true,
                'notify_email' => true,
                'notify_sms' => false,
                'notify_push' => true,
            ]
        );

        return view('admin.ai-settings.features', [
            'features' => $features,
        ]);
    }

    /**
     * Update features
     */
    public function updateFeatures(Request $request)
    {
        $features = FeatureSettings::firstOrFail();

        $features->update($request->only([
            'public_voting',
            'live_results',
            'judging_panel',
            'import_export',
            'pdf_reports',
            'categories',
            'ai_chat_enabled',
            'ai_show_on_voting',
            'ai_show_on_results',
            'ai_show_on_admin',
            'ai_show_on_dashboard',
            'ai_show_on_landing',
            'ai_show_on_mobile',
            'ai_voice_input',
            'ai_voice_output',
            'ai_charts',
            'ai_data_modification',
            'ai_chat_position',
            'ai_button_color',
            'ai_panel_width',
            'notifications_enabled',
            'notify_email',
            'notify_sms',
            'notify_push',
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Features updated successfully.');
    }

    /**
     * System Settings page
     */
    public function settings()
    {
        $settings = SystemSettings::firstOrCreate(
            ['id' => 1],
            [
                'organization_name' => 'VotigoPro',
                'organization_email' => 'admin@example.com',
                'timezone' => 'America/New_York',
                'date_format' => 'M d, Y',
                'time_format' => 'h:i A',
                'primary_color' => '#1e40af',
                'accent_color' => '#ff6600',
                'default_voting_type' => 1,
                'max_votes_per_user' => 1,
                'allow_vote_changes' => false,
            ]
        );

        return view('admin.ai-settings.settings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_email' => 'required|email',
            'timezone' => 'required|string',
        ]);

        $settings = SystemSettings::firstOrFail();
        $settings->update($request->only([
            'organization_name',
            'organization_email',
            'timezone',
            'date_format',
            'time_format',
            'primary_color',
            'accent_color',
            'default_voting_type',
            'max_votes_per_user',
            'allow_vote_changes',
        ]));

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Greeting Configuration page
     */
    public function greeting()
    {
        $config = AiConfig::firstOrFail();

        return view('admin.ai-settings.greeting', [
            'config' => $config,
        ]);
    }

    /**
     * Update greeting
     */
    public function updateGreeting(Request $request)
    {
        $request->validate([
            'greeting_message' => 'required|string|max:1000',
            'greeting_voice' => 'nullable|string',
        ]);

        $config = AiConfig::firstOrFail();
        $config->update([
            'greeting_message' => $request->greeting_message,
            'greeting_voice' => $request->greeting_voice ?? $config->selected_voice,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Greeting updated successfully.');
    }

    /**
     * Preview voice with text
     */
    public function previewVoice(Request $request)
    {
        $request->validate([
            'voice' => 'required|string',
            'text' => 'required|string|max:500',
        ]);

        // In a real implementation, this would call OpenAI TTS API
        // For now, return a success response
        return response()->json([
            'success' => true,
            'message' => 'Voice preview would play here',
            'voice' => $request->voice,
            'text' => $request->text,
        ]);
    }
}
