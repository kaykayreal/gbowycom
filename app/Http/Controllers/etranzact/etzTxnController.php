<?php

namespace App\Http\Controllers\etranzact;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\etranzactDetail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;
use App\Http\Controllers\gbowy\gbowyUserController;

class etzTxnController extends Controller
{

    public function getToken()
    {
        $etzmod = etranzactDetail::where('id', '1')->first();
        $header = [
            'Authorization' => $etzmod->token,
            'Content-Type' => 'application/json'

        ];
        return ($header['Authorization']);
    }

    public function getNewToken()
    {
        $etz = new etzController;
        $token = $etz->getEtzToken();
        //$token = "eyJhbGciOiJIUzUxMiJ9.eyJpZCI6IjEwMDAwMDA4NzEiLCJleHAiOjE3MTQ4NjE5NDUsImlhdCI6MTcxNDgxMTk0NX0.EGg405ZNaOyDCzCBIt2BOZkBY_vb2MFKfcpQG5BfWR3FNh1FQmZNuiNrYxmvwAa6pP_YJkEUFB9SjrWj_H9NHQ";      
        return $token;
    }

    public function salaryInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accountNo' => 'required|numeric',
            'bankCode' => 'required|numeric',
            'businessId' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $etzController = new etzController;
        $response = $etzController->getSalaryInfo($request->accountNo, $request->businessId, $request->bankCode);
    }


    public function createLoan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loanRef' => ['required', 'string'],
            'employeeID' => ['required', 'string'],
            'accountNo' => ['required', 'string'],
            'bankCode' => ['required', 'string'],
            'businessId' => ['required', 'string'],
            'totalAmount' => ['required', 'numeric'],
            'rentalAmount' => ['required', 'numeric'],
            'balance' => ['required', 'numeric'],
            // 'tenure'=> ['required', 'numeric'],
            'tenureType' => ['required', 'string'],
            'nextChargeDate' => ['required', 'string'],
            'loanAccountNo' => ['required', 'string'],
            'loanBankcode' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
            'merchant_code' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $url = "https://www.etranzactng.net/autolend/loan";
        $payload = [
            'loanRef' => $request->loanRef,
            'employeeID' => $request->employeeID,
            'accountNo' => $request->accountNo,
            'bankCode' => $request->bankCode,
            'businessId' => $request->businessId,
            'totalAmount' => (float) $request->totalAmount,
            'rentalAmount' => (float) $request->rentalAmount,
            'balance' => (float) $request->balance,
            'tenure' => (int) $request->tenure,
            'tenureType' => $request->tenureType,
            'nextChargeDate' => $request->nextChargeDate,
            'loanAccountNo' => $request->loanAccountNo,
            'loanBankcode' => $request->loanBankcode,
        ];

        Log::info('Making HTTP request', [
            'url' => $url,
            'payload' => $payload,
        ]);
        $token = $this->getToken();
        $token = str_replace("Bearer", "", $token);

        try {

            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                    if ($exception instanceof RequestException) {
                        // Retry the request with the new token
                        $request->withToken($this->getNewToken());
                        return true;
                    }
                    $this->getNewToken(); // Assuming this method updates the token in your class
                    return false;
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
            return response()->json(['error' => $e->getMessage(), 'Message' => 'Unable to process request' . $this->getToken()], 404);
        }

        // if loan was successfully created. log it in table for recovery expectatio. 
        if (is_object($response) || is_array($response)) {
            $logger = new gbowyUserController;
            if ($logRequest = $logger->requestLog($request, $response)) {

                return json_decode($response);
            } else {
                return response()->json($response->getBody()->getContents(), 200);
            }
        } else {
            return response()->json(['error' => "Unable to process request"], 404);
        }
    }

    public function createRepayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loanRef' => ['required', 'string'],
            'paymentID' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'datePaid' => ['required', 'string'],
            'merchant_ref' => [
                'required',
                'string',
                'max:255',
                Rule::unique('request_logs')->where(function ($query) use ($request) {
                    return $query->where('merchant_code', $request->merchant_code);
                }),
            ],
            'merchant_code' => ['required', 'string']
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $url = "https://www.etranzactng.net/autolend/repayment";

        $payload = [
            'loanRef' => $request->loanRef,
            'paymentID' => $request->paymentID,
            'amount' => floatval($request->amount),
            'datePaid' => $request->datePaid
        ];

        $token = $this->getToken();
        $token = str_replace("Bearer", "", $token);

        try {
            $response = Http::withToken($token)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                    if ($exception instanceof RequestException) {
                        // Retry the request with the new token
                        $request->withToken($this->getNewToken());
                        return true;
                    }
                    //$this->getNewToken(); // Assuming this method updates the token in your class
                    return false;
                })
                ->post($url, $payload);
        } catch (Exception $e) {
            // Handle the exception here generate new token and store in the database
            $newToken = $this->getNewToken();
            // For example, you can log the error message or return a specific response
            log::error(['error' => $e->getMessage()], ['Message' => 'Unable to process request']);
            return response()->json(['error' => $e->getMessage(), 'Message' => 'Unable to process request, please try again'],);
        }

        // if it was successfully created.//log in requestLog table  
        if (is_object($response) || is_array($response)) {
            $logger = new gbowyUserController;
            if ($logRequest = $logger->requestLog($request, $response)) {
                return $response;
            } else {
                return response()->json($response, 200);
            }
        } else {
            return response()->json(['error' => "Unable to process request"], 404);
        }
    }
}
