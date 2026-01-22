<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmitDriverKycRequest;
use App\Http\Requests\SubmitOwnerKycRequest;
use App\Models\Verification;
use App\Services\VerificationService;

class KycController extends Controller
{
    public function submitOwner(SubmitOwnerKycRequest $request, VerificationService $service)
    {
        $this->authorize('submitOwner', Verification::class);

        $verification = $service->submitOwnerKyc($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $verification,
            'message' => 'Owner KYC submitted successfully.',
            'errors' => null,
        ], 201);
    }

    public function submitDriver(SubmitDriverKycRequest $request, VerificationService $service)
    {
        $this->authorize('submitDriver', Verification::class);

        $verification = $service->submitDriverKyc($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $verification,
            'message' => 'Driver KYC submitted successfully.',
            'errors' => null,
        ], 201);
    }
}
