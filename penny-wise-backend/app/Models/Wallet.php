<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'balance', 'currency', 'user_id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function goals()
    {
        return $this->hasMany(Goal::class);
    }


    public function recurringTransactions()
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
