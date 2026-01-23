<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class AdminOwnerPageController extends Controller
{
    public function index(IndexQueryRequest $request): Response
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

        $owners = $query->paginate($request->perPage())->appends($request->query());

        return Inertia::render('Admin/Owners/Index', [
            'owners' => $owners,
            'filters' => [
                'q' => $request->input('q'),
                'status' => $status,
                'verification_status' => $verificationStatus,
                'sort' => $request->input('sort'),
                'per_page' => $request->input('per_page'),
            ],
        ]);
    }

    public function show(int $id): Response
    {
        $owner = User::query()
            ->where('role', 'owner')
            ->with([
                'ownerVerifications' => function ($query) {
                    $query->latest('requested_at')->with('files');
                },
            ])
            ->findOrFail($id);

        return Inertia::render('Admin/Owners/Show', [
            'owner' => $owner,
        ]);
    }
}
