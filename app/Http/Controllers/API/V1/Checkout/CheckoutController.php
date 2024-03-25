<?php

namespace App\Http\Controllers\API\V1\Checkout;

use App\Enums\PaymentProviderMode;
use App\Enums\PaymentStatus;
use App\Http\Controllers\API\BaseApiController;
use App\Models\UserPaymentProvider;
use App\Repositories\EsewaRepo;
use App\Repositories\KhaltiRepo;
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

    function providers(Request $request)
    {
        $user = auth()->user();
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
        // $existingPayment = Payment::where('user_payment_provider_id', $request->user_payment_provider_id)
        // ->where('code', $request->code)
        // ->whereIn('status', ['Pending', 'Waiting'])
        // ->where(function ($query) {
        //     $query->where('status', 'Waiting')
        //         ->orWhere(function ($query) {
        //             $query->where('status', 'Pending')
        //                 ->where('expires_at', '>', now());
        //         });
        // })
        // ->where('expires_at', '>', now())
        // ->first();
        $existingPayment = null;

        if ($existingPayment) {
            // Return the existing payment if it's not expired
            return $this->success(new CheckoutPaymentResource($existingPayment), 'Existing payment returned.');
        } else {
            // Otherwise, create a new payment
            $payment = new Payment([
                'user_id' => $paymentProvider->user_id,
                'user_payment_provider_id' => $request->user_payment_provider_id,
                'amount' => $request->amount,
                'code' => $request->code,
                'failure_url' => $request->failure_url,
                'success_url' => $request->success_url,
                'status' => 'pending',
                'expires_at' => now()->addMinutes(10),
            ]);

            $payment->id = Str::uuid();
            $payment->save();
            $payment->load('userPaymentProvider');

            if ($payment->userPaymentProvider->mode === PaymentProviderMode::API) {
                if ($payment->userPaymentProvider->provider->code === "esewa") {
                    $tid = rand(99999, 9999999);
                    $data = "total_amount=100,transaction_uuid=$tid,product_code=EPAYTEST";
                    $s = hash_hmac('sha256', $data, '8gBm/:&EnhH.1/q', true);
                    $signature = base64_encode($s);


                    $payment->process_data = [
                        'url' => 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
                        'method' => "POST",
                        'fields' => [
                            "amount" => "100",
                            "tax_amount" => "0",
                            "total_amount" => "100",
                            "transaction_uuid" => $tid,
                            "product_code" => "EPAYTEST",
                            "product_service_charge" => "0",
                            "product_delivery_charge" => "0",
                            "success_url" => env('FE_URL') . '/checkout/handle/esewa',
                            "failure_url" => env('FE_URL') . '/checkout/handle/esewa',
                            "signed_field_names" => "total_amount,transaction_uuid,product_code",
                            "signature" => $signature,
                        ],
                    ];
                    $payment->transaction_id = $tid;
                    $payment->save();
                } else if ($payment->userPaymentProvider->provider->code === "khalti") {
                    $repo = new KhaltiRepo();
                    $link = $repo->generateLink($payment);
                    $payment->process_data = [
                        'url' =>  $link,
                        'method' => "GET",
                        'fields' => [
                        ],
                    ];
                    $payment->transaction_id = $payment->id;
                    $payment->save();
                }
            }
            return $this->success(new CheckoutPaymentResource($payment), 'New payment initiated successfully.');
        }
    }

    public function details(Request $request, $id)
    {
        $existingPayment = Payment::where('id', $id)
            // ->whereIn('status', ['Pending', 'Waiting'])
            // ->where(function ($query) {
            //     $query->where('status', 'Waiting')
            //         ->orWhere(function ($query) {
            //             $query->where('status', 'Pending')
            //                 ->where('expires_at', '>', now());
            //         });
            // })
            ->first();

        if ($existingPayment) {
            // Return the existing payment if it's not expired
            return $this->success(new CheckoutPaymentResource($existingPayment), 'Existing payment returned.');
        } else {
            return $this->error('Expired or Invalid Payment');
        }
    }


    public function update(Request $request, $paymentId)
    {
        $validator = Validator::make($request->all(), [
            'user_payment_provider_id' => 'nullable|sometimes|uuid|exists:user_payment_providers,id',
            'status' => 'required|in:waiting,cancelled',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors());
        }
        $payment = Payment::whereIn('status', ['pending', 'waiting'])
            ->where('expires_at', '>', now())->findOrFail($paymentId);

        if ($request->has('user_payment_provider_id')) {
            if ($payment->user_payment_provider_id !== $request->user_payment_provider_id && $payment->status === "waiting")
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
            if ($payment->status === "waiting")
                $payment->marked_paid_at = now();
            if ($payment->status === "cancelled")
                $payment->cancelled_at = now();
            $payment->save();
        }
        //TODO Add updates logs
        return $this->success(new CheckoutPaymentResource($payment));
    }


    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required',
            'data' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed', $validator->errors());
        }
        $payment = Payment::where('transaction_id', $request->transaction_id)
            // ->whereIn('status', ['Pending'])
            //     ->where('expires_at', '>', now())
            ->first();

        if (!$payment) {
            return $this->error('Payment not found');
        }

        // if ($payment->paid_at) {
        //     return $this->error('Payment already paid');
        // }

        if ($payment->cancelled_at) {
            return $this->error('Payment cancelled');
        }

        if ($request->provider === 'esewa') {
            $status = EsewaRepo::verify('uat', $request->data);
            if ($status === PaymentStatus::COMPLETE) {
                $payment->status = PaymentStatus::COMPLETE;
                $payment->completed_at = now();
                $payment->payment_data = $request->data;
                $payment->save();
            } else {
                $payment->status = "cancelled";
                $payment->cancelled_at = now();
                $payment->save();
            }
            // return ['status' => $status];
            // if ($request->status == 'success') {
            //     if ($request->provider === 'esewa') {
            //         //some esewa verificaiton stuff
            //         $payment->status = "Paid";
            //         $payment->paid_at = now();
            //         $payment->payment_data = $request->data;
            //         $payment->save();
            //     }
            // } else if ($request->status == "failure") {
            //     $payment->status = "Invalid";
            //     $payment->invalid_at = now();
            //     $payment->save();
            // }
        }

        //TODO Add updates logs

        return $this->success(new CheckoutPaymentResource($payment));
    }
}
