<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'api_key' => $this->api_key,
            'email_verified_at' => $this->email_verified_at ? $this->email_verified_at->toDateTimeString() : null,
            'phone_verified_at' => $this->phone_verified_at ? $this->phone_verified_at->toDateTimeString() : null,
            'avatar_url' => $this->avatar_url,
            'last_login_at' => $this->last_login_at ? $this->last_login_at->toDateTimeString() : null,
            'last_login_ip' => $this->last_login_ip,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'business_legal_type' => $this->business_legal_type,
            'business_legal_number' => $this->business_legal_number,
            'fcm_token' => $this->fcm_token,
            'status' => $this->status,
            'subscription_plan' => $this->subscription_plan,
            'subscription_expiry' => $this->subscription_expiry ? $this->subscription_expiry->toDateTimeString() : null,
            'balance' => (float) $this->balance,
            'parent_id' => $this->parent_id,
        ];
    }
}
