<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'borrower_id',
        'loan_amount',
        'interest_rate',
        'loan_term',
        'loan_status',
        'date_approved',
        'date_disbursed',
        'repayment_frequency',
        'transaction_id',
    ];

    public function borrower()
    {
        return $this->belongsTo(Borrower::class);
    }

    public function repayments()
    {
        return $this->hasMany(Repayment::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
