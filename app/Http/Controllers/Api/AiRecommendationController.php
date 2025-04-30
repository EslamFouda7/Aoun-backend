<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\Foundation;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
#--------------------------------------------------------
class AiRecommendationController extends Controller
{
    public function recommend(Request $request)
    {
        //هنا بتتحقق من صلاحيات المستخدم
        $validated = $request->validate([
            'donor_id' => 'required|exists:donors,id',
            'max_results' => 'sometimes|integer|min:1|max:10'
        ]);
        #------------------------------------------------------------------
        //هنا بجيب بيانات المتبرع بناء على id  من data base
        $donor = Donor::find($validated['donor_id']);
        #وهنا بجيب id الطلب الى طلع من المودل
        $requests = DonationRequest::get();
        #-------------------------------------------------------------------
        //هنا بجهز البيانات الى هبعتها الى سكريب بايثون الخاص بالمودل
        $inputData = json_encode([
            'donor' => $donor->toArray(),
            'requests' => $requests->toArray(),
            'max_results' => $validated['max_results'] ?? 5
        ]);
        #------------------------------------------------------------------
        // استدعاء سكربت بايثون
        $result = exec("python3" . storage_path('app/ai_model/predict.py') . " " . escapeshellarg($inputData));
        #-----------------------------------------------------------------
        // إذا لم يتم الحصول على نتيجة من السكربت، نرجع خطأ
        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI model failed to respond'
            ]);
        }
        #--------------------------------------------------------------
        //  json عشان افك تشقير البيانات ال json_decode  هنا استخدمت
        $recommendedIds = json_decode($result, true);
        #------------------------------------------------------------
        // إذا لم يتم فك التشفير أو كانت النتيجة فارغة، نرجع خطأ
        if (!$recommendedIds || !is_array($recommendedIds)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid response from AI model'
            ]);
        }
        #------------------------------------------------------------
        // استرجاع الطلبات الموصى بها من قاعدة البيانات باستخدام الـ IDs التي أرجعها النموذج
        $recommendedRequests = DonationRequest::whereIn('id', $recommendedIds)
            ->limit($validated['max_results'] ?? 5)
            ->get();

        // إرجاع النتيجة في صيغة JSON
        return response()->json([
            'status' => 'success',
            'recommendations' => $recommendedRequests
        ]);
    }
    #-----------------------------------------------------------
    public function recommendations_Ai($donorId)
    {
        $donor = Donor::find($donorId);
        if (!$donor) {
            return response()->json([
                'success' => false,
                'message' => 'Donor not Found'
            ], 404);
        }
        $requests = DonationRequest::with(['foundation', 'donations'])
            ->where(function ($query) use ($donor) {
                $query->where('location', $donor->location)
                    ->orWhere('reqiured_donation', $donor->preferred_donation);
            })->get();
        //----------------------------------------------------------------
        $requests = $requests->map(function ($request) use ($donor) {
            $matchLocation = $request->location === $donor->location;
            $matchDonation = $request->reqiured_donation === $donor->preferred_donation;
            $matchPercentage = 0;

            if ($matchLocation) {
                $matchPercentage += rand(60, 80);
            }

            if ($matchDonation) {
                $matchPercentage += rand(50, 70);
            }


            $matchPercentage = min($matchPercentage, 100);
            $totalDonated = $request->donations->sum('amount');
            $remainingAmount = $request->required_amount - $totalDonated;
            $percentageCompleted = $request->required_amount > 0
                ? round(($totalDonated / $request->required_amount) * 100)
                : 0;
            //-------------------------------------------------------------------
            return [
                'id' => $request->id,
                'title' => $request->title,
                'description' => $request->description,
                'required_donation' => $request->reqiured_donation,
                'required_amount' => $request->required_amount,
                'file_path' => $request->file_path,
                'location' => $request->location,
                'created_at' => $request->created_at,
                'updated_at' => $request->updated_at,
                'match_percentage' => $matchPercentage,
                'stats' => [
                    'total_donated' => $totalDonated,
                    'remaining_amount' => $remainingAmount,
                    'percentage_completed' => $percentageCompleted
                ],
                'foundation' => $request->foundation ? [
                    'id' => $request->foundation->id,
                    'foundation_name' => $request->foundation->foundation_name
                ] : null
            ];
        })
            ->sortByDesc('match_percentage') // ترتيب حسب نسبة التطابق
            ->values();

        return response()->json([
            'recommendations' => $requests
        ]);
    }
}
