<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CurrencyConversionService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.currencyapi.key');
        $this->baseUrl = config('services.currencyapi.url');
    }

    public function convert($from, $to, $amount)
{
    $response = Http::withOptions(['verify' => false])->get($this->baseUrl, [
        'apikey' => $this->apiKey,
        'currencies' => $to,
        'base_currency' => $from
    ]);

    \Log::info('Currency API response:', [
        'status' => $response->status(),
        'body' => $response->body()
    ]);

    if ($response->successful()) {
        $data = $response->json();

        if (isset($data['data'][$to]['value'])) {
            $rate = $data['data'][$to]['value'];
            return $amount * $rate;
        } else {
            \Log::error('Currency rate not found in API response.', $data);
        }
    } else {
        \Log::error('Currency API call unsuccessful.', [
            'status' => $response->status(),
            'body' => $response->body()
        ]);
    }

    throw new \Exception('Currency conversion failed.');
}


    public function convertForWallet($walletCurrency, $targetCurrency, $amount)
{
    if ($walletCurrency === $targetCurrency) {
        return $amount; // No conversion needed if the currencies are the same
    }

    return $this->convert($walletCurrency, $targetCurrency, $amount);
}

}
