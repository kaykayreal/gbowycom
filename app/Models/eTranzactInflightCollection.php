<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class eTranzactInflightCollection extends Model
{
    use HasFactory;
    protected $table = 'e_tranzact_inflight_collections';
    protected $fillable = [
        'paymentID',
        'loanRef',
        'payeeID',
        'accountNo',
        'bankCode',
        'businessId',
        'amount',
        'source',
        'datePaid',
    ];
}
