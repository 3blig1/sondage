<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'participant_name',
        'comment',
    ];

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function choices(): HasMany
    {
        return $this->hasMany(PollResponseChoice::class)->with('date');
    }
}