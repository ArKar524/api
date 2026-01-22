<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexQueryRequest;
use App\Models\Car;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;

class CarListController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at', 'daily_rate'];

        $query = Car::query()
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->applyFilters($request->filters(), AllowedFilters::carPublic())
            ->applySearch($request->search(), ['title', 'make', 'model', 'license_plate']);

        $sort = $request->sortFieldAndDirection($allowedSorts);
        if ($sort['column']) {
            $query->orderBy($sort['column'], $sort['direction']);
        } else {
            $query->latest();
        }

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        return ApiResponse::paginate($paginator, 'Cars fetched successfully.');
    }
}
