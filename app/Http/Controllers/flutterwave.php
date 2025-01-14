<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;

class flutterwave extends Controller
{
    //this is the function that validates content of the webhook sent to gbowycom
    public function flutterValidate(Request $request){
        
        $flutterKeys = PaymentGateway::where('PayGateName', 'flutterwave')->first(); // Get the Flutterwave keys from the database
        
        $key= (decrypt($flutterKeys->gateKey)); // Dump the Flutterwave keys to the console
        $url = (decrypt($flutterKeys->vurl));
        $url = str_replace(":id",$request->data['id'],$url);
        
        $data = json_decode(Http::withHeaders(["Authorization"=>"$key"])->get("$url"));
        
        if($data->status=="success"){
            // the transaction is successful on flutterwave, 
            //log in database and call service provider
            return $data;
        }else return false;
    }

    //Refunds are not enabled on accounts by default. 
    //You would need to request this feature on your account by sending an email to hi@flutterwavego.com. 
    //This warning also applies to other refund-related features, i.e. 
    //getting the details of a refunded transaction and querying all refunds.

    public function flutterCreateRefund(Request $request){
        
        $flutterKeys = PaymentGateway::where('PayGateName', 'flutterwave')->first(); // Get the Flutterwave keys from the database

        $key= (decrypt($flutterKeys->gateKey)); // Dump the Flutterwave keys to the console
        $url = (decrypt($flutterKeys->vurl));
        $url = str_replace(":id",$request->id,$url);
        $url = str_replace("verify","refund",$url);
        $data = json_decode(Http::withHeaders(["Authorization"=>"$key"])->get("$url"));
        if($data->status=="success"){
            // the transaction is successful on flutterwave, 
            //log in database and call service provider
            return $data;
        }else return false;
    }

    public function flutterCreate($data){
        
        $values = [
            "payGateName"=>"flutterwave",
            "txnRef"=>$data->data->tx_ref,
            "amount"=>$data->data->charged_amount,
            "created_at"=>$data->data->created_at,
            "fees"=>$data->data->app_fee,
            "gatewayResponse"=>$data->data->processor_response,
            "gateId"=>1,
            "ipAddress"=>$data->data->ip,
            "status"=>$data->data->status,
            "bank"=>isset($data->data->card->issuer)?$data->data->card->issuer:"null",
            "bin"=>isset($data->data->card->first_6digits)?$data->data->card->first_6digits:"null",
            "brand"=>isset($data->data->card->type)?$data->data->card->type:"null",
            "channel"=>$data->data->payment_type,
            "expMonth"=>isset($data->data->card->expiry)?$data->data->card->expiry:"null",
            "expYear"=>isset($data->data->card->expiry)?$data->data->card->expiry:"null",
            "lastFour"=>isset($data->data->card->last_4digits)?$data->data->card->last_4digits:"null",
            "dump"=>json_encode($data),
            "transactionId"=>$data->data->meta->transactionId
        ];
        
         if(PaymentTransaction::create($values)){
            return true;
         }else return false;
    }

}