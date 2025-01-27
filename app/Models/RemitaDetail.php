<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemitaDetail extends Model
{
    use HasFactory;
    protected $table = 'remita_details';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'content_type',
        'api_key',
        'merchant_id',
        'url',
    ];
   
}
