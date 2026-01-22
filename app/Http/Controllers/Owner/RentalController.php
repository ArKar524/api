<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Models\Rental;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;

class RentalController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['start_at', 'created_at'];

        $query = Rental::query()
            ->where('owner_id', $request->user()->id)
            ->applyFilters($request->filters(), AllowedFilters::rentalOwner())
            ->applySearch($request->search(), ['pickup_location', 'dropoff_location']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        $sort['column'] ? $query->orderBy($sort['column'], $sort['direction']) : $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Owner rentals fetched.');
    }
}
