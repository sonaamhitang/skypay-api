<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
    function userPaymentProvider()
    {
        return $this->belongsTo(UserPaymentProvider::class, 'user_payment_provider_id')->with('provider');
    }
}
