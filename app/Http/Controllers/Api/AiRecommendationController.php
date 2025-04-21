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
        $result = exec("python3 " . storage_path('app/ai_model/predict.py') . " " . escapeshellarg($inputData));
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
        $recommendedRequests = DonationRequest::with('foundation', 'donations')
            ->whereIn('id', $recommendedIds)
            ->limit($validated['max_results'] ?? 5)
            ->get();

        // معالجة الطلبات وإضافة الحقول المطلوبة
        $recommendations = $recommendedRequests->map(function ($request) {
            $totalDonated = $request->donations->sum('amount');
            $remainingAmount = $request->required_amount - $totalDonated;
            $percentage = $request->required_amount > 0 ? round(($totalDonated / $request->required_amount) * 100) : 0;

            return [
                'id' => $request->id,
                'title' => $request->title,
                'description' => $request->description,
                'required_donation' => $request->reqiured_donation, // تأكد أن اسم الحقل صحيح في DB
                'required_amount' => $request->required_amount,
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

        // إرجاع النتيجة في صيغة JSON
        return response()->json([
            'status' => 'success',
            'recommendations' => $recommendations
        ]);
    }
    #-----------------------------------------------------------
    public function recommendations_Ai($donorId)
    {
        $donor = Donor::find($donorId);

        if (!$donor) {
            return response()->json([
                'success' => false,
                'message' => 'Donor not Found '
            ], 404);
        }

        // استرجاع الطلبات بناءً على الموقع مع تضمين file_path
        $requests = DonationRequest::with(['foundation', 'donations'])
            ->where('location', $donor->location)
            ->where('reqiured_donation', $donor->preferred_donation) // اضفنا الفلترة على نوع التبرع
            ->get();
        // إضافة الإحصائيات لكل طلب تبرع
        $requests = $requests->map(function ($request) {
            // حساب إجمالي التبرعات
            $totalDonated = $request->donations->sum('amount');

            // حساب المبلغ المتبقي
            $remainingAmount = $request->required_amount - $totalDonated;

            // حساب النسبة المئوية المكتملة
            $percentage = $request->required_amount > 0 ? round(($totalDonated / $request->required_amount) * 100) : 0;

            // تنسيق البيانات النهائية
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
                'stats' => [
                    'total_donated' => $totalDonated,
                    'remaining_amount' => $remainingAmount,
                    'percentage_completed' => $percentage
                ],
                'foundation' => $request->foundation ? [
                    'id' => $request->foundation->id,
                    'foundation_name' => $request->foundation->foundation_name
                ] : null
            ];
        });

        // إرجاع النتيجة مع الإحصائيات
        return response()->json([
            'recommendations' => $requests
        ]);
    }
}
