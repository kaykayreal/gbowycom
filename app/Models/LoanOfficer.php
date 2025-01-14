<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanOfficer extends Model
{
    protected $fillable = ['user_id', 'name', 'contact_information'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
