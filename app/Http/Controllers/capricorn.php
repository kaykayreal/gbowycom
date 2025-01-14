<?php

namespace App\Http\Controllers;

use App\Models\BillerService;
use App\Models\BillerVendor;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class capricorn extends Controller
{
    public function processFlutter($data){

        //get process settings for platform
       $settings = ($data->data->meta->platformId);
       if(isset($settings)){
        
       if($ps = Platform::find($settings)) {
        //validate xapikey 
        if($ps->x_api_key !== $data->data->meta->x_api_key)
            return response()->json(["Message"=>"invalid Key"], 401);
       }

      }else return response()->json(['error'=>'No Settings Found'],404);            
       //get service provider details from DB

        $vendor = BillerVendor::where('billerVendorName','capricorn')->first();
        //get the service to be processed
       $key = decrypt($vendor->billerVendorKey);
       $agentid = decrypt($vendor->agentId);
       $header = [
        'x-api-key' => $key,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
       ];
      
       $service = $data->data->meta->category;
       $meta = $data->data->meta;

       switch(strtolower($service)){
        case "airtime":
            $url = "https://api.baxibap.com/services/airtime/request";
            $payload=[
                "phone"=>$meta->customerId,
                "amount"=>$meta->realAmount,
                "service_type"=>$meta->biller,
                "plan"=>$meta->plan,
                "agentId"=>$agentid,
                "agentReference"=>$meta->transactionId
            ];

            if($ps->automation == "0"){
                //automation 
            $response = json_decode(Http::withHeaders($header)->post("$url",($payload)));
            if($response -> status == "success" && $response->message == "Successful" ){
                //enter valudes into database
                $values =[
                    'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>"",
                    'subscriptionMonth'=>"",
                    'addon'=>"",
                    'addonMonth'=>"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                    'vendingStatus'=>$response -> status,
                    'vendingRetrials'=>"0",
                    'payload'=>json_encode($response),
                    'plan'=>$meta->plan,  
                    
                ];

                BillerService::create($values);
                return true;

            }
            }elseif($meta->automation == "1"){
            //automation is via cronjob... dump into table 
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>"",
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"Cron-Job",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan, 
                
            ];

            BillerService::create($values);
            return true;

         }else{
            //automation is manual... dump in manual processing table
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>"",
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"manual",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan,                 
            ];
            BillerService::create($values);
            return true;
        }   
            break;
        case "data":

            $url = "https://api.baxibap.com/services/databundle/request";
            $payload=[
                "phone"=>$meta->customerId,
                "amount"=>$meta->realAmount,
                "service_type"=>$meta->biller,
                "datacode"=> $meta->subscription,
                "agentId"=>$agentid,
                "agentReference"=>$meta->transactionId
            ];

            if($meta->automation == "0"){
                //automation 
            $response = json_decode(Http::withHeaders($header)->post("$url",($payload)));
            if($response -> status == "success" && $response->message == "Successful" ){
                //enter valudes into database
                $values =[
                    'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>$meta->subscription,
                    'subscriptionMonth'=>"",
                    'addon'=>"",
                    'addonMonth'=>"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                    'vendingStatus'=>$response -> status,
                    'vendingRetrials'=>"0",
                    'payload'=>json_encode($response),
                    'plan'=>$meta->plan,  
                    
                ];

                BillerService::create($values);
                return true;

            }
            }elseif($meta->automation == "1"){
            //automation is via cronjob... dump into table 
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>$meta->subscription,
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"Cron-Job",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan, 
                
            ];

            BillerService::create($values);
            return true;

         }else{
            //automation is manual... dump in manual processing table
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>$meta->subscription,
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"manual",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan,                 
            ];
            BillerService::create($values);
            return true;}
            break; 

        case "internet":
            
            $url = "https://api.baxibap.com/services/databundle/request";
            $payload=[
                "phone"=>$meta->customerId,
                "amount"=>$meta->realAmount,
                "service_type"=>$meta->biller,
                "datacode"=> $meta->subscription,
                "agentId"=>$agentid,
                "agentReference"=>$meta->transactionId
            ];

            if($meta->automation == "0"){
                //automation 
            $response = json_decode(Http::withHeaders($header)->post("$url",($payload)));
            if($response -> status == "success" && $response->message == "Successful" ){
                //enter valudes into database
                $values =[
                    'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>$meta->subscription,
                    'subscriptionMonth'=>"",
                    'addon'=>"",
                    'addonMonth'=>"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                    'vendingStatus'=>$response -> status,
                    'vendingRetrials'=>"0",
                    'payload'=>json_encode($response),
                    'plan'=>$meta->plan,  
                    
                ];

                BillerService::create($values);
                return true;

            }
            }elseif($meta->automation == "1"){
            //automation is via cronjob... dump into table 
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>$meta->subscription,
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"Cron-Job",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan, 
                
            ];

            BillerService::create($values);
            return true;

         }else{
            //automation is manual... dump in manual processing table
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>$meta->subscription,
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"manual",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan,                 
            ];
            BillerService::create($values);
            return true;
        }

                break;

        case "electricity":

            $url = "https://api.baxibap.com/services/electricity/request";
            $payload=[

                "service_type"=>$meta->biller,
                "account_number"=>$meta->customerId,
                "amount"=>$meta->realAmount,
                "metadata"=>"",
                "phone" =>$meta->subscription,
                "agentId"=>$agentid,
                "agentReference"=>$meta->transactionId
            ];

            if($meta->automation == "0"){
                //automation 
            $response = json_decode(Http::withHeaders($header)->post("$url",($payload)));
           
            if($response -> status == "success" && $response->message == "Successful" ){
                //enter valudes into database
                $values =[
                    'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>$meta->subscription,
                    'subscriptionMonth'=>"",
                    'addon'=>"",
                    'addonMonth'=>"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                    'vendingStatus'=>$response -> status,
                    'vendingRetrials'=>"0",
                    'payload'=>json_encode($response),
                    'plan'=>$meta->plan,  
                    
                ];

                BillerService::create($values);
                return true;

            }
            }elseif($meta->automation == "1"){
            //automation is via cronjob... dump into table 
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>$meta->subscription,
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"Cron-Job",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan, 
                
            ];

            BillerService::create($values);
            return true;

         }else{
            //automation is manual... dump in manual processing table
            $values =[
                'platformId'=> $settings,        
                'customerId'=>$meta->customerId,
                'transactionId'=>$meta->transactionId,
                'category'=>$meta->category,
                'biller'=>$meta->biller,
                'subscription'=>$meta->subscription,
                'subscriptionMonth'=>"",
                'addon'=>"",
                'addonMonth'=>"",
                'preferredVendor'=>$meta->preferredVendor,
                'email'=>$data->data->customer->email,
                'amount'=>$meta->realAmount,
                'vendingStatus'=>"manual",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan,                 
            ];
            BillerService::create($values);
            return true;
        }


                break;
         case "cable":
           
            $url = "https://api.baxibap.com/services/multichoice/request";

            if(isset($meta->addon)){
            $payload=[

                "smartcard_number"=>$meta->customerId,
                "total_amount"=>$meta->realAmount,
                "product_code"=>$meta->subscription,
                "addon_code"=>isset($meta->addon)?$meta->addon:"",
                "product_monthsPaidFor"=>$meta->subscriptionMonth,
                "addon_monthsPaidFor"=>isset($meta->addonMonth)?$meta->addonMonth:"",
                "service_type"=>$meta->biller,
                "agentId"=>$agentid,
                "agentReference"=>$meta->transactionId
            ];
        } else{
            $payload=[

                "smartcard_number"=>$meta->customerId,
                "total_amount"=>$meta->realAmount,
                "product_code"=>$meta->subscription,
                "product_monthsPaidFor"=>$meta->subscriptionMonth,
                "addon_monthsPaidFor"=>isset($meta->addonMonth)?$meta->addonMonth:"",
                "service_type"=>$meta->biller,
                "agentId"=>$agentid,
                "agentReference"=>$meta->transactionId
            ];

        }

            if($meta->automation == "0"){
                //automation 
            $response = json_decode(Http::withHeaders($header)->post("$url",($payload)));
            if($response -> status == "success" && $response->message == "Successful" ){
                //enter valudes into database
                
                    $values =[
                        'platformId'=> $settings,        
                        'customerId'=>$meta->customerId,
                        'transactionId'=>$meta->transactionId,
                        'category'=>$meta->category,
                        'biller'=>$meta->biller,
                        'subscription'=>$meta->subscription,
                        'subscriptionMonth'=>$meta->subscriptionMonth,
                        'addonMonth'=>isset($meta->addonMonth)?$meta->addonMonth:"",
                        'preferredVendor'=>$meta->preferredVendor,
                        'email'=>$data->data->customer->email,
                        'amount'=>$meta->realAmount,
                        'vendingStatus'=>$response -> status,
                        'vendingRetrials'=>"0",
                        'payload'=>json_encode($response),
                        'plan'=>$meta->plan,  ]; 
                
               

                BillerService::create($values);
                return true;

            }
            else{
                $values =[
                    'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>$meta->subscription,
                    'subscriptionMonth'=>$meta->subscriptionMonth,
                    'addonMonth'=>isset($meta->addonMonth)?$meta->addonMonth:"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                    'vendingStatus'=>$response ->status,
                    'vendingRetrials'=>"0",
                    'payload'=>json_encode($response),
                    'plan'=>isset($meta->plan)? $meta->plan:"NA" ]; 
            
           

            BillerService::create($values);
            return true;

            }
            }elseif($meta->automation == "1"){
            //automation is via cronjob... dump into table 
            $values =[
                'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>$meta->subscription,
                    'subscriptionMonth'=>$meta->subscriptionMonth,
                    'addon'=>isset($meta->addon)?$meta->addon:"",
                    'addonMonth'=>isset($meta->addonMonth)?$meta->addonMonth:"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                'vendingStatus'=>"Cron-Job",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan, 
                
            ];

            BillerService::create($values);
            return true;

         }else{
            //automation is manual... dump in manual processing table
            $values =[
                'platformId'=> $settings,        
                    'customerId'=>$meta->customerId,
                    'transactionId'=>$meta->transactionId,
                    'category'=>$meta->category,
                    'biller'=>$meta->biller,
                    'subscription'=>$meta->subscription,
                    'subscriptionMonth'=>$meta->subscriptionMonth,
                    'addon'=>isset($meta->addon)?$meta->addon:"",
                    'addonMonth'=>isset($meta->addonMonth)?$meta->addonMonth:"",
                    'preferredVendor'=>$meta->preferredVendor,
                    'email'=>$data->data->customer->email,
                    'amount'=>$meta->realAmount,
                'vendingStatus'=>"manual",
                'vendingRetrials'=>"99",
                'payload'=>"",
                'plan'=>$meta->plan,                 
            ];
            BillerService::create($values);
            return true;
        }
                break;
        case "insurance":
                break;  
        case "water":
                break;
        case "gaming":
                break; 
           default;
           return response()->json(["Message"=>"invalid category"], 401);
       }
    }

    
}
