<?php

namespace App\Services\Commision;

use App\Enums\ClientTypes;
use App\Enums\OperationTypes;
use App\Services\ExchangeRate\ExchangeRateFactory;
use App\Services\ExchangeRate\ExchangeRateInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CommissionService
{
    protected object $exchangeRateService;

    /**
     * CommissionService constructor.
     * @param ExchangeRateFactory $exchangeRateFactory
     */
    public function __construct(public ExchangeRateFactory $exchangeRateFactory)
    {
        $this->exchangeRateService = $exchangeRateFactory->getExchangeRateService();
    }

    /**
     *
     * @param $csvFile
     * @return array
     * @throws \Exception
     */
    public function getAllOperationFromCsv($csvFile): array
    {
        $filePath = storage_path($csvFile);

        if (!file_exists($filePath)) {
            throw new \Exception('File not found in storage folder!');
        }

        $file = fopen($filePath, 'r');

        $header = [
            'date',
            'client_id',
            'client_type',
            'operation_type',
            'amount',
            'currency',
            'operation_id',
        ];

        $operations = [];

        $operationId = 0;

        while ($row = fgetcsv($file)) {
            $row[] = $operationId;
            $operations[] = array_combine($header, $row);
            $operationId++;
        }

        fclose($file);

        return $operations;
    }

    /**
     * calculate commission for each operation
     * @param $operation
     * @param $operations
     * @return string
     */
    public function calculateCommission($operation, $operations): string
    {
        $commission = 0;

        if ($operation['operation_type'] === OperationTypes::DEPOSIT->value) {
            $commission = $this->calculateDepositCommission($operation);
        }

        if ($operation['operation_type'] === OperationTypes::WITHDRAW->value) {
            $commission = $this->calculateWithdrawCommission($operation, $operations);
        }

        return number_format(ceil($commission * 100) / 100,2);

    }
    /**
     * calculate commission for deposit operation
     * @param $operation
     * @return float
     */
    private function calculateDepositCommission($operation): float
    {
        if ($operation['client_type'] === ClientTypes::PRIVATE->value) {
            return $operation['amount'] * 0.0003;
        }

        if ($operation['client_type'] === ClientTypes::BUSINESS->value) {
            return $operation['amount'] * 0.0003;
        }
    }

    /**
     * calculate commission for withdraw operation
     * @param $operation
     * @param $operations
     * @return float
     */
    private function calculateWithdrawCommission($operation, $operations): float
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
                        $operationAmountPerWeek += $this->exchangeRateService->amountToEur($clientTransaction);
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
                    $amount = max($this->exchangeRateService->amountToEur($operation) + $operationAmountPerWeek - 1000, 0);
                    return $this->exchangeRateService->amountFromEur($amount, $operation['currency']) * 0.003;
                }
            }
        }
        if ($operation['client_type'] === ClientTypes::BUSINESS->value) {
            return $operation['amount'] * 0.005;
        }
    }
}
