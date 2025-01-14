<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillerVendor extends Model
{ protected $table = 'biller_vendors';

    protected $fillable = [
        'billerVendorName',
        'billerVendorKey',
        'billerVendorStatus',
        'agentId',
    ];

    // Validation rules for creating or updating a biller vendor
    // public static $rules = [
    //     'billerVendorName' => 'required|string|max:255',
    //     'billerVendorKey' => 'required|string|max:255',
    //     'billerVendorStatus' => 'required|string|max:255',
    //     'agentId' => 'required|integer|exists:agents,id',
    // ];

    // Define the relationship to the agent
    // public function agent()
    // {
    //     return $this->belongsTo(Agent::class);
    // }

    // Custom methods can be added for specific functionality
    public function customMethod()
    {
        // Your custom logic here
    }
}
