<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiKnowledgeDocument extends Model
{
    protected $fillable = [
        'title',
        'category',
        'content',
        'keywords',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Search knowledge documents by keywords
     */
    public static function search(string $query): \Illuminate\Database\Eloquent\Collection
    {
        $terms = explode(' ', strtolower($query));

        return static::where('is_active', true)
            ->where(function ($q) use ($terms, $query) {
                // Match title
                $q->where('title', 'like', "%{$query}%");

                // Match content
                $q->orWhere('content', 'like', "%{$query}%");

                // Match keywords
                foreach ($terms as $term) {
                    if (strlen($term) >= 3) {
                        $q->orWhereJsonContains('keywords', $term);
                    }
                }
            })
            ->orderByDesc('priority')
            ->limit(5)
            ->get();
    }

    /**
     * Get knowledge by category
     */
    public static function getByCategory(string $category): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where('category', $category)
            ->orderByDesc('priority')
            ->get();
    }

    /**
     * Build context string for AI
     */
    public static function buildContext(string $query): string
    {
        $docs = static::search($query);

        if ($docs->isEmpty()) {
            return '';
        }

        $context = "\n\n## Relevant Knowledge:\n";
        foreach ($docs as $doc) {
            $context .= "### {$doc->title}\n{$doc->content}\n\n";
        }

        return $context;
    }
}
