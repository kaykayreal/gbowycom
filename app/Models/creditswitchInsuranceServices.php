<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class creditswitchInsuranceServices extends Model
{
    use HasFactory;

    protected $table = 'creditswitchInsuranceServices';
    // Specify the primary key and that it is non-incrementing
    protected $primaryKey = 'service_id';
    public $incrementing = false;

    protected $fillable = [
        'service_id',
        'name',
        'invoice_period',
        'product_type'
    ];

    // Cast JSON columns to arrays
    protected $casts = [
        'invoice_period' => 'array',
        'product_type' => 'array',
    ];
}
