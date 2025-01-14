<?php

namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;

class GbowyNetWalletService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = DB::table('payment_gateways')
                          ->where('id', '2')
                          ->first();

        
    }

    public function verifyTranssaction($transactionId)
    {
        $payload =[
            "transaction_Ref" => $transactionId
        ];
        try {
            $response = Http::withHeaders([
                "Content-Type" => "application/json",
                "Authorization" => "Bearer ".$this->apiKey->gateKey         
            ])->post($this->apiKey->url, json_decode(json_encode($payload)));

            // Log the response and update the database
           //Log::info($response->json());
                            
            // Check if response status code is not successful
            if ($response->status() !== 200) {
                Log::info("transaction request failed" . $response->body());
                return [
                    'status' => 404,
                    'message' => 'Transaction request failed',
                ];
            }

            return [
                    "status" =>200,
                    "data"=>$response->body()
            ];

        } catch (\Exception $e) {
            Log::error('transaction validation HTTP request failed: ' . $e->getMessage());
           return [
            "status"=> 500,
            "data"=>"Unable to validate transaction"        
        ];
        }
    
        
        

    }



}
