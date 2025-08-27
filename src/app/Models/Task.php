<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'tags',
        'completed',
        'completed_at'
    ];

    protected $casts = [
        'tags' => 'array',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected function tags(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true) ?? [],
            set: fn ($value) => json_encode(is_array($value) ? $value : []),
        );
    }

    public function scopeSearch(Builder $query, ?string $search = null): void
    {
        $query->when($search, function (Builder $query) use ($search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereJsonContains('tags', $search);
            });
        });
    }

    public function scopeWithTags(Builder $query, $tags): void
    {
        $query->when($tags, function (Builder $query) use ($tags) {
            if (is_string($tags)) {
                $tags = array_filter(
                    array_map('trim', explode(',', $tags)),
                    fn($tag) => !empty($tag)
                );
            }

            if (is_array($tags) && !empty($tags)) {
                $query->where(function (Builder $query) use ($tags) {
                    foreach ($tags as $tag) {
                        $query->whereJsonContains('tags', $tag);
                    }
                });
            }
        });
    }

    public function scopeCompleted(Builder $query, ?bool $completed = null): void
    {
        $query->when(!is_null($completed), function (Builder $query) use ($completed) {
            $query->where('completed', $completed);
        });
    }
}
