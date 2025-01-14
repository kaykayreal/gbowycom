<?php 

namespace App\Helpers;

use App\Services\CreditSwitchService;
use Illuminate\Support\Facades\DB;

Class CreditSwitchHelper{

    public static function checkAirtimeData($data){
        $concatString = $data['loginId'] ."|" . $data['requestId'] ."|". $data['serviceId']."|".$data['requestAmount']."|".$data['privateKey'] ."|".$data['recipient'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkElectricValidate($data){
        $concatString = $data['loginId']."|".$data['serviceId']."|".$data['privateKey']."|".$data['customerAccountId'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkElectricVend($data){
        $concatString = $data['loginId']."|".$data['serviceId']."|".$data['privateKey']."|".$data['customerAccountId']."|".$data['requestId']."|".$data['amount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkMerchantDetail($data){
        $concatString = $data['loginId']."|".$data['privateKey'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkDeductMobileAirtime($data){
        $concatString = $data['loginId']."|".$data['privateKey']."|".$data['msisdn']."|".$data['amount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkSendSms($data){
        $concatString = $data['loginId']."|".$data['privateKey']."|".$data['requestId'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkcableTvValidate($data){
        $concatString = $data['loginId']."|".$data['privateKey']."|".$data['customerCode'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }
   
    public static function checkStartimesVend($data){
        $concatString = $data['loginId']."|".$data['privateKey']."|".$data['customerCode']."|".$data['fee'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkDstvVend($data){        
        $concatString = $data['loginId']."|".$data['privateKey']."|".$data['customerCode']."|".$data['transactionRef']."|".$data['requestAmount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public static function checkVendLogical($data){
        $concatString = $data['loginId']."|".$data['serviceId']."|".$data['privateKey']."|".$data['requestId']."|".$data['amount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT) ); 
    }

    public function validateMultiChoiceCustomer($vals){             
        $cs =DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();
        $creditSwitchService = new CreditSwitchService;
        
        $val=[
            "loginId" =>decrypt($cs->loginId),
            "privateKey"=>decrypt($cs->privateKey),
            "customerCode"=>$vals['customerId']
        ];
        $checksum = $this->checkcableTvValidate($val);
        $payload = [
            "loginId" =>decrypt($cs->loginId),
            "serviceId"=> $vals['serviceId'],
            "key"=>decrypt($cs->publicKey),
            "checksum"=>$checksum,
            "customerNo"=>$vals['subscription']
        ];
               
       $response = $creditSwitchService->makeRequest('Post','cabletv/multichoice/validate',$payload); 
       $response = json_decode(json_encode($response)); 
   
        return $response;

    }

}