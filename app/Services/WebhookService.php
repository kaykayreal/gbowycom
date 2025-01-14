<?php

namespace App\Services;

use App\Http\Controllers\Vendors\creditSwitchController;
use App\Models\BillerService;
use Exception;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\BillerVendor;
use App\Models\WebhookNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\RequestException;
use PhpParser\JsonDecoder;

class WebhookService{

    protected $merchant;
    protected $transaction;
    protected $payload;
    

    public function __construct($merchant,  $meta){  
        
        $this->merchant = User::where('merchant_code',$merchant)->first(); 
    
        $this->payload = $meta;
    }
    
    public function sendNotification($transactionId)
    {
        // Requery transaction depending on the vendor
        $webhook = new WebhookNotification;

        log::info(json_encode($this->merchant));
    
        // Ensure values have the correct field names
       
        $webhook->user_id = $this->merchant->id;
        $webhook->event_type = "Bill Payment Notification";
        $webhook->target_url = $this->merchant->webhook;
        $webhook->payload = json_decode(json_encode($this->payload, true));
        $webhook->attempts = 1;
    
            $webhook->save();
    
        switch ($this->payload->preferredVendor) {
            case 'capricorn':
                // Add any logic for Capricorn if necessary
                break;
    
            case 'creditswitch':
                $requeryService = new CreditSwitchService();
                // sleep(2);
                $requeryValue = $requeryService->requery(strval($transactionId));
                $this->payload->requeryValue = $requeryValue;
    
                try {
                    // Convert response to object format directly
                    $response = Http::timeout(60)->post($this->merchant->webhook, $this->payload)->object();
                    log::info(json_encode($response). $this->merchant->webhook . $transactionId. "- transaction id".json_encode($this->payload));
                    // Ensure response structure is as expected before update
                    if (property_exists($response,"response_code")) {
                        $webhook->update([
                            "response_code" => $response->response_code,
                            "response_body" => (json_encode($response)),
                            "status" => $response->status
                        ]);
                        echo"here too";
                    } else {
                        Log::warning("Unexpected response format from webhook for merchant ID: " . $this->merchant->id);
                    }
    
                } catch (Exception $e) {
                    Log::error('Error sending notification: ' . $e->getMessage());
                }
                break;
    
            default:
                Log::info("No matching vendor case found for preferredVendor: " . $this->payload->preferredVendor);
                break;
        }
    }

    

}
