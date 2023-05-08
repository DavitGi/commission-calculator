<?php

namespace App\Services;

use App\Enums\ClientTypes;
use App\Enums\OperationTypes;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CommissionService
{

    public function __construct()
    {
    }


    public function calculateCommission($operation, $operations)
    {
        $commission = 0;

        if ($operation['operation_type'] === OperationTypes::DEPOSIT->value) {
            $commission = $this->calculateDepositCommission($operation);
        }

        if ($operation['operation_type'] === OperationTypes::WITHDRAW->value) {
            $commission = $this->calculateWithdrawCommission($operation, $operations);
        }
        return ceil($commission * pow(10, 2)) / pow(10, 2);

    }

    private function calculateDepositCommission($operation)
    {
        $commission = 0;

        if ($operation['client_type'] === ClientTypes::PRIVATE->value) {
            $commission = $operation['amount'] * 0.0003;
        }

        if ($operation['client_type'] === ClientTypes::BUSINESS->value) {
            $commission = $operation['amount'] * 0.0003;
        }

        return $commission;
    }

    private function calculateWithdrawCommission($operation, $operations)
    {
        if ($operation['client_type'] === ClientTypes::PRIVATE->value) {
            $date = Carbon::parse($operation['date']);
            $weekStart = $date->startOfWeek()->format('Y-m-d');
            $weekEnd = $date->endOfWeek()->format('Y-m-d');
            $clientTransactions = collect($operations)->where('client_id', $operation['client_id'])->toArray();
            $operationAmountPerWeek = 0;
            $operationCountPerWeek = 0;

            foreach ($clientTransactions as $clientTransaction) {
                if ($clientTransaction['operation_type'] == OperationTypes::WITHDRAW->value) {
                    $operationDate = Carbon::parse($clientTransaction['date']);
                    if ($operationDate->between($weekStart, $weekEnd)) {
                        if ($clientTransaction === $operation) {
                            break;
                        }
                        $operationAmountPerWeek += $this->amountToEur($clientTransaction);
                        $operationCountPerWeek++;
                    }
                }
            }
            if ($operationCountPerWeek > 3) {
                return $operation['amount'] * 0.003;
            } else {
                if ($operationAmountPerWeek >= 1000) {
                    return $operation['amount'] * 0.003;
                } else {
                    $amount = max($this->amountToEur($operation) + $operationAmountPerWeek - 1000, 0);
                    return $this->amountFromEur($amount, $operation['currency']) * 0.003;
                }
            }
        }
        if ($operation['client_type'] === ClientTypes::BUSINESS->value) {
            return $operation['amount'] * 0.005;
        }
    }

    private function amountToEur($operation): float|int
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

    private function amountFromEur($amount, $currency): float|int
    {
        if ($currency !== 'EUR') {
            $currencyRate = $this->getCurrencyRate($currency);
            $amount = $amount * $currencyRate;
        } else {
            $amount = $amount * 1;
        }

        return $amount;
    }

    private function getCurrencyRate(mixed $currency)
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
