<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationRequest;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    /**
     * تسجيل تبرع جديد
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'donation_request_id' => 'required|exists:donation_requests,id',
            'donor_id' => 'required|exists:donors,id',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:credit_card,bank_transfer,paypal,cash',
            'currency' => 'sometimes|in:USD,EGP',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $donation = Donation::create([
                'donation_request_id' => $request->donation_request_id,
                'donor_id' => $request->donor_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'currency' => $request->currency ?? 'USD',
                'status' => 'completed',
                'notes' => $request->notes,
                'transaction_id' => 'DON-' . time() . '-' . uniqid()
            ]);

            // تحديث إحصائيات طلب التبرع
            $donationRequest = DonationRequest::with('donations')->find($request->donation_request_id);
            $totalDonated = $donationRequest->donations()->sum('amount');
            $remainingAmount = $donationRequest->required_amount - $totalDonated;
            $percentage = round(($totalDonated / $donationRequest->required_amount) * 100);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Donation completed successfully',
                'data' => [
                    'donation' => $donation,
                    'donation_request_status' => [
                        'total_required' => $donationRequest->required_amount,
                        'total_donated' => $totalDonated,
                        'remaining_amount' => $remainingAmount,
                        'percentage_completed' => $percentage
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Donation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * عرض تفاصيل تبرع معين
     */
    public function show($id)
    {
        $donation = Donation::with(['donationRequest', 'donor'])->find($id);

        if (!$donation) {
            return response()->json([
                'status' => 'error',
                'message' => 'Donation not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $donation
        ]);
    }

    /**
     * الحصول على إحصائيات تبرعات لطلب معين
     */
    public function getRequestStats($requestId)
    {
        $donationRequest = DonationRequest::with('donations')->find($requestId);

        if (!$donationRequest) {
            return response()->json([
                'status' => 'error',
                'message' => 'Donation request not found'
            ], 404);
        }

        $totalDonated = $donationRequest->donations()->sum('amount');
        $remainingAmount = $donationRequest->required_amount - $totalDonated;
        $percentage = round(($totalDonated / $donationRequest->required_amount) * 100);

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_required' => $donationRequest->required_amount,
                'total_donated' => $totalDonated,
                'remaining_amount' => $remainingAmount,
                'percentage_completed' => $percentage,
                'donations_count' => $donationRequest->donations->count()
            ]
        ]);
    }

    /**
     * الحصول على تبرعات متبرع معين
     */
    public function getDonorDonations($donorId)
    {
    $donor = Donor::with('donations.donationRequest.donations')->find($donorId);

    if (!$donor)
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Donor not found'
        ], 404);
    }
        $donations = $donor->donations->map(function ($donation)
         {
        $donationRequest = $donation->donationRequest;
        if ($donationRequest)
        {
            $totalDonated = $donationRequest->donations->sum('amount');
            $remainingAmount = $donationRequest->required_amount - $totalDonated;
            $percentage = $donationRequest->required_amount > 0
            ? round(($totalDonated / $donationRequest->required_amount) * 100) : 0;
        }
        else
         {
            $totalDonated = $remainingAmount = $percentage = null;
         }

        return [
            'donation' => $donation,
            'status' =>
            [
                'total_required' => $donationRequest->required_amount ?? null,
                'total_donated' => $totalDonated,
                'remaining_amount' => $remainingAmount,
                'percentage_completed' => $percentage
            ]
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $donations
    ]);
}
    #--------------------------------------------------------------

}

