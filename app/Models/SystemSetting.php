<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'is_encrypted',
        'type',
        'description',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get a setting value
     */
    public static function getValue(string $group, string $key, $default = null)
    {
        $setting = self::where('group', $group)->where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->is_encrypted
            ? Crypt::decryptString($setting->value)
            : $setting->value;

        return self::castValue($value, $setting->type);
    }

    /**
     * Set a setting value
     */
    public static function setValue(string $group, string $key, $value, bool $encrypt = false, string $type = 'string', ?string $description = null): self
    {
        $storedValue = $encrypt ? Crypt::encryptString($value) : $value;

        return self::updateOrCreate(
            ['group' => $group, 'key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $encrypt,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings for a group
     */
    public static function getGroup(string $group): array
    {
        $settings = self::where('group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $value = $setting->is_encrypted
                ? Crypt::decryptString($setting->value)
                : $setting->value;

            $result[$setting->key] = self::castValue($value, $setting->type);
        }

        return $result;
    }

    /**
     * Set multiple settings for a group
     */
    public static function setGroup(string $group, array $settings, array $encrypted = [], array $types = []): void
    {
        foreach ($settings as $key => $value) {
            $encrypt = in_array($key, $encrypted);
            $type = $types[$key] ?? 'string';

            self::setValue($group, $key, $value, $encrypt, $type);
        }
    }

    /**
     * Cast value to proper type
     */
    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'boolean', 'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $value,
            'float', 'double' => (float) $value,
            'array', 'json' => json_decode($value, true) ?? [],
            default => $value,
        };
    }

    /**
     * Check if a setting exists
     */
    public static function has(string $group, string $key): bool
    {
        return self::where('group', $group)->where('key', $key)->exists();
    }

    /**
     * Delete a setting
     */
    public static function forget(string $group, string $key): bool
    {
        return self::where('group', $group)->where('key', $key)->delete() > 0;
    }

    /**
     * Delete all settings for a group
     */
    public static function forgetGroup(string $group): int
    {
        return self::where('group', $group)->delete();
    }
}
