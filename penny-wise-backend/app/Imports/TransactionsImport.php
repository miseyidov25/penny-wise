<?php

namespace App\Imports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\ToModel;

class TransactionsImport implements ToModel
{
    public function model(array $row)
    {
        return new Transaction([
            'user_id' => auth()->id(),
            'wallet_id' => $row[0],
            'category_id' => $row[1],
            'amount' => $row[2],
            'currency' => $row[3],
            'description' => $row[4],
            'date' => $row[5],
        ]);
    }
}
