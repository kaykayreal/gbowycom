<?php
namespace App\Http\Controllers\etranzact;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\eTranzactInflightCollection;

class eTzInflightController extends Controller
{
    public function eTzCPayNotification(Request $request)
    {
        $content = $request->getContent();

        // Remove carriage return and newline characters
        $content = str_replace("\r\n", "", $content);
        Log::info($content);
        // Decode the JSON string into an associative array
        $data = (json_decode($content, true));
        if (isset($data['datePaid'])) {
            $data['datePaid'] = Carbon::parse($data['datePaid'])->format('Y-m-d');
        }
        // Debugging: Dump the decoded data to check its contents
              
        if (eTranzactInflightCollection::create($data)) {
            echo "success";
        } else {
            echo "fail";
        }

        return response()->json(['message' => 'Webhook received'], 200);

    }
}

