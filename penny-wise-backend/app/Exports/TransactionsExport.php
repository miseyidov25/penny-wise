<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
{
    return Transaction::with('category')
        ->where('user_id', auth()->id())
        ->get();
}

    public function headings(): array
    {
        return [
            'Transaction Name',
            'Amount',
            'Category Name',
            'Currency',
            'Start Date',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->description,
            $transaction->amount,
            $transaction->category ? $transaction->category->name : '',
            $transaction->currency,
            $transaction->start_date ?? $transaction->created_at->format('Y-m-d'),
        ];
    }
}


