<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Transaction;
use App\Models\Category;

class TransactionsImport implements ToModel, WithHeadingRow
{
    protected $userId;
    protected $walletId;

    public function __construct($userId, $walletId)
    {
        $this->userId = $userId;
        $this->walletId = $walletId;
    }

    public function model(array $row)
    {
        \Log::info('Row received', $row);

        if (empty($row['transaction_name'])) {
            \Log::warning('Skipping empty row');
            return null;
        }

        $categoryId = $this->getCategoryIdByName($row['category_name']);

        return new Transaction([
            'user_id'     => $this->userId,
            'wallet_id'   => $this->walletId,
            'category_id' => $categoryId,
            'amount'      => $row['amount'],
            'currency'    => $row['currency'],
            'description' => $row['transaction_name'],
            'date'        => $row['start_date'],
        ]);
    }

    protected function getCategoryIdByName(string $categoryName)
    {
        $category = Category::firstOrCreate([
            'name'     => $categoryName,
            'user_id'  => $this->userId,
        ]);

        return $category->id;
    }
}


