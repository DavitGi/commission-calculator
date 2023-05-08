<?php

namespace Tests\Unit;

use App\Services\CommissionService;
use PHPUnit\Framework\TestCase;

class CommissionTest extends TestCase
{

    public function test_private_client_deposit(): void
    {
        $operations = [
            [
                'date' => '2014-12-31',
                'client_id' => '4',
                'client_type' => 'private',
                'operation_type' => 'deposit',
                'amount' => '1000.00',
                'currency' => 'EUR'
            ]
        ];

        $service = new CommissionService();

        foreach ($operations as $operation) {
            $this->assertEquals(0.3, $service->calculateCommission($operation, $operations));
        }
    }

public function test_private_client_withdraw(): void
    {
        //if amount is less than 1000 and
        $operations = [
            [
                'date' => '2014-12-31',
                'client_id' => '4',
                'client_type' => 'private',
                'operation_type' => 'withdraw',
                'amount' => '1000.00',
                'currency' => 'EUR'
            ]
        ];

        $service = new CommissionService();

        foreach ($operations as $operation) {
            $this->assertEquals(0, $service->calculateCommission($operation, $operations));
        }

        //if amount is more than 1000 and
        $operations = [
            [
                'date' => '2014-12-31',
                'client_id' => '4',
                'client_type' => 'private',
                'operation_type' => 'withdraw',
                'amount' => '1000.00',
                'currency' => 'EUR',
                'operation_id' => '1'
            ],
            [
                'date' => '2014-12-31',
                'client_id' => '4',
                'client_type' => 'private',
                'operation_type' => 'withdraw',
                'amount' => '1000.00',
                'currency' => 'EUR',
                'operation_id' => '2'
            ]
        ];

        $assertions = [
            0,
            3
        ];

        foreach ($operations as $key => $operation) {
            $this->assertEquals($assertions[$key],$service->calculateCommission($operation, $operations));
        }
    }

}