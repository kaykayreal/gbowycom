<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'payment_gateways';

    protected $fillable = [
        'PayGateName',
        'url',
        'gateKey',
        'publicKey',
        'secretHash',
        'encryptionKey',
        'vurl',
    ];

    // Validation rules for creating or updating a payment gateway
    // public static $rules = [
    //     'PayGateName' => 'required|string|max:255',
    //     'url' => 'required|string|max:255',
    //     'gateKey' => 'required|string|max:255',
    //     'publicKey' => 'required|string|max:255',
    //     'secretHash' => 'required|string|max:255',
    //     'encryptionKey' => 'required|string|max:255',
    //     'vurl' => 'required|string|max:255',
    // ];

    // Define any relationships, for example, if a PaymentGateway belongs to a User
    // public function user()
    // {
    //     // return $this->belongsTo(User::class);
    // }

    // Custom methods can be added for specific functionality
    public function customMethod()
    {
        // Your custom logic here
    }
}
