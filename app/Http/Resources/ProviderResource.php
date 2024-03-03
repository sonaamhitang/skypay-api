<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProviderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'logo_url' => $this->logo_url,
            'status' => (bool) $this->status,
            'description' => $this->description,
            'fee_percentage' => $this->fee_percentage,
            'fee_fixed' => $this->fee_fixed,
            'currency' => $this->currency,
            'minimum_amount' => $this->minimum_amount,
            'maximum_amount' => $this->maximum_amount,
            'website_url' => $this->website_url,
            'documentation_url' => $this->documentation_url,
            'support_email' => $this->support_email,
            'region' => $this->region,
            'integration_difficulty' => $this->integration_difficulty,
            'signup_url' => $this->signup_url,
            'api_version' => $this->api_version,
            'rating' => $this->rating,
            'featured' =>  (bool) $this->featured,
        ];
    }
}
