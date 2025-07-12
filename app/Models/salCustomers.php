<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalCustomer extends Model
{
    protected $table = 'salCustomers';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'account_number',
        'bvn',
        'bank',
        'average_salary',
        'existing_loan',
        'tenure',
        'status',
    ];
}
// The model represents the 'salCustomers' table in the database.