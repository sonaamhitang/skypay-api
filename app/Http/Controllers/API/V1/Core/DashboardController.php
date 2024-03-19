<?php

namespace App\Http\Controllers\API\V1\Core;

use App\Http\Controllers\API\BaseApiController;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Str;

class DashboardController extends BaseApiController
{
    // GET: List all payments
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Payment::whereHas('userPaymentProvider.user', function ($query) use ($user) {
            $query->where('id', $user->id);
        });

        // Apply date filtering based on request parameters
        if ($request->has('from') && $request->has('to')) {
            // Filter by custom date range
            $from = Carbon::parse($request->input('from'))->startOfDay();
            $to = Carbon::parse($request->input('to'))->endOfDay();
            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($request->has('date')) {
            // Filter by a single date
            $date = Carbon::parse($request->input('date'))->startOfDay();
            $query->whereDate('created_at', $date);
        } else {
            // Default: no date filtering
        }

        // Execute the query
        $payments = $query->get();

        // Calculate counts by provider name
        $paymentCounts = $payments->groupBy(function ($payment) {
            return $payment->userPaymentProvider->provider->name;
        })->map->count();

        // Calculate other metrics (total payments, revenue, etc.)
        $totalPayments = $payments->count();
        $cancelledCount = $payments->where('status', 'Cancelled')->count();
        $successCount = $payments->where('status', 'Paid')->count();
        $invalidCount = $payments->where('status', 'Invalid')->count();
        $totalRevenue = $payments->where('status', 'Paid')->sum('amount');

        // Calculate success rate
        $successPayments = $payments->where('status', 'Paid')->count();
        $successRate = ($successPayments / max($totalPayments, 1)) * 100; // Avoid division by zero

        // Prepare and return the response
        $data = [
            'paymentCounts' => $paymentCounts,
            'totalPayments' => $totalPayments,
            'totalRevenue' => $totalRevenue,
            'successRate' => $successRate,
            'successCount' => $successCount,
            'cancelledCount' => $cancelledCount,
            'invalidCount' => $invalidCount,
            // Include other metrics here...
        ];

        return $this->success($data, 'Payments retrieved successfully.');
    }
}
