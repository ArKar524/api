<?php

namespace App\Http\Controllers\Driver;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\StoreDriverTripApplicationRequest;
use App\Models\Trip;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;
use App\Services\TripFlowService;

class TripApplicationController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at'];
        $driverId = $request->user()->id;

        $query = Trip::query()
            ->where(function ($q) use ($driverId) {
                $q->where('driver_id', $driverId)
                    ->orWhere(function ($sub) {
                        $sub->whereNull('driver_id')
                            ->where('status', 'pending');
                    });
            })
            ->applyFilters($request->filters(), AllowedFilters::tripDriver())
            ->applySearch($request->search(), ['notes']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        $sort['column'] ? $query->orderBy($sort['column'], $sort['direction']) : $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Driver trips fetched.');
    }

    public function store(StoreDriverTripApplicationRequest $request, TripFlowService $tripFlowService)
    {
        $trip = Trip::query()->findOrFail($request->validated()['trip_id']);
        $existingApplication = $trip->tripApplication()->first();
        $alreadyApplied = $existingApplication
            && (int) $existingApplication->driver_id === (int) $request->user()->id;

        try {
            $tripApplication = $tripFlowService->apply($request->user(), $trip);
        } catch (BusinessRuleException $e) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status());
        }

        $message = $alreadyApplied
            ? 'Trip application already submitted.'
            : 'Trip application submitted.';

        return ApiResponse::success($tripApplication, $message, $alreadyApplied ? 200 : 201);
    }
}
