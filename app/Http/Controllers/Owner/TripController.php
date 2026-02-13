<?php

namespace App\Http\Controllers\Owner;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Models\Trip;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;
use App\Services\TripFlowService;

class TripController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at'];

        $query = Trip::query()
            ->where('owner_id', $request->user()->id)
            ->applyFilters($request->filters(), AllowedFilters::tripOwner())
            ->applySearch($request->search(), ['notes']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        $sort['column'] ? $query->orderBy($sort['column'], $sort['direction']) : $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Owner trips fetched.');
    }

    public function store(StoreTripRequest $request, TripFlowService $tripFlowService)
    {
        try {
            $trip = $tripFlowService->createTrip($request->user(), $request->validated());
        } catch (BusinessRuleException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status());
        }

        return ApiResponse::success($trip, 'Trip posted successfully.', 201);
    }

    public function update(
        UpdateTripRequest $request,
        Trip $trip,
        TripFlowService $tripFlowService
    )
    {
        try {
            $trip = $tripFlowService->updateTrip($request->user(), $trip, $request->validated());
        } catch (BusinessRuleException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status());
        }

        return ApiResponse::success($trip, 'Trip updated successfully.');
    }
}
