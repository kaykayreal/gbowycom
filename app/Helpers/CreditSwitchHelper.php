<?php

namespace App\Helpers;

use App\Http\Controllers\Vendors\creditSwitchController;
use App\Models\creditswitchInsuranceServices;
use App\Services\CreditSwitchService;
use Illuminate\Support\Facades\DB;

class CreditSwitchHelper
{

    public static function checkAirtimeData($data)
    {
        $concatString = $data['loginId'] . "|" . $data['requestId'] . "|" . $data['serviceId'] . "|" . $data['requestAmount'] . "|" . $data['privateKey'] . "|" . $data['recipient'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkElectricValidate($data)
    {
        $concatString = $data['loginId'] . "|" . $data['serviceId'] . "|" . $data['privateKey'] . "|" . $data['customerAccountId'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkElectricVend($data)
    {
        $concatString = $data['loginId'] . "|" . $data['serviceId'] . "|" . $data['privateKey'] . "|" . $data['customerAccountId'] . "|" . $data['requestId'] . "|" . $data['amount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkMerchantDetail($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkDeductMobileAirtime($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'] . "|" . $data['msisdn'] . "|" . $data['amount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkSendSms($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'] . "|" . $data['requestId'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkcableTvValidate($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'] . "|" . $data['customerCode'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkStartimesVend($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'] . "|" . $data['customerCode'] . "|" . $data['fee'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkDstvVend($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'] . "|" . $data['customerCode'] . "|" . $data['transactionRef'] . "|" . $data['requestAmount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function checkVendLogical($data)
    {
        $concatString = $data['loginId'] . "|" . $data['serviceId'] . "|" . $data['privateKey'] . "|" . $data['requestId'] . "|" . $data['amount'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public function validateMultiChoiceCustomer($vals)
    {
        $cs = DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();
        $creditSwitchService = new CreditSwitchService;

        $val = [
            "loginId" => decrypt($cs->loginId),
            "privateKey" => decrypt($cs->privateKey),
            "customerCode" => $vals['customerId']
        ];
        $checksum = $this->checkcableTvValidate($val);
        $payload = [
            "loginId" => decrypt($cs->loginId),
            "serviceId" => $vals['serviceId'],
            "key" => decrypt($cs->publicKey),
            "checksum" => $checksum,
            "customerNo" => $vals['subscription']
        ];

        $response = $creditSwitchService->makeRequest('Post', 'cabletv/multichoice/validate', $payload);
        $response = json_decode(json_encode($response));

        return $response;
    }

    public function checkBalance($data)
    {
        $concatString = $data['loginId'] . "|" . $data['privateKey'];
        return base64_encode(password_hash($concatString, PASSWORD_DEFAULT));
    }

    public static function storeInsurancePackages(string $data)
    {
        // Decode the JSON data into an associative array.
        $decoded = json_decode($data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON provided: " . json_last_error_msg());
        }

        // Check if the expected "data" key exists.
        if (!isset($decoded['data']) || !is_array($decoded['data'])) {
            throw new \Exception("No service data found in the JSON");
        }

        // Loop through each service record.
        foreach ($decoded['data'] as $serviceId => $serviceData) {
            // Ensure the key 'serviceId' exists in the service data.
            if (!isset($serviceData['serviceId'])) {
                continue; // Skip if there's no serviceId.
            }

            // Use updateOrCreate to insert a new record or update an existing one.
            creditswitchInsuranceServices::updateOrCreate(
                ['service_id' => $serviceData['serviceId']], // Condition to check for existing record.
                [
                    'name'           => $serviceData['name'] ?? null,
                    'invoice_period' => $serviceData['invoicePeriod'] ?? null,
                    'product_type'   => $serviceData['productType'] ?? null,
                ]
            );
        }

        return "Services inserted/updated successfully.";
    }
}
