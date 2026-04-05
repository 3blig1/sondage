<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollResponseChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_response_id',
        'poll_date_id',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(PollResponse::class, 'poll_response_id');
    }

    public function date(): BelongsTo
    {
        return $this->belongsTo(PollDate::class, 'poll_date_id');
    }
}