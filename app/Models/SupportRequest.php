<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportRequest extends Model
{
    protected $fillable = [
        'title',
        'description',
        'response',
        'reason_refusal',
        'type',
        'priority',
        'status',
        'user_id',
        'support_user_id',
        'start_date',
        'conclusion_date',
        'cancellation_date',
        'refusal_date'
    ];

    public function usuario() {
        return $this->belongsTo(User::class, 'user_id');
    }
}


