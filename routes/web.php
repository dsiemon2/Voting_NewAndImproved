<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\VotingTypeController;
use App\Http\Controllers\Admin\DivisionController;
use App\Http\Controllers\Admin\ParticipantController;
use App\Http\Controllers\Admin\EntryController;
use App\Http\Controllers\Admin\JudgeController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\Admin\AiConfigController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PdfController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AiAgentController;
use App\Http\Controllers\Admin\WebhookController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Api\AiChatController;
use App\Http\Controllers\Voting\VoteController;
use App\Http\Controllers\Voting\ResultsController;
use App\Http\Controllers\TrialCodeController;
use App\Http\Controllers\Admin\TrialCodeController as AdminTrialCodeController;
use App\Http\Controllers\Admin\TwilioSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('landing');
})->name('home');

// Trial Code Routes (Public AJAX endpoints)
Route::prefix('trial-code')->name('trial-code.')->group(function () {
    Route::post('/request', [TrialCodeController::class, 'request'])->name('request');
    Route::post('/validate', [TrialCodeController::class, 'validate'])->name('validate');
    Route::post('/resend', [TrialCodeController::class, 'resend'])->name('resend');
});

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Subscription routes (public pricing page)
Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('subscription.pricing');

// Stripe webhook (no auth required)
Route::post('/webhook/stripe', [SubscriptionController::class, 'webhook'])->name('subscription.webhook');

