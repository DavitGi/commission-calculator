<?php

namespace App\Enums;

enum OperationTypes: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
}
