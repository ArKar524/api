<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Requests\UpdateTripApplicationRequest;
use App\Models\TripApplication;
use App\Models\TripApplicationEvent;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;
use Illuminate\Support\Carbon;

class TripApplicationController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['start_at', 'created_at'];

        $query = TripApplication::query()
            ->where('owner_id', $request->user()->id)
            ->applyFilters($request->filters(), AllowedFilters::tripApplicationOwner())
            ->applySearch($request->search(), ['pickup_location', 'dropoff_location']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        $sort['column'] ? $query->orderBy($sort['column'], $sort['direction']) : $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Owner trip applications fetched.');
    }

    public function update(UpdateTripApplicationRequest $request, TripApplication $tripApplication)
    {
        if ((int) $tripApplication->owner_id !== (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Forbidden.',
                'errors' => null,
            ], 403);
        }

        $validated = $request->validated();
        $statusChanged = array_key_exists('status', $validated) && $validated['status'] !== $tripApplication->status;

        $startAt = $validated['start_at'] ?? $tripApplication->start_at;
        $endAt = $validated['end_at'] ?? $tripApplication->end_at;

        if ($startAt && $endAt && Carbon::parse($endAt)->lessThanOrEqualTo(Carbon::parse($startAt))) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Validation failed.',
                'errors' => [
                    'end_at' => ['The end_at must be a date after start_at.'],
                ],
            ], 422);
        }

        $tripApplication->fill([
            'status' => $validated['status'] ?? $tripApplication->status,
            'total_amount' => $validated['total_amount'] ?? $tripApplication->total_amount,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'pickup_location' => $validated['pickup_location'] ?? $tripApplication->pickup_location,
            'dropoff_location' => $validated['dropoff_location'] ?? $tripApplication->dropoff_location,
            'contract_terms' => $validated['contract_terms'] ?? $tripApplication->contract_terms,
        ]);
        $tripApplication->save();

        if ($statusChanged) {
            $eventType = match ($tripApplication->status) {
                'active' => 'started',
                'completed' => 'completed',
                'dispute' => 'dispute',
                default => null,
            };

            if ($eventType) {
                TripApplicationEvent::create([
                    'trip_application_id' => $tripApplication->id,
                    'type' => $eventType,
                    'notes' => 'Trip application status updated to ' . $tripApplication->status . '.',
                ]);
            }
        }

        return ApiResponse::success($tripApplication->fresh(), 'Trip application updated.');
    }
}
