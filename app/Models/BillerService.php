<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillerService extends Model
{
    protected $table = 'biller_services';

    protected $fillable = [               
        'customerId',
        'transactionId',
        'category',
        'biller',
        'subscription',
        'subscriptionMonth',
        'addon',
        'addonMonth',
        'preferredVendor',
        'email',
        'amount',
        'vendingStatus',
        'vendingRetrials',
        'payload',
        'plan',
        'service_description'
    ];

    // Define the foreign key relationship for the platform
    public function platform()
    {
        return $this->belongsTo(Platform::class, 'Id');
    }

    // Any additional model-specific configurations can be added here.
}
