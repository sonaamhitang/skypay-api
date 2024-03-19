<?php

namespace App\Http\Controllers\API\V1\Core;

use App\Http\Controllers\API\BaseApiController;
use App\Models\UserPaymentProvider;
use Illuminate\Http\Request;
use App\Models\Provider;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Resources\UserPaymentProviderResource;
use App\Enums\PaymentProviderMode;

class UserPaymentProviderController extends BaseApiController
{
    // GET: List all providers
    public function index()
    {
        $user = auth()->user();
        $providers = UserPaymentProvider::where('user_id', $user->id)->latest()->get();
        return $this->success(UserPaymentProviderResource::collection($providers), 'Providers retrieved successfully.');
    }

    // GET: Show a single provider
    public function show($id)
    {
        $user = auth()->user();
        $provider = UserPaymentProvider::where('user_id', $user->id)->findOrFail($id);
        return $this->success(new UserPaymentProviderResource($provider), 'Provider retrieved successfully.');
    }

    // POST: Create a new provider
    public function store(Request $request)
    {
        $user = auth()->user();
        // return $user;
        $input = $request->all();
        $rules = [
            'provider_id' => 'required',
            'mode' => 'required|in:Manual,API,Assisted',
        ];

        if ($request->mode === 'Manual') {
            $rules['manual_configuration.account_number'] = 'required|string';
            $rules['manual_configuration.account_name'] = 'required|string';
            $rules['manual_configuration.image'] = 'nullable|sometimes|image|max:1024';
        } elseif ($request->mode === 'API') {
            $rules['api_configuration.secret'] = 'required|string';
            $rules['api_configuration.environment'] = 'required|in:LIVE,UAT';
        } elseif ($request->mode === 'Assisted') {
            // Define rules for Assisted mode here
        }

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->error('Validation Error.', $validator->errors());
        }

        try {
            // Prepare the data for creating a new user
            $data = [
                'mode' => $request->mode,
                'notes' => $request->notes,
                'user_id' => $user->id,
                'provider_id' => $request->provider_id,
            ];

            if ($request->mode === PaymentProviderMode::MANUAL) {
                $data['manual_configuration'] = $request->manual_configuration;

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $customFileName = 'avatar' . '.' . $file->getClientOriginalExtension();

                    $media = $user->addMediaFromRequest('image')
                        ->usingFileName($customFileName)
                        ->toMediaCollection('default');
                    $data['manual_configuration']['image_url'] = $media->getUrl();
                } else {
                    $data['manual_configuration']['image_url'] = null;
                }
            } else if ($request->mode === PaymentProviderMode::API) {
                $data['api_configuration'] = $request->api_configuration;
            }
            $userPaymentProvider = new UserPaymentProvider($data);
            $userPaymentProvider->id = Str::uuid();
            $userPaymentProvider->save();
            $userPaymentProvider->load('provider');
            return $this->success(new UserPaymentProviderResource($userPaymentProvider), 'Provider created successfully.');
        } catch (\Exception $e) {
            return $e;
        }
        return $this->error('Something went wrong!');
    }

    // PUT/PATCH: Update a provider
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $input = $request->all();
        $userPaymentProvider = UserPaymentProvider::whereUserId($user->id)->findOrFail($id);
        $rules = [
            'provider_id' => 'nullable',
            'mode' => 'nullable|in:Manual,API,Assisted',
        ];
        if ($request->mode === 'Manual') {
            $rules['account_number'] = 'required|string';
            $rules['account_name'] = 'required|string';
            $rules['avatar'] = 'nullable|sometimes|image|max:1024';
        } elseif ($request->mode === 'API') {
            $rules['api_secret'] = 'required|string';
            $rules['environment'] = 'required|in:LIVE,UAT';
        } elseif ($request->mode === 'Assisted') {
            // Define rules for Assisted mode here
        }

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return $this->error('Validation Error.', $validator->errors());
        }

        try {
            // Prepare the data for creating a new user
            $data = $request->all();
            if ($request->mode === PaymentProviderMode::MANUAL) {
                $data['manual_configuration'] = [
                    'account_name' => $request->account_name,
                    'account_number' => $request->account_number,
                ];
            } else if ($request->mode === PaymentProviderMode::API) {
                $data['api_configuration'] = [
                    'environment' => $request->environment,
                    'secret' => $request->api_secret,
                ];
            }
            $userPaymentProvider->update($data);
            return $this->success(new UserPaymentProviderResource($userPaymentProvider), 'Updated successfully.');
        } catch (\Exception $e) {
            return $this->error('Something went wrong!');
        }
    }

    // DELETE: Remove a provider
    public function destroy($id)
    {
        $user = auth()->user();
        $provider = UserPaymentProvider::where('user_id', $user->id)->find($id);
        if (is_null($provider)) {
            return $this->error('Provider not found.');
        }

        $provider->delete();
        return $this->success([], 'Provider deleted successfully.');
    }
}
