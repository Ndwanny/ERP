<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketingEvent extends Model
{
    protected $table = 'marketing_campaigns';

    protected $fillable = [
        'created_by', 'name', 'description', 'type', 'status', 'start_date', 'end_date',
        'budget', 'actual_spend', 'target_audience', 'metrics',
    ];

    protected $casts = [
        'start_date' => 'date', 'end_date' => 'date',
        'target_audience' => 'array', 'metrics' => 'array',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('event_type', function ($query) {
            $query->where('type', 'event');
        });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
