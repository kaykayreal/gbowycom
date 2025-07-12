<?php

namespace App\Http\Controllers;

use App\Http\Controllers\remita\RITSController;
use App\Models\SalCustomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;

class testController extends Controller
{
    public function encrypt(Request $request)
    {
        $encrypted = encrypt($request->data);
        return response()->json(["data" => $encrypted]);
    }

    public function decrypt(Request $request)
    {
        $decrypted = decrypt($request->data);
        return response()->json(["data" => $decrypted]);
    }

    public function getSalaryInfo($customerDetail = null)
    {

        $requestId = strval(mt_rand(10000000, 99999999));
        $header = [
            'Content_Type' => 'application/json',
            'api_key' => 'QzAwMDA1OTY5OTgxMjM0fEMwMDAwNTk2OTk4',
            'merchant_id' => '11362553819',
            'url' => 'https://login.remita.net/remita/exapp/api/v1/send/api/loansvc/data/api/v2/payday/salary/history/provideCustomerDetails'
        ];
        $header = json_decode(json_encode($header));
        $apiToken = "WVlkekt3Q0hSc2NyMEltU3lJYjBmMkdIY3lzQWgwYkhFOHJaeUxOTzJ2b2VuMitNRG9pSDRhWFQ3MldPOVJjUA==";
        $apiHash = $header->api_key . $requestId . $apiToken;
        $apiHash = hash('sha512', $header->api_key . $requestId . $apiToken);
        $requestHeader = ([
            'Content-Type' => $header->Content_Type,
            'API_KEY' => $header->api_key,
            'MERCHANT_ID' => $header->merchant_id,
            'REQUEST_ID' => $requestId,
            'AUTHORIZATION' => "remitaConsumerKey=$header->api_key,remitaConsumerToken=$apiHash",
            'url' => $header->url,
        ]);

        if (!is_null($customerDetail)) {
            $customerDetail = $customerDetail;
        } else {
            $customerDetail = [
                "authorisationCode" => "2633564",
                "firstName" => "DAMILOLA",
                "lastName" => "ADEDIPE",
                "middleName" => "OMOTAYO",
                "accountNumber" => "0043286577",
                "bankCode" => "044",
                "bvn" => "22246788263",
                "authorisationChannel" => "USSD"
            ];
        }

        try {
            $response = json_decode(Http::withHeaders($requestHeader)->timeout(120)->post($requestHeader['url'], ($customerDetail)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            Log::channel('http')->info(json_encode($requestHeader));
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        return $response;
    }



    public function getaccount(Request $request)
    {

        $accountnumber = $request->Account;
        $bankcode = $request->bankCode;

        $val = new RITSController();
        return $val->accountEnquiry($accountnumber, $bankcode);
    }


    public function getSalaryInformation(Request $request)
    {
        $validator = Validator::make($request->all(), [

            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'middleName' => 'nullable|string',
            'accountNumber' => 'required|digits_between:10,12',
            'bankCode' => 'required|string',
            'bvn' => 'required|digits:11',

        ]);

        // Validate the request data
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customerDetail = [
            "authorisationCode" => Mt_rand(1000000, 9999999), // Generate a random 7-digit authorisation code
            "firstName" => $request->firstName,
            "lastName" => $request->lastName,
            "middleName" => $request->middleName ?? null,
            "accountNumber" => $request->accountNumber,
            "bankCode" => $request->bankCode,
            "bvn" => $request->bvn,
            "authorisationChannel" => "web" // Assuming the default channel is 'web'
        ];


        // Call the getSalaryInfo method with validated data
        return
            $this->getSalaryInfo($customerDetail);
    }

    public function lkup()
    {
        //obtain data from the database
        $customerDetail = SalCustomer::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();

        $customerDetail = [
            "authorisationCode" => Mt_rand(1000000, 9999999),
            "firstName" => $request->firstName,
            "lastName" => $request->lastName,
            "middleName" => $request->middleName ?? null,
            "accountNumber" => $request->accountNumber,
            "bankCode" => $request->bankCode,
            "bvn" => $request->bvn,
            "authorisationChannel" => "web"
        ];

        return $this->getSalaryInfo($customerDetail);
    }
}
