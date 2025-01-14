<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'payGateName',
        'txnRef',
        'amount',
        'created_at',
        'fees',
        'gatewayResponse',
        'gateId',
        'ipAddress',
        'status',
        'bank',
        'bin',
        'brand',
        'channel',
        'expMonth',
        'expYear',
        'lastFour',
        'dump',
        'transactionId',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fees' => 'decimal:2',
    ];

    // Validation rules for creating or updating a payment transaction
    public static $rules = [
        'payGateName' => 'required|string|max:255',
        'txnRef' => 'required|string|max:255',
        'amount' => 'required|numeric',
        // Add validation rules for other fields
    ];

    // Define any relationships, if applicable
    // Example: a PaymentTransaction belongs to a User
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }

    // Custom methods can be added for specific functionality
    public function customMethod()
    {
        // Your custom logic here
    }
}
