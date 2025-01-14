<?php

namespace App\Http\Controllers\remita;

use Exception;
use App\Models\Rits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;

class RITSController extends Controller
{
    public function getnewRITSToken(){
        $ritsDetails = Rits::find(1);

        $username = decrypt($ritsDetails->username);
        $password = decrypt($ritsDetails->password);
        $url = decrypt($ritsDetails->base_url);
        $url = $url."/remita/exapp/api/v1/send/api/uaasvc/uaa/token";
        $payload=[
            'username'=>$username,
            'password'=>$password
        ];

        try {
            $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                    if ($exception instanceof RequestException ) {
                    // Retry the request with the new token
                     
                        return true;
                    }
                     // Update the token
                  return false;
                })
                ->post($url, $payload);
        } catch (Exception $e) {
            // Handle the exception here
            // For example, you can log the error message or return a specific response
            log::error(['error' => $e->getMessage()],['reason'=> 'Error on Remita Account Enquiry']);
            return response()->json(['error' => $e->getMessage()."url : $url"], 500);
        }

        // $response = Http::retry(3, 100, function (Exception $exception, PendingRequest $request) {
        //     return $exception instanceof ConnectionException;
        // })->post($url,$payload);

        $response=$response->json();
        $jsonresponse = json_decode(json_encode($response));

        if(isset($jsonresponse->status) && ($jsonresponse->status == "00")){

           // i have a valid response , update data base 
           Rits::where('id', 1)->update([
            "api_token"=> $jsonresponse->data['0']->accessToken,
            // "api_token"=> $jsonresponse->data['0']->accessToken,
           ]);
          return $jsonresponse->data['0']->accessToken;
        }
        else{
            return false;
        }
        
    }

     public function getRemitaRequestHeaders(){
        
        $ritsDetails = Rits::find(1);
        $url = decrypt($ritsDetails->base_url);
        $token = $ritsDetails->api_token;
      
        $headers = [
                      'Authorization' => "$token",
                      'Content-Type' => 'application/json'
                ];

        $requestheader = [
            "header"=>$headers,
            'end_point'=>$url
        ];
 
        return $requestheader;
        
 }

 public function getRitsToken(){
    return Rits::find(1)->api_token;
 }

 public function accountEnquiry($sourceAccount, $sourceBankCode)
 {
     $requestHeader = $this->getRemitaRequestHeaders();
     $url = $requestHeader['end_point'];
     $url = $url . "/remita/exapp/api/v1/send/api/rpgsvc/v3/rpg/account/lookup";
     $param = array("sourceAccount" => $sourceAccount, "sourceBankCode" => $sourceBankCode);
 
     try {
         $response = Http::withToken($this->getRitsToken())
             ->withHeaders([
                 'Content-Type' => 'application/json',
             ])
             ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                 if ($exception instanceof RequestException ) {
                 // Retry the request with the new token
                  $request->withToken($this->getNewRITSToken());
                     return true;
                 }
                  // Update the token
               return false;
             })
             ->post($url, $param);
     } catch (Exception $e) {
         // Handle the exception here
         // For example, you can log the error message or return a specific response
         log::error(['error' => $e->getMessage()],['reason'=> 'Error on Remita Account Enquiry']);
         return response()->json(['error' => $e->getMessage()."url : $url"], 500);
     }
 
     // Process the response here
     return $response->json();
 }
    

    public function singlePayment($paymentDetails){

        $requestHeader = $this->getRemitaRequestHeaders();
        $url = $requestHeader['end_point'];
        $url = $url . "/rpgsvc/v3/rpg/single/payment";
        $param = json_decode(json_encode($paymentDetails));
    
        try {
            $response = Http::withToken($this->getRitsToken())
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                    if ($exception instanceof RequestException ) {
                    // Retry the request with the new token
                     $request->withToken($this->getRitsToken());
                        return true;
                    }
                     // Update the token
                     $this->getnewRITSToken(); // Assuming this method updates the token in your class
                    return false;
                })
                ->post($url, $param);
        } catch (Exception $e) {
            // Handle the exception here
            // For example, you can log the error message or return a specific response
            log::error(['error' => $e->getMessage()],['Transfer failed'=> 'Error on Remita Account Enquiry']);
            return response()->json(['error' => $e->getMessage()]);
        }
    
        // Process the response here
        return $response->json();
   
        
    }

}


