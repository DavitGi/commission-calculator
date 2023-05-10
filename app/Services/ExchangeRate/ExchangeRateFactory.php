<?php

namespace App\Services\ExchangeRate;

use App\Enums\ExchangeRateProviders;

class ExchangeRateFactory
{
    /**
     * For future, if we want to add more providers
     * @return ExchangeRateInterface
     */
    public static function getExchangeRateService(): ExchangeRateInterface
    {
        return new ExchangeRateService();
    }

}
