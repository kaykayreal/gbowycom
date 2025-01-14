<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCredential extends Model
{
    use HasFactory;
    use HasFactory;

    protected $table = 'api_credentials';

    protected $fillable = [
        'name',
        'endpoint',
        'description',
        'third_party_apis_id',
        'payload',
    ];

    public function thirdPartyApi()
    {
        return $this->belongsTo(ThirdPartyApi::class);
    }
   
}
