<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Models\RentalRequest;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;

class RentalRequestController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at'];

        $query = RentalRequest::query()
            ->where('owner_id', $request->user()->id)
            ->applyFilters($request->filters(), AllowedFilters::rentalRequestOwner())
            ->applySearch($request->search(), ['notes']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        $sort['column'] ? $query->orderBy($sort['column'], $sort['direction']) : $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Owner rental requests fetched.');
    }
}
