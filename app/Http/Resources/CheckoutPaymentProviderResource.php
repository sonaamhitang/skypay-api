<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutPaymentProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'provider_name' => $this->provider?->name,
            'provider_logo_url' => $this->provider?->logo_url,
            'mode' => $this->mode,
            'alias_name' => $this->alias_name,
            'order' => $this->order,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
