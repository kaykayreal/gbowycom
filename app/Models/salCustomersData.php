<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalCustomer extends Model
{
    protected $table = 'salCustomers';

    protected $fillable = [
        'customer_id',
        'first_name',
        'middle_name',
        'last_name',
        'account_number',
        'bvn',
        'bank',
        'company_name',
        'category',
        'first_payment_date',
        'salary_count',
        'average_salary',
        'existing_loan',
        'tenure',
        'original_customer_id',
    ];

    protected $casts = [
        'first_payment_date' => 'datetime',
    ];

    /**
     * Get the salary payments for the customer.
     */
    public function salaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Get the loan histories for the customer.
     */
    public function loanHistories(): HasMany
    {
        return $this->hasMany(LoanHistory::class);
    }
}
