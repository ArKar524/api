<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Models\Car;
use Inertia\Inertia;
use Inertia\Response;

class AdminCarPageController extends Controller
{
    public function index(IndexQueryRequest $request): Response
    {
        $allowedSorts = ['created_at', 'daily_rate', 'year'];

        $query = Car::query()->with('owner');

        $status = $request->input('filter.status');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $approvalStatus = $request->input('filter.approval_status');
        if ($approvalStatus !== null && $approvalStatus !== '') {
            $query->where('approval_status', $approvalStatus);
        }

        $ownerId = $request->input('filter.owner_id');
        if ($ownerId !== null && $ownerId !== '') {
            $query->where('owner_id', $ownerId);
        }

        $search = $request->search();
        if ($search) {
            $like = '%' . str_replace('%', '\\%', $search) . '%';
            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('title', 'like', $like)
                    ->orWhere('make', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('license_plate', 'like', $like)
                    ->orWhereHas('owner', function ($ownerQuery) use ($like) {
                        $ownerQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    });
            });
        }

        $sort = $request->sortFieldAndDirection($allowedSorts);
        if ($sort['column']) {
            $query->orderBy($sort['column'], $sort['direction']);
        } else {
            $query->latest();
        }

        $cars = $query->paginate($request->perPage())->appends($request->query());

        return Inertia::render('Admin/Cars/Index', [
            'cars' => $cars,
            'filters' => [
                'q' => $request->input('q'),
                'status' => $status,
                'approval_status' => $approvalStatus,
                'owner_id' => $ownerId,
                'sort' => $request->input('sort'),
                'per_page' => $request->input('per_page'),
            ],
        ]);
    }

    public function show(int $id): Response
    {
        $car = Car::query()
            ->with(['owner', 'photos', 'documents'])
            ->findOrFail($id);

        return Inertia::render('Admin/Cars/Show', [
            'car' => $car,
        ]);
    }
}
