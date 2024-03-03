<?php

namespace App\Http\Controllers\API\V1\Core;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Str;

class ProviderController extends BaseApiController
{
    // GET: List all providers
    public function index()
    {
        $providers = Provider::latest()->get();
        return $this->success(ProviderResource::collection($providers), 'Providers retrieved successfully.');
    }

    // GET: Show a single provider
    public function show($id)
    {
        $provider = Provider::findOrFail($id);
        return $this->success(new ProviderResource($provider), 'Provider retrieved successfully.');
    }

    // POST: Create a new provider
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'code' => 'required|unique:providers,code',
            // Define other fields based on your validation requirements
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error.', $validator->errors());
        }
        $provider = new Provider($input);
        $provider->id = Str::uuid();
        $provider->save();
        return $this->success(new ProviderResource($provider), 'Provider created successfully.');
    }

    // PUT/PATCH: Update a provider
    public function update(Request $request, $id)
    {
        $provider = Provider::find($id);
        if (is_null($provider)) {
            return $this->error('Provider not found.');
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'nullable',
            'code' => 'nullable|unique:providers,code,' . $id,
            'status' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
            'integration_difficulty' => 'nullable|string',
            'integration_difficulty' => 'nullable|string',
            // Define other fields based on your validation requirements
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error.', $validator->errors());
        }

        $provider->update($input);
        return $this->success(new ProviderResource($provider), 'Provider updated successfully.');
    }

    // DELETE: Remove a provider
    public function destroy($id)
    {
        $provider = Provider::find($id);
        if (is_null($provider)) {
            return $this->error('Provider not found.');
        }

        $provider->delete();
        return $this->success([], 'Provider deleted successfully.');
    }
}
