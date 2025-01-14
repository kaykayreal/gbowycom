<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCable extends Model
{
    use HasFactory;

    protected $table = 'vendor_cables';

    protected $fillable = [
        'name',
        'amount',
        'code',
        'addon_code',
        'service_type',
        'vendor_id',
    ];

    // Define any relationships, if needed
    
}
