<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLanguage extends Model
{
    protected $table = 'ai_languages';

    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag',
        'enabled',
        'doc_count',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'doc_count' => 'integer',
    ];

    /**
     * Get language data for a given code
     */
    public static function getLanguageData(string $code): array
    {
        $languages = [
            'en' => ['name' => 'English', 'native_name' => 'English', 'flag' => "\u{1F1FA}\u{1F1F8}"],
            'es' => ['name' => 'Spanish', 'native_name' => 'Español', 'flag' => "\u{1F1EA}\u{1F1F8}"],
            'fr' => ['name' => 'French', 'native_name' => 'Français', 'flag' => "\u{1F1EB}\u{1F1F7}"],
            'de' => ['name' => 'German', 'native_name' => 'Deutsch', 'flag' => "\u{1F1E9}\u{1F1EA}"],
            'it' => ['name' => 'Italian', 'native_name' => 'Italiano', 'flag' => "\u{1F1EE}\u{1F1F9}"],
            'pt' => ['name' => 'Portuguese', 'native_name' => 'Português', 'flag' => "\u{1F1E7}\u{1F1F7}"],
            'zh' => ['name' => 'Chinese', 'native_name' => '中文', 'flag' => "\u{1F1E8}\u{1F1F3}"],
            'ja' => ['name' => 'Japanese', 'native_name' => '日本語', 'flag' => "\u{1F1EF}\u{1F1F5}"],
            'ko' => ['name' => 'Korean', 'native_name' => '한국어', 'flag' => "\u{1F1F0}\u{1F1F7}"],
            'ar' => ['name' => 'Arabic', 'native_name' => 'العربية', 'flag' => "\u{1F1F8}\u{1F1E6}"],
            'hi' => ['name' => 'Hindi', 'native_name' => 'हिन्दी', 'flag' => "\u{1F1EE}\u{1F1F3}"],
            'ru' => ['name' => 'Russian', 'native_name' => 'Русский', 'flag' => "\u{1F1F7}\u{1F1FA}"],
            'vi' => ['name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'flag' => "\u{1F1FB}\u{1F1F3}"],
            'pl' => ['name' => 'Polish', 'native_name' => 'Polski', 'flag' => "\u{1F1F5}\u{1F1F1}"],
            'nl' => ['name' => 'Dutch', 'native_name' => 'Nederlands', 'flag' => "\u{1F1F3}\u{1F1F1}"],
            'uk' => ['name' => 'Ukrainian', 'native_name' => 'Українська', 'flag' => "\u{1F1FA}\u{1F1E6}"],
            'tr' => ['name' => 'Turkish', 'native_name' => 'Türkçe', 'flag' => "\u{1F1F9}\u{1F1F7}"],
            'th' => ['name' => 'Thai', 'native_name' => 'ไทย', 'flag' => "\u{1F1F9}\u{1F1ED}"],
            'sv' => ['name' => 'Swedish', 'native_name' => 'Svenska', 'flag' => "\u{1F1F8}\u{1F1EA}"],
            'da' => ['name' => 'Danish', 'native_name' => 'Dansk', 'flag' => "\u{1F1E9}\u{1F1F0}"],
            'fi' => ['name' => 'Finnish', 'native_name' => 'Suomi', 'flag' => "\u{1F1EB}\u{1F1EE}"],
            'no' => ['name' => 'Norwegian', 'native_name' => 'Norsk', 'flag' => "\u{1F1F3}\u{1F1F4}"],
            'cs' => ['name' => 'Czech', 'native_name' => 'Čeština', 'flag' => "\u{1F1E8}\u{1F1FF}"],
            'el' => ['name' => 'Greek', 'native_name' => 'Ελληνικά', 'flag' => "\u{1F1EC}\u{1F1F7}"],
            'he' => ['name' => 'Hebrew', 'native_name' => 'עברית', 'flag' => "\u{1F1EE}\u{1F1F1}"],
            'id' => ['name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => "\u{1F1EE}\u{1F1E9}"],
            'fil' => ['name' => 'Filipino', 'native_name' => 'Filipino', 'flag' => "\u{1F1F5}\u{1F1ED}"],
        ];

        return $languages[$code] ?? ['name' => ucfirst($code), 'native_name' => $code, 'flag' => '🌐'];
    }

    /**
     * Get all available languages for dropdown
     */
    public static function getAvailableLanguages(): array
    {
        return [
            'en' => '🇺🇸 English',
            'es' => '🇪🇸 Spanish - Español',
            'fr' => '🇫🇷 French - Français',
            'de' => '🇩🇪 German - Deutsch',
            'it' => '🇮🇹 Italian - Italiano',
            'pt' => '🇧🇷 Portuguese - Português',
            'zh' => '🇨🇳 Chinese - 中文',
            'ja' => '🇯🇵 Japanese - 日本語',
            'ko' => '🇰🇷 Korean - 한국어',
            'ar' => '🇸🇦 Arabic - العربية',
            'hi' => '🇮🇳 Hindi - हिन्दी',
            'ru' => '🇷🇺 Russian - Русский',
            'vi' => '🇻🇳 Vietnamese - Tiếng Việt',
            'pl' => '🇵🇱 Polish - Polski',
            'nl' => '🇳🇱 Dutch - Nederlands',
            'uk' => '🇺🇦 Ukrainian - Українська',
            'tr' => '🇹🇷 Turkish - Türkçe',
            'th' => '🇹🇭 Thai - ไทย',
            'sv' => '🇸🇪 Swedish - Svenska',
            'da' => '🇩🇰 Danish - Dansk',
            'fi' => '🇫🇮 Finnish - Suomi',
            'no' => '🇳🇴 Norwegian - Norsk',
            'cs' => '🇨🇿 Czech - Čeština',
            'el' => '🇬🇷 Greek - Ελληνικά',
            'he' => '🇮🇱 Hebrew - עברית',
            'id' => '🇮🇩 Indonesian - Bahasa Indonesia',
            'fil' => '🇵🇭 Filipino',
        ];
    }
}
