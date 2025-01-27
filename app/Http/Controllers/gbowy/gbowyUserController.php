<?php

namespace App\Http\Controllers\gbowy;

use App\Models\User;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class gbowyUserController extends Controller
{
    public function Register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $merchant_code = $this->setMerchantCode();
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'merchant_code' => $merchant_code
        ]);

        unset($user->created_at, $user->updated_at, $user->email_verified_at);

        $token = $user->createToken('gbowyToken')->plainTextToken;
        $response = [
            'user' => $user,
            'token' => $token,
            'merchant_code' => $merchant_code
        ];

        return response($response, 201);
    }

    public function setMerchantCode()
    {


        do {
            $merchant_code = strval(mt_rand(100000000, 999999999));
        } while (User::where('merchant_code', $merchant_code)->exists());

        return $merchant_code;
    }

    //merchnt_code username and password for new_token 
    public function createToken(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'merchant_code' => ['required', 'numeric'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        //check merchantcode, email and password if it is correct
        $user = User::where('merchant_code', $request->merchant_code)->first();
        $hashedPassword = Hash::make($request->password);

        if (is_object($user) && $user->email == $request->email && Hash::check($request->password, $user->password)) {

            // Delete all existing tokens
            $user->tokens()->delete();

            //now you can create new token
            $token = $user->createToken('gbowyToken')->plainTextToken;
            $response = [
                'user' => $user,
                'token' => $token,
                'merchant_code' => $request->merchant_code
            ];

            return response($response, 201);
        } else {
            return response()->json(['errors' => 'Invalid User Details'], 401);
        }
    }

    public function requestLog(Request $request, $response)
    {
        // initiate datareferencing debit for successful calls with proper responses 


        $logData = [
            'request_url' => $request->server('REQUEST_URI'),
            'request_method' => $request->server('REQUEST_METHOD'),
            'request_headers' => ($request->header()),
            'request_body' => json_encode($request->getContent()),
            'ip_address' => $request->ip(),
            'merchant_ref' => $request->merchant_ref,
            'user_agent' => $request->header('user-agent'),
            'response_body' => $request->server('REQUEST_URI') == "/api/eTz/init/getAllCorporate" ? "...list of Corporates" : ($response),
            'merchant_code' => $request->merchant_code,

        ];

        if (RequestLog::create($logData)) {
            return true;
        } else {
            return false;
        }
    }
}
