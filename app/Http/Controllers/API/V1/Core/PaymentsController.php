<?php

namespace App\Http\Controllers\API\V1\Core;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Str;

class PaymentsController extends BaseApiController
{
    // GET: List all payments
    public function index()
    {
        $user = auth()->user();
        $payments = Payment::where('user_id', $user->id)->with('userPaymentProvider')->latest()->get();
        return $this->success(PaymentResource::collection($payments), 'Payments retrieved successfully.');
    }

    // GET: Show a single provider
    public function show($id)
    {
        $user = auth()->user();
        $provider = Payment::findOrFail($id);
        return $this->success(new PaymentResource($provider), 'Payment retrieved successfully.');
    }

    // POST: Create a new provider
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'code' => 'required|unique:payments,code',
            // Define other fields based on your validation requirements
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error.', $validator->errors());
        }
        $provider = new Payment($input);
        $provider->id = Str::uuid();
        $provider->save();
        return $this->success(new PaymentResource($provider), 'Payment created successfully.');
    }

    // PUT/PATCH: Update a provider
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Payment::where('user_id', $user->id)->find($id);
        if (is_null($provider)) {
            return $this->error('Payment not found.');
        }

        $input = $request->all();

        if($provider->status === 'Invalid') {
            return $this->error('Unable to update invalid payment.');
        }
        
        if($provider->status === 'cancelled' || $provider->status === 'paid') {

        }
        $validator = Validator::make($input, [
            'status' => 'required|in:cancelled,invalid,complete',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error.', $validator->errors());
        }

        if ($provider->status === 'completed') {
            $input['completed_at'] = now();
        } else if ($provider->status === 'cancelled') {
            $input['cancelled_at'] = now();
        } else if ($provider->status === 'invalid') {
            $input['invalid_at'] = now();
        }
        $provider->update($input);
        return $this->success(new PaymentResource($provider), 'Payment updated successfully.');
    }

    // DELETE: Remove a provider
    public function destroy($id)
    {
        $user = auth()->user();
        $provider = Payment::find($id);
        if (is_null($provider)) {
            return $this->error('Payment not found.');
        }

        $provider->delete();
        return $this->success([], 'Payment deleted successfully.');
    }
}
