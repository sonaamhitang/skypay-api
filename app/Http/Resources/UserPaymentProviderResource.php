<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPaymentProviderResource extends JsonResource
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
            'manual_configuration' => $this->manual_configuration,
            'api_configuration' => $this->api_configuration,
            'assisted_configuration' => $this->assisted_configuration,
            'notes' => $this->notes,
            'mode' => $this->mode,
            'alias_name' => $this->alias_name,
            'order' => $this->order,
            'is_default' => (bool) $this->is_default,
            'status' => (bool) $this->status,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
        ];
    }
}
