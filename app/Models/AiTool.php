<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTool extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'parameters',
        'handler_class',
        'requires_event',
        'requires_auth',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'parameters' => 'array',
        'requires_event' => 'boolean',
        'requires_auth' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Get all active tools by category
     */
    public static function getActiveByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where('category', $category)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get all active tools
     */
    public static function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->orderBy('category')
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Build available tools description for AI prompt
     */
    public static function buildToolsDescription(?bool $hasEvent = null): string
    {
        $tools = static::where('is_active', true);

        if ($hasEvent === false) {
            $tools->where('requires_event', false);
        }

        $tools = $tools->orderBy('category')->orderBy('display_order')->get();

        if ($tools->isEmpty()) {
            return '';
        }

        $grouped = $tools->groupBy('category');
        $description = "\n## Available Commands:\n";

        foreach ($grouped as $category => $categoryTools) {
            $categoryName = ucfirst(str_replace('_', ' ', $category));
            $description .= "\n### {$categoryName}:\n";

            foreach ($categoryTools as $tool) {
                $description .= "- **{$tool->name}**: {$tool->description}\n";
            }
        }

        return $description;
    }
}
