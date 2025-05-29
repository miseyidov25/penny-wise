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
        'current_amount',
        'currency',
        'deadline',
        'is_completed',
        'user_id'
    ];

    protected $casts = [
        'deadline' => 'date',
        'is_completed' => 'boolean',
    ];
    
    public function user()
{
    return $this->belongsTo(User::class);
}

}
