<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\RequestGuard;
use App\Models\Donor;
use App\Models\Foundation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;

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
        return response()->json([
            'token' => $token,
            'user_type' => 'donor',
            'user_id' => $user->id,
        ], 200);
    }

    // التحقق من Foundation
    if (Auth::guard('foundation')->attempt(['email' => $request->email, 'password' => $request->password])) {
        $user = Auth::guard('foundation')->user();
        $token = $user->createToken('FoundationToken')->plainTextToken;
        return response()->json([
            'token' => $token,
            'user_type' => 'foundation',
            'user_id' => $user->id,
        ], 200);
    }

    return response()->json(['error' => 'User not found'], 401);
}

     #------------------------------------------------------------------------------------------
    # logout
    #---------------------------------------------------------------------------------------------
    public function logoutDonor(Request $request)
    {
        $user = Auth::guard('donor')->user();

        if ($user) {
            $user->tokens()->delete();
            return response()->json(['message' => 'Donor logged out successfully'], 200);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function logoutFoundation(Request $request)
    {
        $user = Auth::guard('foundation')->user();

        if ($user) {
            $user->tokens()->delete();
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
            'user_id' => 'required|integer',
            'user_type' => 'required|string',
            'current_password' => 'required|string',
            'new_password' => 'required|string|confirmed',
        ]);

        if ($request->user_type === 'donor') {
            $user = Donor::find($request->user_id);
        } else {
            $user = Foundation::find($request->user_id);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // التحقق من كلمة المرور الحالية
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Old password is incorrect'], 401);
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
        'user_id' => 'required|integer',
        'user_type' => 'required|in:donor,foundation',
        'email' => 'sometimes|email',
        'full_name' => 'sometimes|string|max:255',
        'foundation_name' => 'sometimes|string|max:255',
        'phone' => 'sometimes|string',
        'preferred_donation' => 'sometimes|string',
        'required_donation' => 'sometimes|string',
        'location' => 'sometimes|string',
    ]);

    if ($request->user_type === 'donor') {
        $user = Donor::find($request->user_id);
    } else {
        $user = Foundation::find($request->user_id);
    }

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    if ($request->has('email')) {
        $emailExistsInDonors = Donor::where('email', $request->email)
            ->where('id', '!=', $request->user_id)
            ->exists();

        $emailExistsInFoundations = Foundation::where('email', $request->email)
            ->where('id', '!=', $request->user_id)
            ->exists();

        if ($emailExistsInDonors || $emailExistsInFoundations) {
            return response()->json(['message' => 'Email is already in use by another user'], 400);
        }

        $user->email = $request->email;
    }

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

public function forgotPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
    ]);

    $user = Donor::where('email', $request->email)->first()
        ?? Foundation::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'No user found with this email'], 404);
    }

    $token = Str::random(64);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => Hash::make($token),
            'created_at' => now(),
        ]
    );

    // Send email with token
    Mail::to($user->email)->send(new ResetPasswordMail($token, $user->email));

    return response()->json(['message' => 'Chick your email!! Reset link sent to your email.']);
}


// ------------------------------------------------------------

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'token' => 'required',
        'password' => 'required|string|min:8|confirmed', // إضافة التحقق من الطول
    ]);

    $reset = DB::table('password_reset_tokens')
        ->where('email', $request->email)
        ->first();

    if (!$reset || !Hash::check($request->token, $reset->token)) {
        return response()->json(['message' => 'Invalid token'], 400);
    }

    $user = Donor::where('email', $request->email)->first()
        ?? Foundation::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    DB::table('password_reset_tokens')->where('email', $request->email)->delete();

    return response()->json(['message' => 'Password has been reset successfully']);
}
}
