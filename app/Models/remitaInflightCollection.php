<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class remitaInflightCollection extends Model
{
    use HasFactory;
    
        protected $fillable = [
            'amount',
            'modulename',
            'notificationSent',
            'dateNotificationSent',
            'firstNotificationSent',
            'dateFirstNotificationSent',
            'netSalary',
            'totalCredit',
            'customerPhoneNumber',
            'mandateRef',
            'balanceDue',
            'customer_id',
            'request_id',
            'payment_date',
            'payment_status',
            'status_reason',
            'dot_net_notification'

        ];



}
