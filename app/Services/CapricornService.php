<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use App\Models\BillerVendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;

class CapricornService
{

    protected $capricorn;
    protected $x_api_key;
    protected $agentId;
    protected $header;
    protected $url;

    public function __construct()
    {
        $this->capricorn = BillerVendor::where("billerVendorName", "capricorn")->first();
        $this->x_api_key = decrypt($this->capricorn->billerVendorKey);
        $this->agentId = decrypt($this->capricorn->agentId);
        $this->url = decrypt($this->capricorn->url);
        $this->header = [
            'x-api-key' => $this->x_api_key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }

    public function purchaseAirtime($meta)
    {

        $payload = [
            "phone" => $meta->customerId,
            "amount" => intval($meta->amount),
            "service_type" => $meta->biller,
            "plan" => $meta->plan,
            "agentId" => $this->agentId,
            "agentReference" => strval($meta->transactionId)
        ];

        try {
            $response = Http::withHeaders($this->header)->timeout(10)->post($this->url . "airtime/request", $payload);
            Log::info("airtime vending for" . $payload['agentReference'] . "  response : $response ");
            return $response;
        } catch (Exception $e) {
            Log::info("could not send airtime request with transaction id :" . $payload['agentReference']);
            return false;
        }
    }

    public function purchaseData($meta)
    {
        $payload = [
            "phone" => $meta->customerId,
            "amount" => intval($meta->amount),
            "service_type" => $meta->biller,
            "plan" => $meta->plan,
            "datacode" => $meta->subscription,
            "agentId" => $this->agentId,
            "agentReference" => strval($meta->transactionId)
        ];

        try {
            $response = Http::withHeaders($this->header)->timeout(10)->post($this->url . "databundle/request", $payload);
            Log::info("Data Bundle for phoneNo: " . $payload['phone'] . " Transaction Id :" . $payload['agentReference'] . "  response : $response ");
            return $response;
        } catch (Exception $e) {
            Log::info("could not send capricorn databundle request with transaction id :" . $payload['agentReference']);
            return false;
        }
    }

    public function lookup($service_type, $customerId)
    {
        $payload = [
            'service_type' => $service_type,
            'account_number' => $customerId,
        ];

        try { // Send the HTTP request with headers and payload
            $response = Http::withHeaders($this->header)->timeout(10)->post($this->url . "electricity/verify", $payload);
            //Log::info("Data Bundle for phoneNo: ". $response );
            return $response;
        } catch (RequestException $exception) {
            // Handle the exception, log it, or return a custom response
            return response()->json(['error' => 'Error in HTTP request'], 500);
        }
    }

    public function getBalance()
    {

        try { // Send the HTTP request with headers and payload
            $response = Http::withHeaders($this->header)->timeout(10)->get($this->url . "superagent/account/balance");
            Log::info("Capricorn Wallet Balance : " . $response);
            return $response;
        } catch (RequestException $exception) {
            // Handle the exception, log it, or return a custom response
            return response()->json(['error' => 'Error in HTTP request'], 500);
        }
    }

    public function verifyTransaction($transaction_ref)
    {
        try { // Send the HTTP request with headers and payload
            $response = Http::withHeaders($this->header)->timeout(10)->get($this->url . "superagent/transaction/requery?agentReference=$transaction_ref");
            Log::info("Customer Details : " . $response);
            return $response;
        } catch (RequestException $exception) {
            // Handle the exception, log it, or return a custom response
            return response()->json(['error' => 'Error in HTTP request'], 500);
        }
    }
}
