<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DonationRequest;
use App\Models\Foundation;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image; 

class DonationRequestController extends Controller
{
#-----------------------------------------------------------------------------------------------------
# make request donation 
#----------------------------------------------------------------------------------------------------

    public function store(Request $request)
{
    $request->validate([
        'foundation_name' => 'required|string|exists:foundations,foundation_name', // التحقق من وجود المؤسسة بالاسم
        'location' => 'required|string',
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'required_amount' => 'required|numeric',
        'file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
    ]);

    // البحث عن المؤسسة باستخدام الاسم
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
        'foundation_id' => $foundation->id, // استخدام الـ id الخاص بالمؤسسة
        'title' => $request->title,
        'description' => $request->description,
        'location' => $request->location,
        'required_amount' => $request->required_amount,
        'file_path' => $filePath,
    ]);

    return response()->json(['message' => 'Donation request created successfully', 'data' => $donationRequest], 201);
}
#---------------------------------------------------------------------------------------------------------------------------
#update request
#----------------------------------------------------------------------------------------------------------------------------
public function update(Request $request, $id)
{
    $donationRequest = DonationRequest::findOrFail($id);

    $request->validate([
        'foundation_name' => 'sometimes|string|exists:foundations,foundation_name', // التحقق من وجود المؤسسة بالاسم
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

    $donationRequest->update($request->only(['title', 'description', 'location', 'required_amount']));

    return response()->json(['message' => 'Donation request updated successfully', 'data' => $donationRequest]);
}
}
