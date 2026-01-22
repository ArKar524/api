<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarRequest;
use App\Models\Car;
use App\Services\CarApprovalService;

class CarController extends Controller
{
    public function store(StoreCarRequest $request, CarApprovalService $service)
    {
        $this->authorize('create', Car::class);

        $car = $service->createCar($request->user(), $request->validated());

        return response()->json([
            'success' => true,
            'data' => $car,
            'message' => 'Car submitted for review.',
            'errors' => null,
        ], 201);
    }
}
