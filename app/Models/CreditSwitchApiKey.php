<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreateApiKeysTable extends Model
{
    use HasFactory;
    protected $table = 'create_creditswitch_api_keys_tables';
    protected $guarded = [];
}