// Protected routes
Route::middleware(['auth'])->group(function () {

    // Subscription management
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/manage', [SubscriptionController::class, 'manage'])->name('manage');
        Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::post('/billing-portal', [SubscriptionController::class, 'billingPortal'])->name('billing-portal');
        Route::get('/stripe-key', [SubscriptionController::class, 'getPublishableKey'])->name('stripe-key');
    });

    // Account Settings
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/data', [AccountController::class, 'getData'])->name('data');
        Route::put('/name', [AccountController::class, 'updateName'])->name('name');
        Route::put('/email', [AccountController::class, 'updateEmail'])->name('email');
        Route::put('/phone', [AccountController::class, 'updatePhone'])->name('phone');
        Route::put('/password', [AccountController::class, 'updatePassword'])->name('password');
        Route::get('/payment-methods', [AccountController::class, 'getPaymentMethods'])->name('payment-methods');
        Route::post('/payment-methods', [AccountController::class, 'addPaymentMethod'])->name('payment-methods.store');
        Route::put('/payment-methods/{id}/default', [AccountController::class, 'setDefaultPaymentMethod'])->name('payment-methods.default');
        Route::delete('/payment-methods/{id}', [AccountController::class, 'deletePaymentMethod'])->name('payment-methods.destroy');
        Route::get('/notifications', [AccountController::class, 'getNotifications'])->name('notifications');
        Route::put('/notifications', [AccountController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/devices', [AccountController::class, 'getDevices'])->name('devices');
        Route::delete('/devices/{id}', [AccountController::class, 'signOutDevice'])->name('devices.destroy');
        Route::delete('/devices', [AccountController::class, 'signOutAllDevices'])->name('devices.destroy-all');
    });

    // Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin routes
    Route::prefix('admin')->name('admin.')->group(function () {

        // User Management (Admin only)
        Route::middleware(['role:Administrator'])->group(function () {
            Route::resource('users', UserController::class);
        });

        // Event Templates
        Route::resource('templates', TemplateController::class);

        // Voting Types Configuration
        Route::resource('voting-types', VotingTypeController::class);
        Route::post('voting-types/preset', [VotingTypeController::class, 'preset'])->name('voting-types.preset');

        // AI Settings (Admin only)
        Route::prefix('ai-settings')->name('ai-settings.')->middleware(['role:Administrator'])->group(function () {
            // Voices & Languages
            Route::get('voices', [AiSettingsController::class, 'voices'])->name('voices');
            Route::post('voices/select', [AiSettingsController::class, 'updateVoice'])->name('voices.select');
            Route::post('voices/mode', [AiSettingsController::class, 'updateMode'])->name('voices.mode');
            Route::post('voices/intensity', [AiSettingsController::class, 'updateIntensity'])->name('voices.intensity');
            Route::post('voices/preview', [AiSettingsController::class, 'previewVoice'])->name('voices.preview');
            Route::post('languages', [AiSettingsController::class, 'addLanguage'])->name('languages.store');
            Route::post('languages/{language}/toggle', [AiSettingsController::class, 'toggleLanguage'])->name('languages.toggle');

            // AI Configuration
            Route::get('config', [AiSettingsController::class, 'config'])->name('config');
            Route::post('config', [AiSettingsController::class, 'updateConfig'])->name('config.update');

            // AI Tools
            Route::get('tools', [AiSettingsController::class, 'tools'])->name('tools');

            // Knowledge Base
            Route::get('knowledge-base', [AiSettingsController::class, 'knowledgeBase'])->name('knowledge-base');

            // Features
            Route::get('features', [AiSettingsController::class, 'features'])->name('features');
            Route::post('features', [AiSettingsController::class, 'updateFeatures'])->name('features.update');

            // System Settings
            Route::get('settings', [AiSettingsController::class, 'settings'])->name('settings');
            Route::post('settings', [AiSettingsController::class, 'updateSettings'])->name('settings.update');

            // Greeting
            Route::get('greeting', [AiSettingsController::class, 'greeting'])->name('greeting');
            Route::post('greeting', [AiSettingsController::class, 'updateGreeting'])->name('greeting.update');
        });

        // AI Providers (Admin only)
        Route::prefix('ai-providers')->name('ai-providers.')->middleware(['role:Administrator'])->group(function () {
            Route::get('/', [AiConfigController::class, 'index'])->name('index');
            Route::get('/list', [AiConfigController::class, 'getProviders'])->name('list');
            Route::post('/{provider}/api-key', [AiConfigController::class, 'updateApiKey'])->name('api-key');
            Route::post('/{provider}/select', [AiConfigController::class, 'selectProvider'])->name('select');
            Route::post('/{provider}/settings', [AiConfigController::class, 'updateProviderSettings'])->name('settings');
            Route::post('/{provider}/test', [AiConfigController::class, 'testConnection'])->name('test');
        });

        // Payment Processing (Admin only)
        Route::prefix('payment-processing')->name('payment-processing.')->middleware(['role:Administrator'])->group(function () {
            Route::get('/', [PaymentGatewayController::class, 'index'])->name('index');
            Route::get('/gateways', [PaymentGatewayController::class, 'getGateways'])->name('gateways');
            Route::post('/{provider}', [PaymentGatewayController::class, 'update'])->name('update');
            Route::post('/{provider}/enable', [PaymentGatewayController::class, 'enable'])->name('enable');
            Route::post('/{provider}/disable', [PaymentGatewayController::class, 'disable'])->name('disable');
            Route::post('/{provider}/test', [PaymentGatewayController::class, 'testConnection'])->name('test');
        });

        // AI Agents (Admin only)
        Route::prefix('ai-agents')->name('ai-agents.')->middleware(['role:Administrator'])->group(function () {
            Route::get('/', [AiAgentController::class, 'index'])->name('index');
            Route::get('/list', [AiAgentController::class, 'list'])->name('list');
            Route::post('/', [AiAgentController::class, 'store'])->name('store');
            Route::put('/{agent}', [AiAgentController::class, 'update'])->name('update');
            Route::delete('/{agent}', [AiAgentController::class, 'destroy'])->name('destroy');
            Route::post('/{agent}/default', [AiAgentController::class, 'setDefault'])->name('default');
            Route::post('/{agent}/test', [AiAgentController::class, 'test'])->name('test');
        });

        // Webhooks (Admin only)
        Route::prefix('webhooks')->name('webhooks.')->middleware(['role:Administrator'])->group(function () {
            Route::get('/', [WebhookController::class, 'index'])->name('index');
            Route::get('/list', [WebhookController::class, 'list'])->name('list');
            Route::post('/', [WebhookController::class, 'store'])->name('store');
            Route::put('/{webhook}', [WebhookController::class, 'update'])->name('update');
            Route::delete('/{webhook}', [WebhookController::class, 'destroy'])->name('destroy');
            Route::post('/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('toggle');
            Route::post('/{webhook}/test', [WebhookController::class, 'test'])->name('test');
            Route::get('/{webhook}/logs', [WebhookController::class, 'logs'])->name('logs');
            Route::delete('/{webhook}/logs', [WebhookController::class, 'clearLogs'])->name('logs.clear');
        });

        // Trial Codes (Admin only)
        Route::prefix('trial-codes')->name('trial-codes.')->middleware(['role:Administrator'])->group(function () {
            Route::get('/', [AdminTrialCodeController::class, 'index'])->name('index');
            Route::get('/{trialCode}', [AdminTrialCodeController::class, 'show'])->name('show');
            Route::post('/store', [AdminTrialCodeController::class, 'store'])->name('store');
            Route::post('/{trialCode}/extend', [AdminTrialCodeController::class, 'extend'])->name('extend');
            Route::post('/{trialCode}/revoke', [AdminTrialCodeController::class, 'revoke'])->name('revoke');
            Route::post('/{trialCode}/resend', [AdminTrialCodeController::class, 'resend'])->name('resend');
            Route::post('/expire-old', [AdminTrialCodeController::class, 'expireOld'])->name('expire-old');
        });

        // Twilio Settings (Admin only)
        Route::prefix('twilio-settings')->name('twilio-settings.')->middleware(['role:Administrator'])->group(function () {
            Route::get('/', [TwilioSettingsController::class, 'index'])->name('index');
            Route::post('/', [TwilioSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [TwilioSettingsController::class, 'testConnection'])->name('test-connection');
            Route::post('/send-test-sms', [TwilioSettingsController::class, 'sendTestSms'])->name('send-test-sms');
        });

        // Events - Index, Show, Edit, Update, Destroy (no plan limit)
        Route::resource('events', EventController::class)->except(['create', 'store']);

        // Event creation - Gated by plan event limit
        Route::middleware(['plan.events'])->group(function () {
            Route::get('events/create', [EventController::class, 'create'])->name('events.create');
            Route::post('events', [EventController::class, 'store'])->name('events.store');
        });

        // Event-specific routes (nested under events)
        Route::prefix('events/{event}')->name('events.')->group(function () {

            // Divisions
            Route::resource('divisions', DivisionController::class)->only(['index', 'store', 'update', 'destroy']);

            // Participants
            Route::resource('participants', ParticipantController::class)->except(['show']);

            // Entries
            Route::resource('entries', EntryController::class)->except(['show']);

            // Judges (Judging Panel) - Gated by Professional+ plan
            Route::middleware(['plan.feature:judging_panels'])->group(function () {
                Route::resource('judges', JudgeController::class)->only(['index', 'store', 'update', 'destroy']);
            });

            // Categories
            Route::get('categories', [EventController::class, 'categories'])->name('categories.index');
            Route::post('categories', [EventController::class, 'storeCategory'])->name('categories.store');
            Route::put('categories/{category}', [EventController::class, 'updateCategory'])->name('categories.update');
            Route::delete('categories/{category}', [EventController::class, 'destroyCategory'])->name('categories.destroy');

            // Import - Gated by Non-Profit+ plan (Excel Import feature)
            Route::middleware(['plan.feature:excel_import'])->group(function () {
                Route::get('import', [EventController::class, 'import'])->name('import');
                Route::post('import', [EventController::class, 'processImport'])->name('import.process');
            });

            // PDF/Ballots - Gated by plan (pdf_ballots feature)
            Route::middleware(['plan.feature:pdf_ballots'])->prefix('pdf')->name('pdf.')->group(function () {
                Route::get('ballot', [PdfController::class, 'ballot'])->name('ballot');
                Route::get('ballot-sheets/{perPage?}', [PdfController::class, 'ballotSheets'])->name('ballot-sheets');
                Route::get('results', [PdfController::class, 'results'])->name('results');
                Route::get('certificate/{place?}/{division?}', [PdfController::class, 'certificate'])->name('certificate');
                Route::get('entries-list', [PdfController::class, 'entriesList'])->name('entries-list');
                Route::get('summary', [PdfController::class, 'summary'])->name('summary');
            });

            // Legacy route for backwards compatibility
            Route::get('ballots', [EventController::class, 'ballots'])->name('ballots');

            // Analytics - Gated by Professional+ plan (advanced_analytics feature)
            Route::middleware(['plan.feature:advanced_analytics'])->prefix('analytics')->name('analytics.')->group(function () {
                Route::get('/', [AnalyticsController::class, 'index'])->name('index');
                Route::get('/data', [AnalyticsController::class, 'data'])->name('data');
            });

            // Voting configuration
            Route::get('voting-config', [EventController::class, 'votingConfig'])->name('voting-config');
            Route::put('voting-config', [EventController::class, 'updateVotingConfig'])->name('voting-config.update');

            // Module configuration
            Route::get('modules', [EventController::class, 'modules'])->name('modules');
            Route::put('modules', [EventController::class, 'updateModules'])->name('modules.update');

            // Clear event data
            Route::delete('clear-data', [EventController::class, 'clearData'])->name('clear-data');
        });
    });

    // Voting routes (protected voting interface)
    Route::prefix('vote')->name('voting.')->group(function () {
        Route::get('/{event}', [VoteController::class, 'index'])->name('index');
        Route::post('/{event}', [VoteController::class, 'store'])->name('store');
        Route::get('/{event}/thank-you', [VoteController::class, 'thankYou'])->name('thank-you');
    });

    // Results routes
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/{event}', [ResultsController::class, 'index'])->name('index');
        Route::get('/{event}/division/{division}', [ResultsController::class, 'byDivision'])->name('division');
        Route::get('/{event}/live', [ResultsController::class, 'live'])->name('live');
        Route::get('/{event}/poll', [ResultsController::class, 'poll'])->name('poll');
    });
});

// Public voting (if event allows it)
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/vote/{event}', [VoteController::class, 'publicVote'])->name('vote');
    Route::post('/vote/{event}', [VoteController::class, 'storePublicVote'])->name('vote.store');
    Route::get('/results/{event}', [ResultsController::class, 'publicResults'])->name('results');
});

// AI Chat API
Route::middleware(['auth'])->prefix('api')->name('api.')->group(function () {
    Route::post('/ai-chat', [AiChatController::class, 'chat'])->name('ai-chat');
    Route::post('/ai-chat/transcribe', [AiChatController::class, 'transcribe'])->name('ai-chat.transcribe');
    Route::get('/ai-chat/voice-status', [AiChatController::class, 'voiceStatus'])->name('ai-chat.voice-status');
});
