<?php

namespace App\Http\Controllers\API\V1\Checkout;

use App\Http\Controllers\API\BaseApiController;
use App\Models\UserPaymentProvider;
use Illuminate\Http\Request;
use App\Http\Resources\CheckoutPaymentProviderResource;
use App\Http\Resources\CheckoutPaymentResource;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\error;

class CheckoutController extends BaseApiController
{

    function providers(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $providers = UserPaymentProvider::where('user_id', $user->id)->latest()->get();
        return $this->success(CheckoutPaymentProviderResource::collection($providers));
    }

    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_payment_provider_id' => 'required|uuid|exists:user_payment_providers,id',
            'code' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'success_url' => 'nullable|url',
            'failure_url' => 'nullable|url',
            'notes' => 'nullable|max:255',
            'other_info' => 'nullable|json',
            'customer_info' => 'nullable|json',
            'amount_info' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors());
        }

        $paymentProvider = UserPaymentProvider::with('user')->findOrFail($request->user_payment_provider_id);

        // Check for an existing payment that hasn't expired for the same store and code
        $existingPayment = Payment::where('user_payment_provider_id', $request->user_payment_provider_id)
            ->where('code', $request->code)
            ->whereIn('status', ['Pending', 'Waiting'])
            ->where(function ($query) {
                $query->where('status', 'Waiting')
                    ->orWhere(function ($query) {
                        $query->where('status', 'Pending')
                            ->where('expires_at', '>', now());
                    });
            })
            ->first();

        if ($existingPayment) {
            // Return the existing payment if it's not expired
            return $this->success(new CheckoutPaymentResource($existingPayment), 'Existing payment returned.');
        } else {
            // Otherwise, create a new payment
            $payment = new Payment([
                'user_id' => $paymentProvider->user_id,
                'user_payment_provider_id' => $request->user_payment_provider_id,
                'code' => $request->code,
                // Other fields...
                'expires_at' => now()->addMinutes(10),
            ]);

            $payment->id = Str::uuid();
            $payment->save();
            $payment->load('paymentProvider');

            return $this->success(new CheckoutPaymentResource($payment), 'New payment initiated successfully.');
        }
    }

    public function update(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'user_payment_provider_id' => 'nullable|sometimes|uuid|exists:user_payment_providers,id',
            'status' => 'required|in:Waiting,Cancelled',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors());
        }
        $payment = Payment::whereIn('status', ['Pending', 'Waiting'])
            ->where('expires_at', '>', now())->findOrFail($paymentId);

        if ($request->has('user_payment_provider_id')) {
            if ($payment->user_payment_provider_id !== $request->user_payment_provider_id && $payment->status === "Waiting")
                return $this->error("Please cancel this payment before switching provider");

            $validator = Validator::make($request->all(), [
                'user_payment_provider_id' => 'nullable|sometimes|uuid|exists:user_payment_providers,id',
            ]);

            if ($validator->fails()) {
                return $this->error('Validation failed', $validator->errors());
            }
            $payment->user_payment_provider_id = $request->user_payment_provider_id;
        }
        if ($request->has('status')) {
            $payment->status = $request->status;
            if ($payment->status === "Waiting")
                $payment->marked_paid_at = now();
            if ($payment->status === "Cancelled")
                $payment->cancelled_at = now();
            $payment->save();
        }
        //TODO Add updates logs
        return $this->success(new CheckoutPaymentResource($payment));
    }
}
