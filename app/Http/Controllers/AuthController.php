<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\RequestGuard;
use App\Models\Donor;
use App\Models\Foundation;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
 #------------------------------------------------------------------------------------------
# login
#---------------------------------------------------------------------------------------------
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // التحقق من Donor
        if (Auth::guard('donor')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('donor')->user();
            $token = $user->createToken('DonorToken')->plainTextToken;
            return response()->json(['token' => $token, 'user_type' => 'donor'], 200);
        }

        // التحقق من Foundation
        if (Auth::guard('foundation')->attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::guard('foundation')->user();
            $token = $user->createToken('FoundationToken')->plainTextToken;
            return response()->json(['token' => $token, 'user_type' => 'foundation'], 200);
        }

        // إذا فشل تسجيل الدخول
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
     #------------------------------------------------------------------------------------------
    # logout
    #---------------------------------------------------------------------------------------------
    public function logoutDonor(Request $request)
    {
        $user = Auth::guard('donor')->user();

        if ($user) {
            $user->tokens()->delete(); // حذف جميع Tokens الخاصة بالمستخدم
            return response()->json(['message' => 'Donor logged out successfully'], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
   
    public function logoutFoundation(Request $request)
    {
        $user = Auth::guard('foundation')->user();

        if ($user) {
            $user->tokens()->delete(); // حذف جميع Tokens الخاصة بالمستخدم
            return response()->json(['message' => 'Foundation logged out successfully'], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    #----------------------------------------------------------------------------------------
    #update pasword
    #----------------------------------------------------------------------------------------
    public function updatePassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'current_password' => 'required|string',
        'new_password' => 'required|string|confirmed',
    ]);

    // البحث عن المستخدم سواء كان Donor أو Foundation
    $donor = Donor::where('email', $request->email)->first();
    $foundation = Foundation::where('email', $request->email)->first();

    $user = $donor ?? $foundation;

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // التحقق من كلمة المرور الحالية
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json(['message' => 'Current password is incorrect'], 401);
    }

    // تحديث كلمة المرور الجديدة
    $user->password = Hash::make($request->new_password);
    $user->save();

    return response()->json(['message' => 'Password updated successfully']);
}
 #------------------------------------------------------------------------------------------
 # update profile
#---------------------------------------------------------------------------------------------
public function updateProfile(Request $request)
{
    $request->validate([
        'user_type' => 'required|in:donor,foundation', // تحديد نوع المستخدم
        'email' => 'required|email', // البريد الإلكتروني لتحديد المستخدم
        'full_name' => 'sometimes|string|max:255', // للمتبرع
        'foundation_name' => 'sometimes|string|max:255', // للمؤسسة
        'phone' => 'sometimes|string',
        'preferred_donation' => 'sometimes|string', // للمتبرع
        'required_donation' => 'sometimes|string', // للمؤسسة
        'location' => 'sometimes|string',
    ]);

    // تحديد نوع المستخدم
    if ($request->user_type === 'donor') {
        $user = Donor::where('email', $request->email)->first();
    } else {
        $user = Foundation::where('email', $request->email)->first();
    }

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // تحديث البيانات
    if ($request->has('full_name')) {
        $user->full_name = $request->full_name;
    }

    if ($request->has('foundation_name')) {
        $user->foundation_name = $request->foundation_name;
    }

    if ($request->has('phone')) {
        $user->phone = $request->phone;
    }

    if ($request->has('preferred_donation')) {
        $user->preferred_donation = $request->preferred_donation;
    }

    if ($request->has('required_donation')) {
        $user->required_donation = $request->required_donation;
    }

    if ($request->has('location')) {
        $user->location = $request->location;
    }

    $user->save();

    return response()->json(['message' => 'Profile updated successfully', 'data' => $user]);
}
#------------------------------------------------------------------------------------------------
public function getAllFoundations()
{
    $foundations = Foundation::all();  
    return response()->json(['message' => 'Foundations retrieved successfully', 'data' => $foundations]);
}
public function getAllDonors()
{
    $donors = Donor::all();  
    return response()->json(['message' => 'Donors retrieved successfully', 'data' => $donors]);
}
}
