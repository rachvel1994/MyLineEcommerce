<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMonthlyReport extends Model
{
    protected $table = 'payment_monthly_report';

    public $timestamps = false;

    protected $primaryKey = 'payment_id';

    public $incrementing = false;

    protected $keyType = 'int';
}
