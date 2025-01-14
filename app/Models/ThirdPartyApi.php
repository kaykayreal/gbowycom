<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThirdPartyApi extends Model
{
    use HasFactory;
    
    protected $table = 'third_party_apis';

    protected $fillable = [
        'name',
        'api_token'
    ];

    public function apiCredential()
    {
        return $this->hasOne(ApiCredential::class);
    }
}
