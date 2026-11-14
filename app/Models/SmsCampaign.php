<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsCampaign extends Model
{
    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class, 'campaign_id');
    }

    /** Recipients we never got to (the send stopped early). */
    public function getNotSentAttribute(): int
    {
        return max($this->recipients - $this->sent - $this->failed, 0);
    }
}
