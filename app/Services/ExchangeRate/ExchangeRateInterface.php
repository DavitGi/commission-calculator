<?php

namespace App\Services\ExchangeRate;

interface ExchangeRateInterface
{
    public function amountToEur($operation): float|int;

    public function amountFromEur($amount, $currency): float|int;

    public function getCurrencyRate(mixed $currency);
}
