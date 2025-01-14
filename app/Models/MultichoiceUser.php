<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultichoiceUser extends Model
{
    use HasFactory;

    protected $table = 'multichoice_users';

    // Specify which fields can be mass assigned
    protected $fillable = [
        'customerNo',
        'firstname',
        'lastname',
    ];
}
