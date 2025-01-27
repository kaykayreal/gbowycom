<?php

namespace App\Http\Controllers\remita;

use Carbon\Carbon;
use App\Models\RemitaDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\RequestException;


class RemitaController extends Controller
{

    public function getSalaryHistory(Request $request)
    {
        //this function takes in the request from an aggregator and routes it directly to the service provider, 
        //if the transaction is successful, it logs it and places a charge to the aggregator via gbowy wallet service. 
        //the service doesn't store the json response, it just outputs it for the aggregator's use 

        $validator = Validator::make($request->all(), [
            "authorisationCode" => ['required', 'numeric'],
            "firstName" => ['required', 'string'],
            "lastName" => ['required', 'string'],
            "accountNumber" => ['required', 'regex:/^\d{10}$/'],
            "bankCode" => ['required', 'regex:/^\d{3}$/'],
            "bvn" => ['required', 'regex:/^\d{11}$/'],
            "authorisationChannel" => ['required', 'string'],
            "merchant_code" => ['required', 'numeric'],
        ], [
            'accountNumber.regex' => 'The account number must be exactly 10 digits.',
            'bankCode.regex' => 'The bank code must be exactly 3 digits.',
            'bvn.regex' => 'The BVN must be exactly 11 digits.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // prepare customer details 
        $customerDetail = ([
            "authorisationCode" => mt_rand(100000, 999999),
            "firstName" => $request->firstName,
            "lastName" => $request->lastName,
            "middleName" => $request->middleName,
            "bvn" => $request->bvn,
            "authorisationChannel" => $request->authorisationChannel,
            "accountNumber" => $request->accountNumber,
            "bankCode" => $request->bankCode,
        ]);
    }

    // this function prepares the request header for remita requests
    public function getRemitaRequestHeaders()
    {
        //get parameters from db
        $requestId = strval(mt_rand(10000000, 99999999));
        $header = RemitaDetail::where('id', 1)->first();
        $apiToken = "WVlkekt3Q0hSc2NyMEltU3lJYjBmMkdIY3lzQWgwYkhFOHJaeUxOTzJ2b2VuMitNRG9pSDRhWFQ3MldPOVJjUA==";
        $apiHash = $header->apikey . $requestId . $apiToken;
        $apiHash = hash('sha512', $header->api_key . $requestId . $apiToken);
        $requestHeader = ([
            'Content-Type' => $header->content_type,
            'API_KEY' => $header->api_key,
            'MERCHANT_ID' => $header->merchant_id,
            'REQUEST_ID' => $requestId,
            'AUTHORIZATION' => "remitaConsumerKey=$header->api_key,remitaConsumerToken=$apiHash",
            'url' => $header->url,
            'inflight' => $header->inflight,
            'sm' => $header->sm
        ]);
        return $requestHeader;
    }

    // get customer salary information from remita using this method
    //it takes in a customer details parameter as follows :
    //    "authorisationCode"=>mt_rand(100000, 999999),
    // "firstName"=>$data->firstName,
    // "lastName"=> $data->lastName,
    // "middleName"=>isset($customerBVNdetails->middleName)?$customerBVNdetails->middleName:"",
    // "bvn"=>$customerBVNdetails->bvn,
    // "authorisationChannel"=> "web",
    // "accountNumber"=>$request->salAcct, 
    // "bankCode"=>$request->bankName,

    public function getSalaryInfo($customerDetail)
    {
        $requestHeader = $this->getRemitaRequestHeaders();
        unset($requestHeader['inflight']);
        unset($requestHeader['sm']);
        try {
            // Function to send the HTTP request
            function sendHttpRequest($requestHeader, $customerDetail)
            {
                return json_decode(Http::withHeaders($requestHeader)->timeout(180)->post($requestHeader['url'], ($customerDetail)));
            }

            $maxRetries = 3; // Set the maximum number of retries
            $attempt = 0;
            $response = null;

            // Retry loop
            while ($attempt < $maxRetries && is_null($response)) {
                $response = sendHttpRequest($requestHeader, $customerDetail);
                $attempt++;

                if (is_null($response)) {
                    Log::warning('HTTP request returned a null response, retrying... (Attempt ' . $attempt . ' of ' . $maxRetries . ')');
                    sleep(1); // Optional: Add a delay between retries
                }
            }

            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            Log::channel('http')->info(json_encode($requestHeader));
            Log::channel('http')->info(json_encode($customerDetail));

            // Additional logic here...

        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            // return false;
            // Optionally, return a custom error response
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            // return false;
            // Optionally, return a custom error response
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        // Continue with your existing logic
        if (is_object($response) && property_exists($response, 'data')) {
            if (is_null($response->data)) {

                // implement relevant charges here 
                //by logging the transaction and calling the wallet service system 


                //check if customer is suspended or customer not found 
                switch ($response->responseMsg) {
                    case 'Customer not found':
                        return 'Customer Not Found';
                        break;
                    case 'Customer Is Currently Suspended':
                        return "Customer Is Currently Suspended";
                        break;
                    default:
                        return "unknown Feedback";
                }
            }

            //if for whatever reason the system returns a failed response,
            //then we will return a failed response to the user just as is...

            if (property_exists($response->data, 'responseCode')) {
                // implement relevant charges here 
                //by logging the transaction and calling the wallet service system 
                if ($response->data->responseCode !== "00" && $response->data->status !== "success") {
                    return $response;
                }
            } else {
                // implement relevant charges here 
                //the response is otherwise successful
                //by logging the transaction and calling the wallet service system 

                return $response;
            }
        }
    }
}
