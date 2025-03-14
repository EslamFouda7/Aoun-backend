<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Donor;
use App\Models\Foundation;

class ProfileController extends Controller
{
    #-----------------------------------------------------------
    #Donor
    #-------------------------------------------------------------
    public function updateDonor(Request $request)
    {
        // التحقق من أن المستخدم مسجل دخوله كـ Donor
        $donor = Auth::guard('donor')->user();

        if (!$donor) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // تحقق من البيانات المرسلة
        $request->validate([
            'full_name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:donors,email,' . $donor->id,
            'phone' => 'sometimes|string',
            'preferred_donation' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        // تحديث البيانات
        $donor->update($request->all());

        return response()->json(['message' => 'Donor updated successfully', 'donor' => $donor], 200);
    }
    #-----------------------------------------------------------
    #Foundation
    #-------------------------------------------------------------
    public function updateFoundation(Request $request)
    {
        // التحقق من أن المستخدم مسجل دخوله كـ Foundation
        $foundation = Auth::guard('foundation')->user();

        if (!$foundation) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // تحقق من البيانات المرسلة
        $request->validate([
            'foundation_name' => 'sometimes|string',
            'type_of_foundation' => 'sometimes|string',
            'email' => 'sometimes|email|unique:foundations,email,' . $foundation->id,
            'phone' => 'sometimes|string',
            'required_donation' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $foundation->update($request->all());

        return response()->json(['message' => 'Foundation updated successfully', 'foundation' => $foundation], 200);
    }
}