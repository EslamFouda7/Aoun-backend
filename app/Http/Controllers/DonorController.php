<?php

namespace App\Http\Controllers;
use App\Models\Donor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class DonorController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string',
            'email' => 'required|email|unique:donors',
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
            'preferred_donation' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $donor = Donor::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'preferred_donation' => $request->preferred_donation,
            'location' => $request->location,
        ]);

        return response()->json(['message' => 'Donor registered successfully', 'donor' => $donor], 201);
    }
   #-----------------------------------------------------------------------------------------------------
   public function show($id)
   {
       $donor = Donor::find($id);

       if ($donor) {
           return response()->json(['donor' => $donor], 200);
       }

       return response()->json(['error' => 'Donor not found'], 404);
   }
    
}
