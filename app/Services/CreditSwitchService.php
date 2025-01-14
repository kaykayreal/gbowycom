<?php
namespace App\Services;
use GuzzleHttp\Client;
use App\Models\BillerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CreditswitchServiceCode;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreditSwitchService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = DB::table('create_creditswitch_api_keys_tables')
                          ->where('service_name', 'Credit Switch Api')
                          ->first();

        $this->client = new Client([
            'base_uri' => decrypt($this->apiKey->baseUrl),
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function makeRequest($method, $endpoint, $data = [])
    {
        try {
            $response = $this->client->request($method, $endpoint, [
                'json' => $data
            ]);

            $responseBody = json_decode($response->getBody(), true);
            Log::info('Credit Switch Response:', [
                'method' => $method,
                'endpoint' => $endpoint,
                'response' => $responseBody
            ]);

            return $responseBody;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $errorResponse = json_decode($e->getResponse()->getBody(), true);
                Log::error('Credit Switch Error Response:', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'error' => $errorResponse
                ]);

                return false;
            }

            Log::error('Credit Switch Request Failed:', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    public function checkCustomerElectDetails($values){
        
    }

    public function requery($transactionId){
        
        try{
            $transactionDetails = BillerService::where("transactionId", $transactionId)->firstOrFail();
        }catch(ModelNotFoundException){
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        $serviceId = CreditswitchServiceCode::where("service_type", $transactionDetails->biller)->first()->service_code;
        $cs = DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();
        
        $data=[
            "loginId" =>decrypt($cs->loginId),
            "key"=>decrypt($cs->privateKey),
            "requestId"=>strval($transactionId),
            "serviceId"=>$serviceId
        ];        

        if($response = $this->makeRequest('Get','requery',$data)){

            $response = json_decode(json_encode($response));
            Log::info("Credit Switch response ".json_encode($response)); 
              $transactionDetails->update(["service_description"=>json_encode($response)]);
            return response()->json($response);

    }else{
        return response()->json(['statusCode' => '01',
        "error"=>"unable to verify transaction"], 500);
    }
    }
}


   
    

