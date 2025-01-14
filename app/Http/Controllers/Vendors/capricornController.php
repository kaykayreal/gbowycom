<?php

namespace App\Http\Controllers\Vendors;

use Illuminate\Http\Request;
use App\Services\CapricornService;
use App\Http\Controllers\Controller;

class capricornController extends Controller
{
    //
    protected $capricornService;
    public function __construct(CapricornService $capricornService)
    {
        $this->capricornService = $capricornService;
    }

    public function getBalance()
    {
        return $this->capricornService->getBalance();
    }

    public function lkup(Request $request)
    {
        $service_type = $request->service_type;
        $customerId = $request->customerId;

        $service = new CapricornService;
        return $service->lookup($service_type, $customerId);
    }

    public function verifyTxn(Request $request)
    {
        $transaction_ref = $request->transaction_ref;

        $service = new CapricornService;
        return $service->verifyTransaction($transaction_ref);
    }
}
