<?php

namespace App\Http\Controllers;
use App\Models\Foundation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
class FoundationController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'foundation_name' => 'required|string',
            'type_of_foundation' => 'required|string',
            'email' => 'required|email|unique:foundations',
            'phone' => 'required|string',
            'password' => 'required|string|min:8',
            'required_donation' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $foundation = Foundation::create([
            'foundation_name' => $request->foundation_name,
            'type_of_foundation' => $request->type_of_foundation,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'required_donation' => $request->required_donation,
            'location' => $request->location,
        ]);

        return response()->json(['message' => 'Foundation registered successfully', 'foundation' => $foundation], 201);
    }
    #---------------------------------------------------------------------------------------------------
    public function show($id)
    {
        $foundation = Foundation::find($id);

        if ($foundation) {
            return response()->json(['foundation' => $foundation], 200);
        }

        return response()->json(['error' => 'Foundation not found'], 404);
    }
}
