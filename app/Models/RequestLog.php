<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $table = 'request_logs';

    protected $fillable = [
        'request_url',
        'request_method',
        'request_headers',
        'request_body',
        'ip_address',
        'user_agent',
        'merchant_ref',
        'response_status',
        'response_body',
        'merchant_code',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_body' => 'array',
    ];
}
