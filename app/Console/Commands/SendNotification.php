<?php

namespace App\Console\Commands;

use App\Http\Controllers\remita\RemitaInflightController;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send remita_inflight notifications to .net';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //get the list of notifications where dotNetNotification status has not been updated
        $remitaNotification = New RemitaInflightController;
        $remitaNotification->notifyRemita();
        //send to .net 
    }
}
