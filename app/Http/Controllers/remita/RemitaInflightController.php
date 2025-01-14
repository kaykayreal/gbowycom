<?php

namespace App\Http\Controllers\remita;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ApiCredential;
use App\Models\ThirdPartyApi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\remitaInflightCollection;

class RemitaInflightController extends Controller
{
  //
  public function remitaInflightCollections(Request $request)
  {

    //$paymentDate = Carbon::createFromFormat('d-m-Y H:i:sO',$request->input('payment_date') );
    //register the information against the database
    if (($request->input('id')) !== null) {

      $data = [
        "id" => $request->input('id'),
        "amount" => $request->input('amount'),
        "statusCode" => $request->input('statusCode'),
        "modulename" => $request->input('modulename'),
        "notificationSent" => $request->input('notificationSent'),
        "dateNotificationSent" => $request->input('dateNotificationSent'),
        "firstNotificationSent" => $request->input('firstNotificationSent'),
        "dateFirstNotificationSent" => $request->input('dateFirstNotificationSent'),
        "netSalary" => $request->input('netSalary'),
        "totalCredit" => $request->input('totalCredit'),
        "customerPhoneNumber" => $request->input('customerPhoneNumber'),
        "mandateRef" => $request->input('mandateRef'),
        "balanceDue" => $request->input('balanceDue'),
        "customer_id" => $request->input('customer_id'),
        "request_id" => $request->input('request_id'),
        "payment_date" => $request->input('payment_date'),
        "payment_status" => $request->input('payment_status'),
        "status_reason" => $request->input('status_reason'),

      ];

      if (remitaInflightCollection::create($data)) {

        return response()->json([
          'Response_code' => '00',
          'response_descr' => 'Successful',
          "ack_id" => "00"
        ]);
      }
    }

    //set up for cron job to pick it up until successful
    //update all other internal tables         
  }

  public function generateDotNetToken()
  {

    $credentials = ThirdPartyApi::whereHas('ApiCredential', function ($query) {
      $query->where('name', 'generateDotNetToken')->where('id', '1');
    })->first();



    $endpoint = decrypt($credentials->ApiCredential->endpoint);
    $payload = decrypt($credentials->ApiCredential->payload);
    $payload = str_replace("'", '"', $payload);
    $payload = json_decode($payload, true);



    $response = Http::withHeaders([
      'Accept' => 'application/json',
    ])->post($endpoint, $payload);


    //update tables 
    $response = json_decode($response);
    $token = encrypt($response->token);

    $credentials->api_token = $token;
    if ($credentials->update()) {
      return response()->json([
        "Response" => "success",
        "Message" => "Token Updated"
      ], 201);
    }
  }

  public function notifyRemita()
  {

    // Prepare header
    $credential = DB::table('third_party_apis')
      ->join('api_credentials', 'third_party_apis.id', '=', 'api_credentials.third_party_api_id')
      ->where('api_credentials.name', 'notifyRemita')
      ->where('api_credentials.id', 2)
      ->select('third_party_apis.*', 'api_credentials.*')
      ->first();

    $token = decrypt($credential->api_token);
    $endpoint = decrypt($credential->endpoint);

    // Get the mandate ref, and obtain the service provider details from user table
    //$remitaCollection = remitaInflightCollection::where('dot_net_notification', 'pending')->get();
    $remitaCollection = remitaInflightCollection::whereIn('dot_net_notification', ['pending', 'retry'])->get();

    $merchantCode = '7038208945';


    foreach ($remitaCollection as $record) {
      // Add the merchant_code to the record array
      $record->merchantCode = $merchantCode;
      $payload = json_decode($record, true);

      // Send the POST request with the token in the headers
      $response = Http::withHeaders([
        'Authorization' => "Bearer $token",
        'Accept' => 'application/json',
      ])->post($endpoint, $payload);


      // Check the response and handle accordingly
      unset($record->merchantCode);
      $recordArray = $record->toArray();
      if ($response->successful()) {
        // Update the record status or perform other actions
        $record->update(['dot_net_notification' => 'closed']);
      } else {
        // Log or handle the error
        $record->update(['dot_net_notification' => 'retry']);
        Log::error('Failed to send record via API', ['record' => $record, 'response' => $response->body()]);
      }
    }
  }
}
