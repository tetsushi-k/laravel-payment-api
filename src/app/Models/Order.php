<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'stripe_payment_intent_id',
    ];
}
