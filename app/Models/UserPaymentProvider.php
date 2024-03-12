<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use App\Enums\PaymentProviderMode;

class UserPaymentProvider extends BaseModel
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'manual_configuration' => 'array',
        'api_configuration' => 'array',
        'assisted_configuration' => 'array',
        'preferences' => 'array',
        'credentials' => 'array',
    ];

    function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    function isManualMode()
    {
        return $this->mode == PaymentProviderMode::MANUAL;
    }

    function isApiMode()
    {
        return $this->mode == PaymentProviderMode::API;
    }
    function isAssistedMode()
    {
        return $this->mode == PaymentProviderMode::ASSISTED;
    }
}
