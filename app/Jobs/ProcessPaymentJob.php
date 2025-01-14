<?php

namespace App\Jobs;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BillerVendor;
use App\Models\BillerService;
use Illuminate\Bus\Queueable;
use App\Models\MultichoiceUser;
use App\Services\WebhookService;
use App\Services\CapricornService;
use Illuminate\Support\Facades\DB;
use App\Helpers\CreditSwitchHelper;
use Illuminate\Support\Facades\Log;
use App\Services\CreditSwitchService;
use Illuminate\Queue\SerializesModels;
use App\Models\CreditswitchServiceCode;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\CreditSwitchCustomerElectDetails;
use App\Http\Controllers\Vendors\creditSwitchController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $meta;
    /**
     * Create a new job instance.
     */
    public function __construct($meta)
    {
        $this->meta = $meta;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //initialize the parameters by selecting the item on the database
        if ($val = BillerService::where('transactionId', $this->meta->transactionId)->first()) {
        } else {
            Log::info("could not find item in the database ");
        }

        switch ($this->meta->preferredVendor) {
            case 'capricorn':
                Log::info("Capricorn selected ... calling capricorn service");
                $capricorn = new CapricornService;
                switch ($this->meta->category) {
                    case 'airtime':
                        if ($response = $capricorn->purchaseAirtime($this->meta)) {
                            //update the biller_service table 
                            // anlyse the response 
                            $response = json_decode($response);
                            Log::info("Capricorn response " . json_encode($response));
                            $val->update([
                                "vendingStatus" => $response->status
                            ]);
                            $this->meta->vendingStatus = $response->status;
                            $this->meta->payload = $response;
                            //call webhook 





                        } else {
                            Log::info('unable to process transaction ' . $this->meta->customerId);
                            throw new \Exception("Unable to process transaction ");
                        }
                        break;
                    case 'data':
                        if ($response = $capricorn->purchaseData($this->meta)) {
                            //update the biller_service table 
                            // anlyse the response 
                            $response = json_decode($response);
                            Log::info("Capricorn response " . json_encode($response));
                            $val->update([
                                "vendingStatus" => $response->status
                            ]);
                        } else {
                            Log::info('unable to process transaction ' . $this->meta->customerId);
                            throw new \Exception("Unable to process transaction ");
                        }
                        break;
                    case 'Internet':
                        break;
                    case 'electricity':
                        break;
                    case 'CableTv':
                        break;
                    case 'bulk';
                        break;
                    case 'insurance';
                        break;
                    case 'gaming';
                        break;
                    case 'education';
                        break;
                    default;
                }


                break;

                //credit switch
            case 'creditswitch':
                Log::info("Credit Switch selected ... calling credit switch service");

                $creditSwitch = new CreditSwitchService;
                //initializing variables 
                $cs = DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();

                switch ($this->meta->category) {
                    case 'airtime':
                        $service = CreditswitchServiceCode::where('category', 'airtime')->where('service_type', ucwords($this->meta->biller))->first();
                        $currentDate = Carbon::now();
                        $kay = [
                            "loginId" => decrypt($cs->loginId),
                            "requestId" => strval($this->meta->transactionId),
                            "serviceId" => $service->service_code,
                            "requestAmount" => $this->meta->amount,
                            "privateKey" => decrypt($cs->privateKey),
                            "recipient" => $this->meta->customerId
                        ];
                        //Log::info($kay);
                        $checksum = CreditSwitchHelper::checkAirtimeData($kay);
                        $data = [
                            "loginId" => decrypt($cs->loginId),
                            "key" => decrypt($cs->publicKey),
                            "requestId" => strval($this->meta->transactionId),
                            "serviceId" => $service->service_code,
                            "amount" => $this->meta->amount,
                            "recipient" => $this->meta->customerId,
                            "date" => $currentDate->format('j-M-Y H:i'),
                            "checksum" => $checksum
                        ];

                        if ($response = $creditSwitch->makeRequest('Post', 'mvend', $data)) {

                            $response = json_decode(json_encode($response));
                            Log::info("Credit Switch response " . json_encode($response));
                            if (!property_exists($response, 'error')) {
                                $val->update([
                                    "vendingStatus" => $response->statusDescription
                                ]);
                                //send webhook and update table
                                $webhook = new WebhookService($this->meta->merchantCode, $this->meta);
                                $webhook->sendNotification($this->meta->transactionId);
                            } else {
                                $val->update([
                                    "vendingStatus" => $response->error->statusCode
                                ]);
                            }
                        } else {
                            Log::info('unable to process transaction ' . $this->meta->customerId);
                            throw new \Exception("Unable to process transaction ");
                        }
                        break;
                    case 'data':

                        $service = CreditswitchServiceCode::where('category', 'data')->where('service_type', ucwords($this->meta->biller))->first();

                        $currentDate = Carbon::now();
                        $kay = [
                            "loginId" => decrypt($cs->loginId),
                            "requestId" => strval($this->meta->transactionId),
                            "serviceId" => $service->service_code,
                            "requestAmount" => $this->meta->amount,
                            "privateKey" => decrypt($cs->privateKey),
                            "recipient" => $this->meta->customerId
                        ];
                        Log::info($kay);
                        $checksum = CreditSwitchHelper::checkAirtimeData($kay);
                        $data = [
                            "loginId" => decrypt($cs->loginId),
                            "key" => decrypt($cs->publicKey),
                            "requestId" => strval($this->meta->transactionId),
                            "serviceId" => $service->service_code,
                            "amount" => $this->meta->amount,
                            "recipient" => $this->meta->customerId,
                            "date" => $currentDate->format('j-M-Y H:i'),
                            "productId" => $this->meta->subscription,
                            "checksum" => $checksum
                        ];

                        if ($response = $creditSwitch->makeRequest('Post', 'dvend', $data)) {

                            $response = json_decode(json_encode($response));
                            Log::info("Credit Switch response " . json_encode($response));
                            if (!property_exists($response, 'error')) {
                                $val->update([
                                    "vendingStatus" => $response->statusDescription
                                ]);
                                //send webhook and update table
                                $webhook = new WebhookService($this->meta->merchantCode, $this->meta);
                                $webhook->sendNotification($this->meta->transactionId);
                            } else {
                                $val->update([
                                    "vendingStatus" => $response->error->statusCode
                                ]);
                            }
                        } else {
                            Log::info('unable to process transaction ' . $this->meta->customerId);
                            throw new \Exception("Unable to process transaction ");
                        }

                        break;
                    case 'Internet':
                        break;

                    case 'electricity':
                        log::info('creditswitch ...electricity selected');
                        $service = CreditswitchServiceCode::where('category', 'electricity')->where('service_type', ucwords($this->meta->biller))->first();
                        $currentDate = Carbon::now();

                        $kay = [
                            "loginId" => decrypt($cs->loginId),
                            "serviceId" => $service->service_code,
                            "requestId" => strval($this->meta->transactionId),
                            "amount" => $this->meta->amount,
                            "privateKey" => decrypt($cs->privateKey),
                            "customerCode" => $this->meta->customerId,
                            "customerAccountId" => $this->meta->customerId
                        ];
                        $checksum = CreditSwitchHelper::checkElectricVend($kay);

                        //check if you have customer's details
                        try {
                            $customerDetails = CreditSwitchCustomerElectDetails::where('customerAccountId', $this->meta->customerId)->firstOrFail();
                        } catch (ModelNotFoundException) {
                            //i dont have the metr number call credit Switch 
                            $cd = $this->evalidate($kay);
                            json_encode($cd);
                            if (false)
                            //if(property_exists($cd,'statusDescription') && $cd->statusDescription == "successful")
                            {
                                //set parameters and update table 
                                log::info("obtained electricity information from the validate function" . (json_encode($cd)));
                                $customerDetails = [
                                    "customerAccountId" => $cd->details->accountId,
                                    "customerName" => $cd->details->name,
                                    "customerAddress" => $cd->details->address
                                ];

                                CreditSwitchCustomerElectDetails::create($customerDetails);
                                $customerDetails = json_decode(json_encode($customerDetails));
                            } else {
                                //user is john doe and address is john doe avenue 
                                log::info("could not validate user electicity infromation " . (json_encode($cd)));
                                $customerDetails = [
                                    "customerName" => "john doe",
                                    "customerAddress" => "lagos"
                                ];
                                $customerDetails = json_decode(json_encode($customerDetails));
                            }
                        }

                        $data = [
                            "loginId" => decrypt($cs->loginId),
                            "key" => decrypt($cs->publicKey),
                            "serviceId" => $service->service_code,
                            "customerAccountId" => $this->meta->customerId,
                            "requestId" => strval($this->meta->transactionId),
                            "amount" => $this->meta->amount,
                            "customerName" => $customerDetails->customerName,
                            "customerAddress" => $customerDetails->customerAddress,
                            "checksum" => $checksum
                        ];

                        if ($response = $creditSwitch->makeRequest('Post', 'evend', $data)) {
                            $response = json_decode(json_encode($response));
                            Log::info("Credit Switch response " . json_encode($response));
                            if (!property_exists($response, 'error') && $response->statusCode == "00") {
                                $val->update([
                                    "vendingStatus" => json_encode($response->statusDescription),
                                    "service_description" => json_encode($response)
                                ]);
                                //send pin to front end... 

                                //send webhook and update table
                                $webhook = new WebhookService($this->meta->merchantCode, $this->meta);
                                $webhook->sendNotification($this->meta->transactionId);
                            } else {
                                $val->update([
                                    "vendingStatus" => json_encode($response->error->statusCode),
                                    "service_description" => json_encode($response)
                                ]);
                            }
                        }


                        break;
                    case 'CableTv':
                        $service = CreditswitchServiceCode::where('category', 'CableTv')->where('biller', ucwords($this->meta->biller))->first();
                        $currentDate = Carbon::now();

                        $kay = [
                            "loginId" => decrypt($cs->loginId),
                            "transactionRef" => strval($this->meta->transactionId),
                            "requestAmount" => $this->meta->amount,
                            "privateKey" => decrypt($cs->privateKey),
                            "customerCode" => $this->meta->customerId
                        ];
                        $checksum = CreditSwitchHelper::checkDstvVend($kay);

                        $vals = [
                            'customerId' => $this->meta->customerId,
                            "serviceId" => $service->service_code,
                        ];
                        //check store for the name of the user if you have it. 
                        try {
                            $localCsValidatior = MultichoiceUser::where('customerNo', $this->meta->customerId)->firstOrFail();
                            $customerName =  $localCsValidatior->firstname .  $localCsValidatior->lastname;
                        } catch (ModelNotFoundException) {
                            $localCsValidatior = new MultichoiceUser;
                            $cs = DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();
                            $creditSwitchService = new CreditSwitchService;

                            $value = [
                                "loginId" => decrypt($cs->loginId),
                                "privateKey" => decrypt($cs->privateKey),
                                "customerCode" => $vals['customerId']
                            ];
                            $mychecksum = CreditSwitchHelper::checkcableTvValidate($value);
                            $payload = [
                                "loginId" => decrypt($cs->loginId),
                                "serviceId" => $vals['serviceId'],
                                "key" => decrypt($cs->publicKey),
                                "checksum" => $mychecksum,
                                "customerNo" => $vals['customerId']
                            ];

                            $csvalidator = $creditSwitchService->makeRequest('Post', 'cabletv/multichoice/validate', $payload);
                            $csvalidator = json_decode(json_encode($csvalidator));

                            if (property_exists($csvalidator, 'statusCode') && $csvalidator->statusCode == "00") {
                                $customerName = $csvalidator->statusDescription->firstname . $csvalidator->statusDescription->lastname;
                                $store = [
                                    'customerNo' => $csvalidator->statusDescription->customerNo,
                                    'firstname' => $csvalidator->statusDescription->firstname,
                                    'lastname' => $csvalidator->statusDescription->lastname,
                                ];
                                $localCsValidatior->save($store);
                            } else {
                                $customerName = "John Doe";
                            }
                        }
                        //check for addon codes
                        if (property_exists($this->meta, 'addon_code') && $this->meta->addon_code !== null && $this->meta->addon_code !== "") {
                            $productsCodes = [
                                $this->meta->subscription,
                                $this->meta->addon_code
                            ];
                        } else {
                            $productsCodes = [
                                $this->meta->subscription
                            ];
                        }

                        $data = [
                            "loginId" => decrypt($cs->loginId),
                            "key" => decrypt($cs->publicKey),
                            "transactionRef" => strval($this->meta->transactionId),
                            "serviceId" => $service->service_code,
                            "customerNo" => $this->meta->customerId,
                            "amount" => $this->meta->amount,
                            "customerName" => $customerName,
                            "productsCodes" => $productsCodes,
                            "invoicePeriod" => $this->meta->subscriptionMonth,
                            // "date" => $currentDate->format('j-M-Y H:i'),
                            "checksum" => $checksum
                        ];

                        if ($response = $creditSwitch->makeRequest('Post', 'cabletv/multichoice/vend', $data)) {
                            $response = json_decode(json_encode($response));
                            Log::info("Credit Switch response " . json_encode($response));
                            if (!property_exists($response, 'error')) {
                                $val->update([
                                    "vendingStatus" => $response->statusDescription->message
                                ]);
                            } else {
                                $val->update([
                                    "vendingStatus" => $response->error->statusCode
                                ]);
                            }
                        }

                        break;
                    case 'bulk':
                        break;
                    case 'insurance':
                        break;
                    case 'gaming':
                        break;
                    case 'education':
                        break;
                    default;
                }

                break;
            default;
        }
        //obtain the preferred vendor
        //update the data base 
        //get the api details and call the service for processing 
        //update transaction status

    }

    public function evalidate($values)
    {
        $cs = DB::table('create_creditswitch_api_keys_tables')->where('service_name', 'Credit Switch Api')->first();
        $service = CreditswitchServiceCode::where('category', 'electricity')->where('service_type', ucwords($this->meta->biller))->first();
        log::info(json_encode($values));
        $creditSwitchService = new CreditSwitchService;

        $csval = [
            "loginId" => decrypt($cs->loginId),
            "serviceId" => $service->service_code,
            "privateKey" => decrypt($cs->privateKey),
            "customerAccountId" => $this->meta->customerId
        ];

        $checksum = CreditSwitchHelper::checkElectricValidate($csval);

        $param = [
            "loginId" => decrypt($cs->loginId),
            "serviceId" => $service->service_code,
            "customerAccountId" => $this->meta->customerId,
            "key" => decrypt($cs->privateKey),
            "checksum" => $checksum
        ];
        $csvalidator = $creditSwitchService->makeRequest('Post', 'evalidate', $param);
        $csvalidator = json_decode(json_encode($csvalidator));
        log::info(json_encode($csvalidator));

        return $csvalidator;
    }
}
