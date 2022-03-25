<?php

namespace App\Models\StallPartcipantPanel;

use Illuminate\Database\Eloquent\Model;

class StallPayment extends Model
{
    protected $table = "stall_payments";

    protected $fillable = [
        'user_id',
		'sp_stall_id',
		'amount',
		'payment_type_id',
		'is_partial',
		'transaction_no',
		'voucher_no',
		'voucher',
		'mac_addr',
		'pay_status',
		'status'
    ];
}
