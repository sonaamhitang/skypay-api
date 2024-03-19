<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_payment_provider_id' => $this->userPaymentProvider->id,
            'provider_id' => $this->userPaymentProvider->provider->id,
            'provider_alias' =>  $this->userPaymentProvider?->alias_name,
            'provider_name' =>  $this->userPaymentProvider->provider?->name,
            'provider_logo_url' =>$this->userPaymentProvider->provider?->logo_url,
            'mode' =>$this->userPaymentProvider->mode,
            'amount' => (float) $this->amount,
            'status' => (string) $this->status,
            'code' => (string) $this->code,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'expires_at' => $this->expires_at ? $this->expires_at->toDateTimeString() : null,
        ];
    }
}
