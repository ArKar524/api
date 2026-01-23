<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexQueryRequest;
use App\Models\Car;
use App\Support\ApiResponse;
use App\Support\QueryFilters\AllowedFilters;
use Illuminate\Http\Request;

class CarListController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at', 'daily_rate'];

        $user = $request->user('sanctum');

        $query = Car::query()
            ->with(['owner', 'photos', 'documents']);

        if ($user?->role === 'owner') {
            $query->where('owner_id', $user->id);
        } elseif ($user?->role !== 'admin') {
            $query->where('status', 'active')
                ->where('approval_status', 'approved');
        }

        $query
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

    public function show(Request $request, string $car)
    {
        $user = $request->user('sanctum');

        $query = Car::query();

        if ($user?->role === 'owner') {
            $query->where('owner_id', $user->id);
        } elseif ($user?->role !== 'admin') {
            $query->where('status', 'active')
                ->where('approval_status', 'approved');
        }

        $carModel = $query->findOrFail($car);

        return ApiResponse::success($carModel, 'Car fetched successfully.');
    }
}
