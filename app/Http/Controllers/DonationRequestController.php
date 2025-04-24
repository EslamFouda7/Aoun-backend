<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DonationRequest;
use App\Models\Foundation;
use App\Models\Donor;
use Illuminate\Support\Facades\Storage;

class DonationRequestController extends Controller
{
    #-----------------------------------------------------------------------------------------------------
    # make request donation
    #-----------------------------------------------------------------------------------------------------
    public function store(Request $request)
    {
        $request->validate([
            'foundation_name' => 'required|string|exists:foundations,foundation_name',
            'location' => 'required|string',
            'reqiured_donation' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'required_amount' => 'required|numeric',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $foundation = Foundation::where('foundation_name', $request->foundation_name)->first();

        if (!$foundation) {
            return response()->json(['message' => 'Foundation not found'], 404);
        }

        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('donation_requests', $fileName, 'public');
        }

        $donationRequest = DonationRequest::create([
            'foundation_id' => $foundation->id,
            'title' => $request->title,
            'reqiured_donation' => $request->reqiured_donation,
            'description' => $request->description,
            'location' => $request->location,
            'required_amount' => $request->required_amount,
            'file_path' => $filePath,
        ]);

        return response()->json(['message' => 'Donation request created successfully', 'data' => $donationRequest], 201);
    }

    #---------------------------------------------------------------------------------------------------------------------------
    # update request
    #---------------------------------------------------------------------------------------------------------------------------
    public function update(Request $request, $id)
    {
        $donationRequest = DonationRequest::findOrFail($id);

        $request->validate([
            'foundation_name' => 'sometimes|string|exists:foundations,foundation_name',
            'location' => 'sometimes|string',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'required_amount' => 'sometimes|numeric',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->has('foundation_name')) {
            $foundation = Foundation::where('foundation_name', $request->foundation_name)->first();
            if (!$foundation) {
                return response()->json(['message' => 'Foundation not found'], 404);
            }
            $donationRequest->foundation_id = $foundation->id;
        }

        if ($request->hasFile('file')) {
            if ($donationRequest->file_path) {
                Storage::delete('public/' . $donationRequest->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('donation_requests', $fileName, 'public');
            $donationRequest->file_path = $filePath;
        }

        $donationRequest->update($request->only([
            'title',
            'description',
            'reqiured_donation',
            'location',
            'required_amount'
        ]));

        return response()->json(['message' => 'Donation request updated successfully', 'data' => $donationRequest]);
    }


    #------------------------------------------------------------
    # get all Request
    #-------------------------------------------------------------
    public function GetAllrequest()
    {
        $donationRequests = DonationRequest::with(['donations', 'foundation:id,foundation_name'])->get();

        if ($donationRequests->isEmpty()) {
            return response()->json(['message' => 'No donation requests found'], 404);
        }

        $formattedRequests = $donationRequests->map(function ($request) {
            $totalDonated = $request->donations->sum('amount');
            $remainingAmount = $request->required_amount - $totalDonated;
            $percentage = $request->required_amount > 0
                ? round(($totalDonated / $request->required_amount) * 100) : 0;

            return [
                'id' => $request->id,
                'title' => $request->title,
                'description' => $request->description,
                'required_donation' => $request->reqiured_donation,
                'required_amount' => $request->required_amount,
                'foundation_id' => $request->foundation_id,
                'file_path' => $request->file_path,
                'location' => $request->location,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'stats' => [
                    'total_donated' => $totalDonated,
                    'remaining_amount' => $remainingAmount,
                    'percentage_completed' => $percentage,
                ],
                'foundation' => $request->foundation ? [
                    'id' => $request->foundation->id,
                    'foundation_name' => $request->foundation->foundation_name,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $formattedRequests
        ]);
    }


    #-----------------------------------------------------------
    # get data for single Request
    #-----------------------------------------------------------
    public function Show($id)
    {
        $donationRequest = DonationRequest::with('foundation')->find($id);

        if (!$donationRequest) {
            return response()->json(['message' => 'Donation request not found'], 404);
        }

        return response()->json($donationRequest, 200);
    }

    #-----------------------------------------------------------
    # getRequestsByFoundationId
    #-----------------------------------------------------------
    public function getRequestsByFoundationId($foundationId)
    {
        $donationRequests = DonationRequest::with(['donations', 'foundation:id,foundation_name'])
            ->where('foundation_id', $foundationId)
            ->get();

        if ($donationRequests->isEmpty()) {
            return response()->json(['message' => 'No donation requests found for this foundation'], 404);
        }

        $staterequest = $donationRequests->map(function ($request) {
            $totalDonated = $request->donations->sum('amount');
            $remainingAmount = $request->required_amount - $totalDonated;
            $percentage = $request->required_amount > 0
                ? round(($totalDonated / $request->required_amount) * 100) : 0;


            return [
                'id' => $request->id,
                'title' => $request->title,
                'description' => $request->description,
                'required_donation' => $request->reqiured_donation,
                'required_amount' => $request->required_amount,
                'foundation_id' => $request->foundation_id,
                'file_path' => $request->file_path,
                'location' => $request->location,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'stats' => [
                    'total_donated' => $totalDonated,
                    'remaining_amount' => $remainingAmount,
                    'percentage_completed' => $percentage,
                ],
                'foundation' => $request->foundation ? [
                    'id' => $request->foundation->id,
                    'foundation_name' => $request->foundation->foundation_name,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $staterequest
        ]);
    }

    #getRequestsByLocation
    public function getRequestsByLocation()
    {
        $totalRequests = DonationRequest::count();
        $requestsByLocation = DonationRequest::selectRaw('location, COUNT(*) as requests_count')
            ->groupBy('location')->get()
            ->map(function ($item) use ($totalRequests) {
                $item->percentage = round(($item->requests_count / $totalRequests) * 100, 2);
                return $item;
            });
        return response()->json([
            'success' => true,
            'data' => $requestsByLocation
        ]);
    }
}
