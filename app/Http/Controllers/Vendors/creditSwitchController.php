<?php

namespace App\Http\Controllers\Vendors;

use Exception;
use Carbon\Carbon;
use App\Models\VendorData;
use Illuminate\Http\Request;
use App\Models\BillerService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Helpers\CreditSwitchHelper;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\CreditSwitchService;
use App\Models\CreditswitchServiceCode;
use Illuminate\Support\Facades\Validator;
use CreditSwitchHelper as GlobalCreditSwitchHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class creditSwitchController extends Controller
{
    protected $creditSwitchService;
    protected $cs;


    public function __construct(CreditSwitchService $creditSwitchService)
    {
        $this->creditSwitchService = $creditSwitchService;
        $this->cs = DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();
    }

    public function vendAirtime(Request $request)
    {
        //get wervice id
        $service = CreditswitchServiceCode::where('category', $request->category)->where('biller', ucwords($request->biller))->first();

        $currentDate = Carbon::now();
        $val = [
            "loginId" => decrypt($this->cs->loginId),
            "requestId" => $request->transactionId,
            "serviceId" => $service->service_code,
            "requestAmount" => $request->amount,
            "privateKey" => decrypt($this->cs->privateKey),
            "recipient" => $request->customerId
        ];

        $checksum = CreditSwitchHelper::checkAirtimeData($val);
        $data = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->publicKey),
            "requestId" => $request->transactionId,
            "serviceId" => $service->service_code,
            "amount" => $request->amount,
            "recipient" => $request->customerId,
            "date" => $currentDate->format('j-M-Y H:i'),
            "checksum" => $checksum
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'mvend', $data);
        return response()->json($response);
    }

    public function vendData(Request $request)
    {
        //to revisit datavend and other services later !
        $service = CreditswitchServiceCode::where('category', $request->category)->where('biller', ucwords($request->biller))->first();

        $currentDate = Carbon::now();
        //$concatString = $data['loginId']."|".$data['privateKey']."|".$data['requestId'];
        $val = [
            "loginId" => decrypt($this->cs->loginId),
            "requestId" => $request->transactionId,
            "privateKey" => decrypt($this->cs->privateKey),
        ];

        $checksum = CreditSwitchHelper::checkSendSms($val);
        //{"loginId":"1234","key":"f7a2b42…", "senderId":"TEST-NG", "msisdn":"09062120000", "messageBody":"Keep calm and test", "transactionRef":"008k97658891", “checksum”:”JDhJk…”}

        $data = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->publicKey),
            "senderId" => "TEST-NG",
            "msisdn" => $request->msisdn,
            "messageBody" => $request->message,
            "transactionRef" => $request->transactionId,
            "checksum" => $checksum
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'send', $data);
        return response()->json($response);
    }

    public function pushSMS(Request $request)
    {

        $val = [
            "loginId" => decrypt($this->cs->loginId),
            "requestId" => $request->transactionId,
            "privateKey" => decrypt($this->cs->privateKey),
        ];

        $checksum = CreditSwitchHelper::checkSendSms($val);
        //{"loginId":"1234","key":"f7a2b42…", "senderId":"TEST-NG", "msisdn":"09062120000", "messageBody":"Keep calm and test", "transactionRef":"008k97658891", “checksum”:”JDhJk…”}

        $data = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->publicKey),
            "senderId" => "TEST-NG",
            "msisdn" => $request->msisdn,
            "messageBody" => $request->message,
            "transactionRef" => $request->transactionId,
            "checksum" => $checksum
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'sendsms', $data);
        return response()->json($response);
    }

    public function validateCustomer(Request $request) {}

    public function dataPlans(Request $request)
    {

        $service = CreditswitchServiceCode::where('category', 'data')->where('service_type', '9mobile')->first()->service_code;


        $payload = [
            "loginId" => decrypt($this->cs->loginId),
            "serviceId" => $service,
            "key" => decrypt($this->cs->publicKey),
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'mdataplans', $payload);
        $response = json_decode(json_encode($response));
        $vendor_data = new VendorData;
        foreach ($response->dataPlan as $bundle) {

            $data = [
                'name' => $bundle->databundle . " for " . $bundle->validity,
                'allowance' => $bundle->databundle,
                'price' => $bundle->amount,
                'validity' => $bundle->validity,
                'data_code' => $bundle->productId,
                'biller_id' => "9",
                'vendor_id' => "2",
            ];
            $vendor_data->update($data);
        }
        return "processed";
    }

    public function cablePlans(Request $request)
    {
        try {
            $payload = [
                "loginId" => decrypt($this->cs->loginId),
                "serviceId" => $request->serviceId,
                "key" => decrypt($this->cs->publicKey),
            ];

            $response = $this->creditSwitchService->makeRequest('Post', 'cabletv/multichoice/fetchproducts', $payload);
            $response = json_decode(json_encode($response));

            if (!isset($response->statusDescription->items) || !is_array($response->statusDescription->items)) {
                Log::error('Invalid response format for cable plans', ['response' => json_encode($response)]);
                return response()->json(['error' => 'Invalid response from provider'], 500);
            }

            $plans = [];
            foreach ($response->statusDescription->items as $item) {
                $price = 0;

                if (!empty($item->availablePricingOptions) && is_array($item->availablePricingOptions)) {
                    $firstOption = $item->availablePricingOptions[0] ?? null;
                    $price = isset($firstOption->price) ? (string) $firstOption->price : "0";
                } elseif (isset($item->availablePricingOptions->description)) {
                    $price = (string) $item->availablePricingOptions->description;
                }

                $plans[] = [
                    'code' => (string) $item->code,
                    'name' => (string) $item->name,
                    'price' => $price
                ];
            }

            return response()->json(['plans' => $plans], 200);
        } catch (Exception $e) {
            Log::error('Error fetching cable plans', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


    public function cableStartimes(Request $request)
    {

        $payload = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->publicKey),
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'startimes/fetchProductList', $payload);
        $response = json_decode(json_encode($response));

        foreach ($response->statusDescription->items as $item) {
            echo $item->code . ", " . $item->name . ", ";

            if (is_array($item->availablePricingOptions) && !empty($item->availablePricingOptions)) {
                // Access the first element in the array
                $firstOption = $item->availablePricingOptions[0];
                echo ", " . $firstOption->price;
                echo PHP_EOL;
            } elseif (isset($item->availablePricingOptions->description)) {
                // If it's a single object, just output the description
                echo $item->availablePricingOptions->description;
                echo PHP_EOL;
            } else {
                echo "0"; // Output 0 if neither condition is met
            }
        }
    }


    public function cableAddons(Request $request)
    {


        $payload = [
            "loginId" => decrypt($this->cs->loginId),
            "serviceId" => $request->serviceId,
            "key" => decrypt($this->cs->publicKey),
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'cabletv/multichoice/productaddons', $payload);
        $response = json_decode(json_encode($response));

        return $response;
    }

    public function validateMultiChoiceCustomer(Request $request)
    {

        $val = [
            "loginId" => decrypt($this->cs->loginId),
            "privateKey" => decrypt($this->cs->privateKey),
            "customerCode" => $request->subscription
        ];
        $checksum = CreditSwitchHelper::checkcableTvValidate($val);
        $payload = [
            "loginId" => decrypt($this->cs->loginId),
            "serviceId" => $request->serviceId,
            "key" => decrypt($this->cs->publicKey),
            "checksum" => $checksum,
            "customerNo" => $request->subscription
        ];

        $response = $this->creditSwitchService->makeRequest('Post', 'cabletv/multichoice/validate', $payload);
        $response = json_decode(json_encode($response));

        return $response;
    }

    public function txnRequery(Request $request)
    {

        $transactionId = $request->transactionId;
        try {
            $transactionDetails = BillerService::where("transactionId", $transactionId)->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        $serviceId = CreditswitchServiceCode::where("service_type", $transactionDetails->biller)->first()->service_code;

        $data = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->privateKey),
            "requestId" => strval($transactionId),
            "serviceId" => $serviceId
        ];

        if ($response = $this->creditSwitchService->makeRequest('Get', 'requery', $data)) {

            $response = json_decode(json_encode($response));
            Log::info("Credit Switch response " . json_encode($response));
            return response()->json($response);
        } else {
            return response()->json([
                'statusCode' => '01',
                "error" => "unable to verify transaction"
            ], 500);
        }
    }

    public function requery($transactionId)
    {


        try {
            $transactionDetails = BillerService::where("transactionId", $transactionId)->firstOrFail();
        } catch (ModelNotFoundException) {
            return response()->json(['error' => 'Transaction not found'], 404);
        }
        $serviceId = CreditswitchServiceCode::where("service_type", $transactionDetails->biller)->first()->service_code;

        $data = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->privateKey),
            "requestId" => strval($transactionId),
            "serviceId" => $serviceId
        ];

        if ($response = $this->creditSwitchService->makeRequest('Get', 'requery', $data)) {

            $response = json_decode(json_encode($response));
            Log::info("Credit Switch response " . json_encode($response));
            return response()->json($response);
        } else {
            return response()->json([
                'statusCode' => '01',
                "error" => "unable to verify transaction"
            ], 500);
        }
    }

    public function getBalance()
    {
        $val = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->privateKey),
        ];

        $concatString = $val['loginId'] . "|" . $val['key'];
        $checksum = base64_encode(password_hash($concatString, PASSWORD_DEFAULT));

        $data = [
            "loginId" => $val['loginId'],
            "key" => decrypt($this->cs->publicKey),
            "checksum" => $checksum
        ];
        $response = $this->creditSwitchService->makeRequest('Post', 'mdetails', $data);
        return response()->json($response);
    }

    public function getInsurancePackages(Request $request)
    {
        $data = [
            "loginId" => decrypt($this->cs->loginId),
            "key" => decrypt($this->cs->publicKey),
        ];
        $response = $this->creditSwitchService->makeRequest('get', 'insurance/packages', $data);
        $stored = CreditSwitchHelper::storeInsurancePackages(json_encode($response));
        return $stored;
    }

    public function processInsurancePackages(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'serviceId' => ['required', 'string', 'regex:/^INS\d+$/'],
            'insured_name' => 'required|string|max:255',
            'insurance_type' => ['required', Rule::in(['PRIVATE_MOTOR', 'COMMERCIAL', 'HEALTH', 'LIFE'])],
            'chassis_number' => 'required|string|min:17|max:17',
            'engine_number' => 'required|string|max:50',
            'plate_number' => 'required|string|max:10',
            'customerId' => ['required', 'string', 'regex:/^\d{11}$/'],
            'amount' => 'required|numeric|min:1',
            'vehicle_make' => 'required|string|max:50',
            'vehicle_model' => 'required|string|max:50',
            'vehicle_color' => 'required|string|max:50',
            'year_of_make' => 'required|integer|min:1900|max:' . date('Y'),
            'email' => 'required|email|max:255',
            'contact_address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [
            'loginId' => decrypt($this->cs->loginId),
            'key' => decrypt($this->cs->publicKey),
            'requestId' => mt_rand(10000000000, 99999999999),
            'serviceId' => $request->serviceId,
            'insured_name' => $request->insured_name,
            'insurance_type' => $request->insurance_type,
            'chassis_number' => $request->chassis_number,
            'engine_number' => $request->engine_number,
            'plate_number' => $request->plate_number,
            'customerId' => $request->customerId,
            'amount' => $request->amount,
            'vehicle_make' => $request->vehicle_make,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_color' => $request->vehicle_color,
            'year_of_make' => $request->year_of_make,
            'email' => $request->email,
            'contact_address' => $request->contact_address,

        ];
        $response = $this->creditSwitchService->makeRequest('post', 'insurance/pay', $data);
        return response()->json($response);
    }
}
