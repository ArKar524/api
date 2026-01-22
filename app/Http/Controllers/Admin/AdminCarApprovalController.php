<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReviewCarRequest;
use App\Models\Car;
use App\Services\CarApprovalService;

class AdminCarApprovalController extends Controller
{
    public function review(AdminReviewCarRequest $request, int $id, CarApprovalService $service)
    {
        $car = Car::findOrFail($id);

        $this->authorize('review', $car);

        $updated = $service->reviewCar($car, $request->validated()['status'], $request->user(), $request->validated()['notes'] ?? null);

        return response()->json([
            'success' => true,
            'data' => $updated,
            'message' => 'Car review submitted.',
            'errors' => null,
        ]);
    }
}
