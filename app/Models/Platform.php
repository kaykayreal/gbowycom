<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $table = 'platforms';

    protected $fillable = [
        'platformName',
        'appId',
        'x-api-key',
        'automation'
    ];

    // // Validation rules for creating or updating a platform
    // public static $rules = [
    //     'platformName' => 'required|string|max:255',
    //     'appId' => 'required|string|max:255',
    //     'x-api-key' => 'required|string|max:255',
    // ];

    // Define any relationships, if applicable
    // Example: a Platform has many BillerServices
    public function billerServices()
    {
        return $this->hasMany(BillerService::class);
    }

    // Custom methods can be added for specific functionality
    public function customMethod()
    {
        // Your custom logic here
    }
}
