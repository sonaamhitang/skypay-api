<?php

namespace App\Http\Resources;

use App\Enums\PaymentProviderMode;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $res = [
            'id' => $this->id,
            'user_provider_id' => $this->userPaymentProvider->id,
            'provider_id' => $this->userPaymentProvider->provider->id,
            'provider_name' => $this->userPaymentProvider->provider?->name,
            'provider_logo_url' => $this->userPaymentProvider->provider?->logo_url,
            'code' => $this->code,
            'mode' => $this->userPaymentProvider->mode,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,
            'status' => $this->status,
            'amount' => (int) $this->amount,
            'process_data' =>  $this->process_data,
            'payment_data' =>  $this->payment_data,
            'success_url'=> $this->success_url,
            'failure_url'=> $this->failure_url,

        ];

        if ($this->userPaymentProvider->mode === PaymentProviderMode::MANUAL) {
            $res['data'] = $this->userPaymentProvider->manual_configuration;
        }
        return $res;
    }
}
