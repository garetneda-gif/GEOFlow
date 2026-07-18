<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    public const LUCKIN_MCP_SOURCE_TYPE = 'luckin_mcp';

    public const LUCKIN_MCP_SOURCE_URL = 'https://open.lkcoffee.com/mcp';

    public const REVIEWED_STATUSES = ['reviewed', 'approved', 'verified'];

    protected $table = 'knowledge_bases';

    protected $fillable = [
        'name',
        'description',
        'content',
        'character_count',
        'used_task_count',
        'file_type',
        'file_path',
        'word_count',
        'usage_count',
        'source_name',
        'source_url',
        'source_type',
        'business_line',
        'effective_date',
        'risk_level',
        'review_status',
    ];

    protected function casts(): array
    {
        return [
            'character_count' => 'integer',
            'used_task_count' => 'integer',
            'word_count' => 'integer',
            'usage_count' => 'integer',
            'effective_date' => 'date',
        ];
    }

    public function chunks(): HasMany
    {
        return $this->hasMany(KnowledgeChunk::class, 'knowledge_base_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'knowledge_base_id');
    }

    public function linkedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_knowledge_bases')
            ->withPivot(['sort_order'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('tasks.id');
    }

    public function isUsableForGeneration(): bool
    {
        if (! $this->isLuckinMcpSource()) {
            return true;
        }

        return in_array(strtolower(trim((string) $this->review_status)), self::REVIEWED_STATUSES, true);
    }

    public function isLuckinMcpSource(): bool
    {
        return (string) $this->source_type === self::LUCKIN_MCP_SOURCE_TYPE
            && (string) $this->source_url === self::LUCKIN_MCP_SOURCE_URL;
    }

    public function scopeUsableForGeneration(Builder $query): Builder
    {
        return $query->where(function (Builder $outer): void {
            $outer->where(function (Builder $nonLuckin): void {
                $nonLuckin
                    ->where('source_type', '!=', self::LUCKIN_MCP_SOURCE_TYPE)
                    ->orWhereNull('source_type')
                    ->orWhere('source_url', '!=', self::LUCKIN_MCP_SOURCE_URL)
                    ->orWhereNull('source_url');
            })->orWhereIn('review_status', self::REVIEWED_STATUSES);
        });
    }
}
