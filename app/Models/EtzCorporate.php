<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtzCorporate extends Model
{
    use HasFactory;
    protected $fillable = [
        'businessName',
        'businessId',
        'businessEmail',
        'businessPhone',
        'businessWebsite',
    ];
}
