<?php

namespace App\Http\Controllers\etranzact;

use Exception;
use App\Models\User;
use App\Models\RequestLog;
use App\Models\EtzCorporate;
use Illuminate\Http\Request;
use App\Models\etranzactDetail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Controllers\gbowy\gbowyUserController;

class etzController extends Controller
{
    public function getRequestHeader()
    {
        $etzmod = etranzactDetail::where('id', '1')->first();
        $header = [
            'Authorization' => $etzmod->token,
            'Content-Type' => 'application/json'

        ];
        //later uptimization will require to use firstorfail
        return ($header);
    }

    public function getToken()
    {
        $etzmod = etranzactDetail::where('id', '1')->first();
        $url = $etzmod->authURL;

        $customerDetail = [
            "username" => decrypt($etzmod->username),
            "password" => decrypt($etzmod->password)
        ];

        try {
            $response = json_decode(Http::post($url, ($customerDetail)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        if ($response->status = 200 && $response->message == "Successfully Authenticated") {
            //           store the token in the database
            $etzmod->token = $response->data->access_token;
            $etzmod->refresh_token = $response->data->refresh_token;
            $etzmod->save();
            return $etzmod->token;
        } else {
            Log::error('An unexpected error occurred: Could not generate token');
            return response()->json(['error' => 'could not generate token'], 500);
        }
    }

    public function getEtzToken()
    {
        $etzmod = etranzactDetail::where('id', '1')->first();
        $url = $etzmod->authURL;

        $customerDetail = [
            "username" => decrypt($etzmod->username),
            "password" => decrypt($etzmod->password)
        ];

        try {
            $response = json_decode(Http::post($url, ($customerDetail)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        if ($response->status = 200 && $response->message == "Successfully Authenticated") {
            //           store the token in the database
            $etzmod->token = $response->data->access_token;
            $etzmod->refresh_token = $response->data->refresh_token;
            $etzmod->save();
            return $etzmod->token;
        } else {
            Log::error('An unexpected error occurred: Could not generate token');
            return response()->json(['error' => 'could not generate token'], 500);
        }
    }

    public function refreshToken()
    {
        $etzmod = etranzactDetail::where('id', '1')->first();
        $url = "https://www.etranzactng.net/autolend/auth/refresh";

        $header = [
            'Authorization' => $etzmod->token
        ];

        try {
            $response = json_decode(Http::withHeaders($header)->post($url, (['refresh_token' => $etzmod->refresh_token])));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }



        if ($response->status = 200) {
            //  store the token in the database 
            //i am not using the refresh token for now because they provided information to always get new token ! 
            $etzmod->token = $response->data->access_token;
            $etzmod->refresh_token = $response->data->refresh_token;
            $etzmod->save();
        } else {
            Log::error('An unexpected error occurred: Could not generate token');
            return response()->json(['error' => 'could not generate token'], 500);
        }
    }

    public function getCorporate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_code' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        dd($user);
        if ($user->merchant_code !== $request->merchant_code) {
            return response()->json(['errors' => "invalid Merchant_code"], 422);
        }

        //check if merchant code matches token
        $user = User::where('merchant_code', $request->merchant_code)->first();
        if (!$user) {
            return response()->json(['errors' => "Unregistered Merchant_code"], 422);
        }


        //get corporates will load the database with the list of corporates available on eTranzact.
        // this service will be called or refreshed by the super admin as my be necessary 
        // to update the list of corporates
        //log the request, the source of the request and the response provided.

        $queryRef = strval(mt_rand(10000000, 99999999));

        $url = "https://www.etranzactng.net/autolend/corporate?businessName=&queryRef=$queryRef";

        $header = $this->getRequestHeader();
        try {
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            // Log the request
            Log::debug('HTTP Request', [
                'url' => $url,
                'method' => 'GET',
                'headers' => $header,
            ]);

            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }



        if (isset($response->status) && $response->status !== null) {

            //delete every thing in the database first: 
            EtzCorporate::truncate();

            foreach ($response->data as $entry) {
                //log the details of the request here
                $entry = json_encode($entry);
                $entry = json_decode($entry, true);
                EtzCorporate::create($entry);
            }


            $logger = new gbowyUserController;
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            if ($this->getToken()) {
                $queryRef = strval(mt_rand(10000000, 99999999));
                $url = "https://www.etranzactng.net/autolend/corporate?businessName=&queryRef=$queryRef";

                $header = $this->getRequestHeader();
                try {
                    $response = (json_decode(Http::withHeaders($header)->get($url)));
                    Log::channel('http')->info('HTTP Response:', ['response' => $response]);
                } catch (RequestException $e) {
                    Log::error('HTTP request failed: ' . $e->getMessage());
                    return response()->json(['error' => 'HTTP request failed'], 500);
                } catch (\Exception $e) {
                    Log::error('An unexpected error occurred: ' . $e->getMessage());
                    return response()->json(['error' => 'An unexpected error occurred'], 500);
                }
            }


            if (isset($response->status) && $response->status !== null) {

                //delete every thing in the database first: 
                EtzCorporate::truncate();

                foreach ($response->data as $entry) {
                    //log the details of the request here 
                    $entry = json_encode($entry);
                    $entry = json_decode($entry, true);
                    EtzCorporate::create($entry);

                    $logger = new gbowyUserController;
                }

                if ($logRequest = $logger->requestLog($request, $response)) {
                    return response()->json($response, 201);
                } else {
                    return response()->json($response, 200);
                }
            } else {
                return response()->json(['error' => 'An unexpected error occurred'], 404);
            }
        }
    }

    //this calls the getCorporte method and updates the table...
    public function eTzGetCorporate(Request $request)
    {
        if ($this->getCorporate($request)) {
            echo true;
        }
    }

    public function encript()
    {
        $username = "1000000871";
        $password = 'JJj4FKuwD8yLJHX889k6VA==';
        $encryptedusername = encrypt($username);
        $encryptedPassword = encrypt($password);

        echo ($encryptedPassword . " /n  username :" . $encryptedusername);
    }


    public function getCorporateEmployees(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'businessName' => ['required', 'string'],
            'merchant_code' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // this returns the details of the corporate as in etz_corporates 
        $queryRef = strval(mt_rand(10000000, 99999999));
        $header = $this->getRequestHeader();
        $businessName = urlencode($request->businessName);
        // $token =  etranzactDetail::where('id','1') ->first();
        // $token = $token->token;
        $url = "https://www.etranzactng.net/autolend/corporate?businessName=$businessName&queryRef=$queryRef";


        try {
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }



        $logger = new gbowyUserController;
        if (is_object($response) || is_array($response)) {
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Could not obtain requested data"], 404);
        }
    }


    public function getSalaryInfo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'accountNo' => ['required', 'string'],
            'merchant_code' => ['required', 'string'],
            'bankCode' => ['required', 'string'],
            'businessId' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],

        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $queryRef = strval(mt_rand(10000000, 99999999));
        $url = "https://www.etranzactng.net/autolend/employee?accountNo=$request->accountNo&bankCode=$request->bankCode&businessId=$request->businessId&queryRef=$queryRef";

        $header = $this->getRequestHeader();
        try {
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 406);
        } catch (\Exception $e) {

            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 406);
        }

        $logger = new gbowyUserController;
        if (is_object($response) || is_array($response)) {
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Could not obtain requested data"], 404);
        }
    }

    public function getEmployeeCorporate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'merchant_code' => ['required', 'string'],
            'accountNo' => ['required'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $queryRef = strval(mt_rand(10000000, 99999999));
        $header = $this->getRequestHeader();
        $accountNo =  $request->accountNo;
        $url = "https://www.etranzactng.net/autolend/corporate/employee?accountNo=$accountNo&queryRef=$queryRef";


        try {
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        $logger = new gbowyUserController;
        if (is_object($response) || is_array($response)) {
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Could not obtain requested data"], 404);
        }
    }

    public function getLoanDetails(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'merchant_code' => ['required', 'string'],
            'loanRef' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $queryRef = strval(mt_rand(10000000, 99999999));
        $header = $this->getRequestHeader();
        $url = "https://www.etranzactng.net/autolend/loan/details?loanRef=$request->loanRef";


        try {
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        $logger = new gbowyUserController;
        if (is_object($response) || is_array($response)) {
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Could not obtain requested data"], 404);
        }
    }

    public function getLoanHistory(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'accountNo' => ['required', 'string'],
            'bankCode' => ['required', 'string'],
            'startDate' => ['required', 'string'],
            'endDate' => ['required', 'string'],
            'merchant_code' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
            'businessId' => ['required', 'string']

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $queryRef = strval(mt_rand(10000000, 99999999));
        $header = $this->getRequestHeader();
        $url = "https://www.etranzactng.net/autolend/loan/history?accountNo=$request->accountNo&bankCode=$request->bankCode&businessId=$request->businessId&startDate=$request->startDate&endDate=$request->endDate&queryRef=$queryRef";

        try {
            $response = (json_decode(Http::withHeaders($header)->get($url)));
            Log::channel('http')->info('HTTP Response:', ['response' => $response]);
            Log::info('Request Query ref: ' . $queryRef);
        } catch (RequestException $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            return response()->json(['error' => 'HTTP request failed'], 500);
        } catch (\Exception $e) {
            Log::error('An unexpected error occurred: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred'], 500);
        }

        $logger = new gbowyUserController;
        if (is_object($response) || is_array($response)) {
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Could not obtain requested data"], 404);
        }
    }

    public function createRepaymentNew(Request $request)
    {

        $etzmod = etranzactDetail::where('id', '1')->first();
        $token = $etzmod->token;
        $token = str_replace("Bearer", "", $token);
        $clientId = decrypt($etzmod->username);


        $validator = Validator::make($request->all(), [
            'accountNo' => ['required', 'string'],
            'bankCode' => ['required', 'string'],
            'datePaid' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'merchant_code' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
            'businessId' => ['required', 'string'],
            'loanRef' => ['required', 'string'],
            'paymentID' => ['required', 'string']

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $queryRef = strval(mt_rand(10000000, 99999999));
        $header = $this->getRequestHeader();
        $url = "https://www.etranzactng.net/autolend/corporatepay/repayment";

        $payload = [
            "loanRef" => $request->laonRef,
            "paymentID" => $request->paymentID,
            "amount" => $request->amount,
            "datePaid" => $request->datePaid,
            "bankCode" => $request->bankCode,
            "clientId" => $clientId,
            "businessId" => $request->businessId,
            "accountNo" => $request->accountNo,

        ];

        try {

            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                    if ($exception instanceof RequestException) {
                        // Retry the request with the new token
                        $newToken = $this->getToken();
                        $newToken = str_replace("Bearer", "", $newToken);
                        $request->withToken($newToken);
                        return true;
                    }
                })
                ->post($url, $payload);
        } catch (Exception $e) {
            // Handle the exception here
            // $newToken = $this->getNewToken();
            // For example, you can log the error message or return a specific response
            // log::error(['error' => $e->getMessage()],['Message'=> 'Unable to process request']);
            Log::error('Error making HTTP request', [
                'exception' => $e,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => $e->getMessage(), 'Message' => 'Unable to process request'], 404);
        }

        $response = json_decode($response);

        $logger = new gbowyUserController;
        if (is_object($response) || is_array($response)) {
            if ($logRequest = $logger->requestLog($request, $response)) {
                return response()->json($response, 201);
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Could not obtain requested data", "response" => $response], 404);
        }
    }
}
