<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorData extends Model
{
    use HasFactory;
    protected $table="vendor_data";

    

    protected $fillable=[
        'name',
        'allowance',
        'price',
        'validity',
        'data_code',
        'biller_id',
        'vendor_id'
    ];
    
    public $timestamps = false;
   
}
