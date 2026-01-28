<?php

namespace Database\Seeders;

use App\Models\AiLanguage;
use Illuminate\Database\Seeder;

class AiLanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'flag' => '🇸🇦'],
            ['code' => 'zh', 'name' => 'Chinese (Mandarin)', 'native_name' => '中文', 'flag' => '🇨🇳'],
            ['code' => 'cs', 'name' => 'Czech', 'native_name' => 'Čeština', 'flag' => '🇨🇿'],
            ['code' => 'da', 'name' => 'Danish', 'native_name' => 'Dansk', 'flag' => '🇩🇰'],
            ['code' => 'nl', 'name' => 'Dutch', 'native_name' => 'Nederlands', 'flag' => '🇳🇱'],
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'flag' => '🇺🇸'],
            ['code' => 'fil', 'name' => 'Filipino', 'native_name' => 'Filipino', 'flag' => '🇵🇭'],
            ['code' => 'fi', 'name' => 'Finnish', 'native_name' => 'Suomi', 'flag' => '🇫🇮'],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'flag' => '🇫🇷'],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'flag' => '🇩🇪'],
            ['code' => 'el', 'name' => 'Greek', 'native_name' => 'Ελληνικά', 'flag' => '🇬🇷'],
            ['code' => 'he', 'name' => 'Hebrew', 'native_name' => 'עברית', 'flag' => '🇮🇱'],
            ['code' => 'hi', 'name' => 'Hindi', 'native_name' => 'हिन्दी', 'flag' => '🇮🇳'],
            ['code' => 'id', 'name' => 'Indonesian', 'native_name' => 'Bahasa Indonesia', 'flag' => '🇮🇩'],
            ['code' => 'it', 'name' => 'Italian', 'native_name' => 'Italiano', 'flag' => '🇮🇹'],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'flag' => '🇯🇵'],
            ['code' => 'ko', 'name' => 'Korean', 'native_name' => '한국어', 'flag' => '🇰🇷'],
            ['code' => 'no', 'name' => 'Norwegian', 'native_name' => 'Norsk', 'flag' => '🇳🇴'],
            ['code' => 'pl', 'name' => 'Polish', 'native_name' => 'Polski', 'flag' => '🇵🇱'],
            ['code' => 'pt', 'name' => 'Portuguese', 'native_name' => 'Português', 'flag' => '🇧🇷'],
            ['code' => 'ru', 'name' => 'Russian', 'native_name' => 'Русский', 'flag' => '🇷🇺'],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'flag' => '🇪🇸'],
            ['code' => 'sv', 'name' => 'Swedish', 'native_name' => 'Svenska', 'flag' => '🇸🇪'],
            ['code' => 'th', 'name' => 'Thai', 'native_name' => 'ไทย', 'flag' => '🇹🇭'],
            ['code' => 'tr', 'name' => 'Turkish', 'native_name' => 'Türkçe', 'flag' => '🇹🇷'],
            ['code' => 'uk', 'name' => 'Ukrainian', 'native_name' => 'Українська', 'flag' => '🇺🇦'],
            ['code' => 'vi', 'name' => 'Vietnamese', 'native_name' => 'Tiếng Việt', 'flag' => '🇻🇳'],
        ];

        foreach ($languages as $lang) {
            AiLanguage::updateOrCreate(
                ['code' => $lang['code']],
                array_merge($lang, ['enabled' => true, 'doc_count' => 0])
            );
        }
    }
}
