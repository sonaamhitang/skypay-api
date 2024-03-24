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
            $rules['manual_configuration_account_name'] = 'required|string';
            $rules['manual_configuration_account_number'] = 'required|string';
            $rules['manual_configuration_image'] = 'nullable|sometimes|image|max:1024';
        } elseif ($request->mode === 'API') {
            $rules['api_configuration_secret'] = 'required|string';
            $rules['api_configuration_environment'] = 'required|in:LIVE,UAT';
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
                $data['manual_configuration'] = [
                    'account_number' => $request->input('manual_configuration_account_number'),
                    'account_name' => $request->input('manual_configuration_account_name'),
                ];
            } else if ($request->mode === PaymentProviderMode::API) {
                $data['api_configuration'] = [
                    'environment' => $request->input('api_configuration_environment'),
                    'secret' => $request->input('api_configuration_secret'),
                ];
            }
            $userPaymentProvider = new UserPaymentProvider($data);
            $userPaymentProvider->id = Str::uuid();
            $userPaymentProvider->save();

            if ($userPaymentProvider->mode === PaymentProviderMode::MANUAL) {
                if ($request->hasFile('manual_configuration_image')) {
                    $file = $request->file('manual_configuration_image');
                    $customFileName = 'image' . '.' . $file->getClientOriginalExtension();

                    $media = $userPaymentProvider->addMediaFromRequest('manual_configuration_image')
                        ->usingFileName($customFileName)
                        ->toMediaCollection('default');

                    $mData = $userPaymentProvider->manual_configuration;
                    $mData['image_url'] = $media->getUrl();
                    $userPaymentProvider->manual_configuration = $mData;
                    $userPaymentProvider->save();
                }
            }

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
            $rules['manual_configuration_account_name'] = 'required|string';
            $rules['manual_configuration_account_number'] = 'required|string';
            $rules['manual_configuration_image'] = 'nullable|sometimes|image|max:1024';
        } elseif ($request->mode === 'API') {
            $rules['api_configuration_secret'] = 'required|string';
            $rules['api_configuration_environment'] = 'required|in:LIVE,UAT';
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
                    'account_number' => $request->input('manual_configuration_account_number'),
                    'account_name' => $request->input('manual_configuration_account_name'),
                ];

                if ($request->hasFile('manual_configuration_image')) {

                    $file = $request->file('manual_configuration_image');
                    $customFileName = 'image' . '.' . $file->getClientOriginalExtension();

                    $userPaymentProvider->clearMediaCollection('default');
                    $media = $userPaymentProvider->addMediaFromRequest('manual_configuration_image')
                        ->usingFileName($customFileName)
                        ->toMediaCollection('default');
                    $data['manual_configuration']['image_url'] = $media->getUrl();
                } else {
                    $data['manual_configuration']['image_url'] = null;
                }
            } else if ($request->mode === PaymentProviderMode::API) {
                $data['api_configuration'] = [
                    'environment' => $request->input('api_configuration_environment'),
                    'secret' => $request->input('api_configuration_secret'),
                ];
            }
            $userPaymentProvider->update($data);
            return $this->success(new UserPaymentProviderResource($userPaymentProvider), 'Updated successfully.');
        } catch (\Exception $e) {
            return $e;
            return $this->error('Something went wrong!', $e);
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
