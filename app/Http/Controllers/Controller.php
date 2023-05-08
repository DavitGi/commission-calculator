<?php

namespace App\Http\Controllers;

use App\Services\CommissionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function __invoke(CommissionService $service)
    {
        $service->getCommissions('app/transaction.csv');
    }
}
