<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrower extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'name', 'contact_information', 'address'];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
