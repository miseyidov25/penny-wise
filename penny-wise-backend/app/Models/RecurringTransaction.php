<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
    'user_id',
    'wallet_id',
    'category_id',
    'amount',
    'description',
    'interval',
    'next_run',
    'end_date',
];

public function user()
{
    return $this->belongsTo(User::class);
}

public function wallet()
{
    return $this->belongsTo(Wallet::class);
}

public function category()
{
    return $this->belongsTo(Category::class);
}

}
