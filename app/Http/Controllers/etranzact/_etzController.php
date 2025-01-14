<?php

namespace App\Http\Controllers\etranzact;

use Exception;
use App\Models\EtzCorporate;
use Illuminate\Http\Request;
use App\Models\etranzactDetail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;

class etzController extends Controller
{
    public function getRequestHeader(){
        $etzmod = etranzactDetail::where('id','1') ->first();
        $header = [
            'Authorization'=>$etzmod->token,
            'Content-Type' => 'application/json'

        ];
        //later uptimization will require to use firstorfail
        return ($header);
    }

    public function getToken(){
        $etzmod = etranzactDetail::where('id','1') ->first();
        $url = $etzmod->authURL;

        $customerDetail=[ 
        "username"=>decrypt($etzmod->username),
        "password"=>decrypt($etzmod->password)];

        try{
            $response = json_decode(Http::post($url,($customerDetail)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        if($response->status = 200 && $response->message == "Successfully Authenticated"){
            //           store the token in the database
            $etzmod->token = $response->data->access_token;
            $etzmod->refresh_token = $response->data->refresh_token;
            $etzmod->save();
            return true;
        }
        else{
            Log::error('An unexpected error occurred: Could not generate token' );
             return response()->json(['error' => 'could not generate token'], 500);
        }
        
    }

    public function getEtzToken(){
        $etzmod = etranzactDetail::where('id','1') ->first();
        $url = $etzmod->authURL;

        $customerDetail=[ 
        "username"=>decrypt($etzmod->username),
        "password"=>decrypt($etzmod->password)];

        try{
            $response = json_decode(Http::post($url,($customerDetail)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        if($response->status = 200 && $response->message == "Successfully Authenticated"){
            //           store the token in the database
            $etzmod->token = $response->data->access_token;
            $etzmod->refresh_token = $response->data->refresh_token;
            $etzmod->save();
            return $etzmod->token;
        }
        else{
            Log::error('An unexpected error occurred: Could not generate token' );
             return response()->json(['error' => 'could not generate token'], 500);
        }
        
    }

    public function refreshToken(){
        $etzmod = etranzactDetail::where('id','1') ->first();
        $url = "https://www.etranzactng.net/autolend/auth/refresh";

        $header = [
            'Authorization'=>$etzmod->token
        ];

        try{
            $response = json_decode(Http::withHeaders($header)->post($url,(['refresh_token'=>$etzmod->refresh_token])));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

       

        if($response->status = 200){
            //  store the token in the database 
            //i am not using the refresh token for now because they provided information to always get new token ! 
            $etzmod->token = $response->data->access_token;
            $etzmod->refresh_token = $response->data->refresh_token;
            $etzmod->save();
            
        }
        else{
            Log::error('An unexpected error occurred: Could not generate token' );
             return response()->json(['error' => 'could not generate token'], 500);
        }

    }
    public function getCorporate(){
        //get corporates will load the database with the list of corporates available on eTranzact.
        // this service will be called or refreshed by the super admin as my be necessary 
        // to update the list of corporates
        //log the request, the source of the request and the response provided.

        $queryRef=strval(mt_rand(10000000, 99999999));
        
        $url = "https://www.etranzactng.net/autolend/corporate?businessName=&queryRef=$queryRef";

        $header = $this->getRequestHeader();
        try{
            $response = (json_decode(Http::withHeaders($header)->get($url)));
             // Log the request
            Log::debug('HTTP Request', [
                 'url' => $url,
                    'method' => 'GET',
                    'headers' => $header,
            ]);
    
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        
        
        if(isset($response->status) && $response->status!==null){
            
            //delete every thing in the database first: 
            EtzCorporate::truncate();

            foreach($response->data as $entry ){
                $entry = json_encode($entry);
                $entry = json_decode($entry, true);
               EtzCorporate::create($entry);

               //log the details of the request here
            }
            


        }
        else{
            if($this->getToken()){
                $queryRef=strval(mt_rand(10000000, 99999999));
        $url = "https://www.etranzactng.net/autolend/corporate?businessName=&queryRef=$queryRef";

        $header = $this->getRequestHeader();
        try{
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        } 
            }
            Log::channel('http')->info('HTTP Response:', ['here',$response ]);

            if(isset($response->status) && $response->status!==null){
            
                //delete every thing in the database first: 
                EtzCorporate::truncate();

                foreach($response->data as $entry ){
                    $entry = json_encode($entry);
                    $entry = json_decode($entry, true);
                   EtzCorporate::create($entry);

                   //log the details of the request here 
                }
                

        }
        

     } 
    }

    //this calls the getCorporte method and updates the table...
    public function eTzGetCorporate(){
        if($this->getCorporate()){
            echo true;
        }
        

    }

    public function encript(){
        $username ="1000000871";
        $password = 'JJj4FKuwD8yLJHX889k6VA==';
        $encryptedusername = encrypt($username);
        $encryptedPassword =encrypt($password);
     
        echo ($encryptedPassword." /n  username :". $encryptedusername);
     
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
                     $this->getNewRITSToken(); // Assuming this method updates the token in your class
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


    public function getCorporateEmployees(Request $request){

        $validator = Validator::make($request->all(), [
            'businessName' => ['required', 'string']
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // this returns the details of the corporate as in etz_corporates 
        $queryRef=strval(mt_rand(10000000, 99999999));
        $header = $this->getRequestHeader();
        $businessName = urlencode($request->businessName);
        // $token =  etranzactDetail::where('id','1') ->first();
        // $token = $token->token;
        $url = "https://www.etranzact.net/autolend/corporate?businessName=$businessName&queryRef=$queryRef";
       

        try{
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
                       
        }catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());                         
            return response()->json(['error' => 'HTTP request failed'], 500);
         }catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
             return response()->json(['error' => 'An unexpected error occurred'], 500);
        } 
        //log request in the database
        
        return response()->json($response, 201);
    

    }

    
public function getSalaryInfo($accountNo, $businessId, $bankCode){

    $queryRef=strval(mt_rand(10000000, 99999999));
    $url ="https://www.etranzactng.net/autolend/employee?accountNo=$accountNo&bankCode=$bankCode&businessId=$businessId&queryRef=$queryRef";

    $header = $this->getRequestHeader();
    try{
        $response = (json_decode(Http::withHeaders($header)->get($url)));
        Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        
    }catch (RequestException $e) {
        Log::error('HTTP request failed: ' . $e->getMessage());                         
        return response()->json(['error' => 'HTTP request failed'], 406);
     }catch (\Exception $e) {
        
        Log::error('An unexpected error occurred: ' . $e->getMessage());
         return response()->json(['error' => 'An unexpected error occurred'], 406);
    } 

    if(is_object($response)){

    }
    
}    

}
