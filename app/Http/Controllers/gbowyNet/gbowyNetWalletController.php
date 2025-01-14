<?php

namespace App\Http\Controllers\gbowyNet;


use Illuminate\Http\Request;
use App\Jobs\ProcessPaymentJob;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BillerService;
use App\Services\GbowyNetWalletService;
use Illuminate\Support\Facades\Validator;

class gbowyNetWalletController extends Controller
{
    public function gbowyNetWallets(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'biller' => ['required', 'string'],
            'category' => ['required', 'string'],
            'amount' => ['required', 'string'],
            'customerId' => ['required', 'string'],
            'paygate' => ['required', 'string'],
            'plan' => ['required', 'string'],
            'preferredVendor' => ['required', 'string'],
            'realAmount' => ['required', 'string'],
            'transactionId' => ['required', 'numeric', 'unique:payment_transactions,txnRef'],
            'userId' => ['required', 'numeric'],
            'description' => ['required', 'string'],

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        //first you confirm the transaction id to continue processing...
        $walletService = new GbowyNetWalletService();
        $transaction = $walletService->verifyTranssaction($request->transactionId);

        //if transaction is successfully confirmed 
        if ($transaction['status'] == 200) {

            // transaction successfully verified extract values dispatch the transaction to a job
            $txn =  json_decode($transaction['data'], true);

            DB::beginTransaction();
            $meta = [
                'customerId' => $request->customerId,
                'transactionId' => $request->transactionId,
                'category' => $request->category,
                'biller' => $request->biller,
                'subscription' => $request->has('subscription') ? $request->subscription : '',
                'subscriptionMonth' => $request->has('subscriptionMonth') ? $request->subscriptionMonth : '',
                'addon' => $request->has('addon') ? $request->addon : '',
                'addonMonth' => $request->has('addonMonth') ? $request->addonMonth : '',
                'preferredVendor' => $request->preferredVendor,
                'amount' => $request->realAmount,
                'vendingStatus' => 0,
                'vendingRetrials' => 0,
                'payload' => json_encode($request->all()),
                'plan' => $request->plan,
                'merchantCode' => "531908874",
            ];
            BillerService::create($meta);

            $value = [
                'payGateName' => "Gbowy Wallet",
                'txnRef' => $txn['data']['transaction_ref'],
                'amount' => $request->amount,
                'fees' => $txn['data']['fees'],
                'gatewayResponse' => "transaction Found",
                'gateId' => "2",
                'ipAddress' => "",
                'status' => "Successful",
                'bank' => "",
                'bin' => "",
                'brand' => "",
                'channel' => "",
                'expMonth' => "",
                'expYear' => "",
                'lastFour' => "",
                'dump' => json_encode($txn),
                'transactionId' => $txn['data']['client_ref'],
            ];

            PaymentTransaction::create($value);
        } else {
            // transaction validation failed
        }
        DB::commit();

        ProcessPaymentJob::dispatch(json_decode(json_encode($meta)));
        log::info("meta ;" . ((json_encode($meta))));
        Log::info("vending Job Dispatched");
        return response()->json(["status" => "transaction logged successfully with a 202 response"], 202);
    }
}
