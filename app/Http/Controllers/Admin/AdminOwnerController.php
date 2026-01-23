<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\ApiResponse;

class AdminOwnerController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $allowedSorts = ['created_at', 'name', 'email'];

        $query = User::query()
            ->where('role', 'owner')
            ->with('latestOwnerVerification');

        $status = $request->input('filter.status');
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $verificationStatus = $request->input('filter.verification_status');
        if ($verificationStatus !== null && $verificationStatus !== '') {
            $query->whereHas('ownerVerifications', function ($subQuery) use ($verificationStatus) {
                $subQuery->where('status', $verificationStatus);
            });
        }

        $search = $request->search();
        if ($search) {
            $like = '%' . str_replace('%', '\\%', $search) . '%';
            $query->where(function ($subQuery) use ($like) {
                $subQuery->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        $sort = $request->sortFieldAndDirection($allowedSorts);
        if ($sort['column']) {
            $query->orderBy($sort['column'], $sort['direction']);
        } else {
            $query->latest();
        }

        $paginator = $query->paginate($request->perPage())->appends($request->query());

        $items = UserResource::collection($paginator->items())->toArray($request);

        return ApiResponse::paginate($paginator, 'Owners fetched successfully.', $items);
    }

    public function show(int $id)
    {
        $owner = User::query()
            ->where('role', 'owner')
            ->with(['ownerVerifications.files'])
            ->findOrFail($id);

        return ApiResponse::success(new UserResource($owner), 'Owner fetched successfully.');
    }
}
