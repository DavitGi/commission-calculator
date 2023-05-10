<?php

namespace App\Services\ExchangeRate;

use Illuminate\Support\Facades\Http;

class ExchangeRateService implements ExchangeRateInterface
{
    /**
     * convert amount to EUR
     * @param $operation
     * @return float|int
     */
    public function amountToEur($operation): float|int
    {
        $amount = $operation['amount'];
        $currency = $operation['currency'];

        if ($currency !== 'EUR') {
            $currencyRate = $this->getCurrencyRate($currency);
            $amount = $amount / $currencyRate;
        } else {
            $amount = $amount * 1;
        }

        return $amount;
    }

    /**
     * convert amount from EUR
     * @param $amount
     * @param $currency
     * @return float|int
     */

    public function amountFromEur($amount, $currency): float|int
    {
        if ($currency !== 'EUR') {
            $currencyRate = $this->getCurrencyRate($currency);
            $amount = $amount * $currencyRate;
        } else {
            $amount = $amount * 1;
        }

        return $amount;
    }

    /**
     * get currency rate
     * @param mixed $currency
     * @return mixed
     */
    public function getCurrencyRate(mixed $currency): mixed
    {
        $currencyRates = Http::get(config('exchangeRate.providers.test.url'));

        if ($currencyRates->failed()) {
            abort(500, 'Failed to get currency rates');
        }
        $currencyRates = json_decode($currencyRates->body(), true);
        $currencyRates = $currencyRates['rates'];

        return $currencyRates[$currency];
    }
}
