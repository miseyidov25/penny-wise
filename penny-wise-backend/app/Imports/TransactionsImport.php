<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Services\CurrencyConversionService;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Wallet;


class TransactionsImport implements ToModel, WithHeadingRow
{
    protected $userId;
    protected $walletId;
    protected $currencyConversionService;

    public function __construct($userId, $walletId, CurrencyConversionService $currencyConversionService)
    {
        $this->userId = $userId;
        $this->walletId = $walletId;
        $this->currencyConversionService = $currencyConversionService;
    }


    public function model(array $row)
    {
        \Log::info('Row received', $row);

        if (empty($row['transaction_name'])) {
            \Log::warning('Skipping empty row');
            return null;
        }

        $description = $row['transaction_name'] ?? '';

        $categoryId = $this->getCategoryIdByName($row['category_name']);

        // Get wallet currency (fetch once or pass to constructor for optimization)
        $walletCurrency = Wallet::find($this->walletId)->currency;

        $amount = $row['amount'];
        $transactionCurrency = $row['currency'];

        // Convert amount if currencies differ
        if ($transactionCurrency !== $walletCurrency) {
            try {
                $amount = $this->currencyConversionService->convert($transactionCurrency, $walletCurrency, $amount);
            } catch (\Exception $e) {
                \Log::error('Currency conversion failed: ' . $e->getMessage());
                // Optionally handle failure: skip this row or use original amount
                // For now, just keep original amount (you can change behavior)
            }
        }
    
        return new Transaction([
            'user_id'     => $this->userId,
            'wallet_id'   => $this->walletId,
            'category_id' => $categoryId,
            'amount'      => $amount,
            'currency'    => $walletCurrency,
            'description' => $description,
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

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}


