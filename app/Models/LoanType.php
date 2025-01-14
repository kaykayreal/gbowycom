<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanType extends Model
{
    use HasFactory; protected $fillable = ['type_name', 'interest_rate', 'max_loan_amount', 'max_loan_term'];
}
