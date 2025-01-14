<?php

namespace App\Http\Controllers;

use App\Http\Controllers\remita\RITSController;
use App\Services\CapricornService;
use App\Services\WebhookService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;

class testController extends Controller
{
    public function encrypt(Request $request){
        $encrypted = encrypt($request->data);
        return response()->json(["data"=> $encrypted]);
    }

    public function decrypt(Request $request) {
        $decrypted = decrypt($request->data);
        return response()->json(["data" => $decrypted]);
    }
    
    public function getSalaryInfo(){
        
        $requestId = strval(mt_rand(10000000, 99999999));
        $header = [
            'Content_Type' => 'application/json',
            'api_key'=>'QzAwMDA1OTY5OTgxMjM0fEMwMDAwNTk2OTk4',
            'merchant_id'=>'11362553819',
            'url'=>'https://login.remita.net/remita/exapp/api/v1/send/api/loansvc/data/api/v2/payday/salary/history/provideCustomerDetails'
        ];
        $header = json_decode(json_encode($header));
        $apiToken = "WVlkekt3Q0hSc2NyMEltU3lJYjBmMkdIY3lzQWgwYkhFOHJaeUxOTzJ2b2VuMitNRG9pSDRhWFQ3MldPOVJjUA==";
        $apiHash=$header->api_key.$requestId.$apiToken;
        $apiHash=hash('sha512',$header->api_key.$requestId.$apiToken);
        $requestHeader = ([
            'Content-Type'=>$header->Content_Type,
            'API_KEY'=>$header->api_key,
            'MERCHANT_ID'=>$header->merchant_id,
            'REQUEST_ID'=>$requestId,
            'AUTHORIZATION'=> "remitaConsumerKey=$header->api_key,remitaConsumerToken=$apiHash",
            'url'=>$header->url,
            ]);

        $customerDetail = [
            "authorisationCode"=> "2633564",
            "firstName"=>"DAMILOLA",
            "lastName"=> "ADEDIPE",
            "middleName"=> "OMOTAYO",
            "accountNumber"=> "0043286577",
            "bankCode"=>"044",
            "bvn"=> "22246788263",
            "authorisationChannel"=> "USSD"
        ];
       
        try{
            $response = json_decode(Http::withHeaders($requestHeader)->timeout(120)->post($requestHeader['url'],($customerDetail)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            Log::channel('http')->info(json_encode($requestHeader));
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

            return $response;
        
         
     }



     public function getaccount(Request $request) {

        $accountnumber = $request->Account ;
        $bankcode = $request->bankCode;

        $val = new RITSController();
        return $val->accountEnquiry($accountnumber, $bankcode);

        
     }

     public function lkup(Request $request){
        $service_type= $request->service_type;
        $customerId = $request->customerId;

        $service = new CapricornService;
        return $service->lookup($service_type, $customerId);
     }
    
    
     public function webhk(Request $request){
        $transactionId =  $request->transactionId;        
        $webhk = new WebhookService("531908874", ($request));
        $response = $webhk->sendNotification($transactionId);
        echo "here";
        return response()->json($response);
     }



}

