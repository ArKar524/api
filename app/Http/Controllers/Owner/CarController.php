<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\StoreCarRequest;
use App\Models\Car;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;
use App\Services\CarApprovalService;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at'];

        $query = Car::query()
            ->where('owner_id', $request->user()->id)
            ->applyFilters($request->filters(), AllowedFilters::carOwner())
            ->applySearch($request->search(), ['title', 'make', 'model', 'license_plate']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        $sort['column'] ? $query->orderBy($sort['column'], $sort['direction']) : $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Owner cars fetched.');
    }

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
