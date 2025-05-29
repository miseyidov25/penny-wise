<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'target_amount',
        'currency',
        'deadline',
        'wallet_id',
        'current_amount',
        'is_completed',
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_completed' => 'boolean',
    ];

    protected $appends = ['progress_percentage'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->target_amount == 0) {
            return 0;
        }

        return round(($this->current_amount / $this->target_amount) * 100, 2);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    protected $with = ['wallet'];

}
