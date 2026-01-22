<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReviewVerificationRequest;
use App\Models\Verification;
use App\Services\VerificationService;

class AdminVerificationController extends Controller
{
    public function review(AdminReviewVerificationRequest $request, int $id, VerificationService $service)
    {
        $verification = Verification::findOrFail($id);

        $this->authorize('review', $verification);

        $updated = $service->reviewVerification($verification, $request->validated()['status'], $request->validated()['notes'] ?? null);

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Verification reviewed.',
            'errors' => null,
        ]);
    }
}
