<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'organizer_name',
        'allows_multiple_choices',
        'slug',
        'created_at',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'allows_multiple_choices' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function dates(): HasMany
    {
        return $this->hasMany(PollDate::class)->orderBy('date');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PollResponse::class)->latest();
    }
}