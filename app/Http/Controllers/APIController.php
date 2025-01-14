<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;


class APIController extends Controller
{
    public function flutterWebhook(Request $request)
    {
        $flutterwave = new flutterwave; // Instantiate the Flutterwave class
        

        if ($request->input('status') == "successful" || $request->input('status') == "success") { // Check if the webhook status is successful
           
            // Check the URL and data integrity
            if($data = $flutterwave->flutterValidate($request)){
                //check if transaction ID is in database         
                   
               if(PaymentTransaction::where("transactionId",$data->data->meta->transactionId)->first()) {                
                
                abort(401, 'Duplicate Transaction');
               }
               else{                              
                // Update your database here with the transaction details
                   $flutterwave->flutterCreate($data);
               //respond to flutterwave
               //return response()->json(['success' => true], 200);
             } 
                // process transaction
              //get service provider and process with   
              switch ($data->data->meta->preferredVendor)
              {
                case "capricorn":
                    $serviceProvider = new capricorn;
                    break;
                    case "doyaki":
                        $serviceProvider = new doyaki;
                    break;
                    case "buypower":
                        $serviceProvider = new buypower;
                        break;
                        default;
                    
              }
               if($serviceProvider->processFlutter($data)){
                return response()->json(['success' => true], 200);
               }else{
                // insert in the failed transactions table 
                echo "Transaction failed";

               }
            }
           
        }
    }
}
