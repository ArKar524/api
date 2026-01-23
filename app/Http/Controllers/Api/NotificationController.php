<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexQueryRequest;
use App\Http\Resources\NotificationResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(IndexQueryRequest $request)
    {
        $user = $request->user();

        $status = $request->input('filter.status');
        if ($status === 'unread') {
            $query = $user->unreadNotifications();
        } elseif ($status === 'read') {
            $query = $user->readNotifications();
        } else {
            $query = $user->notifications();
        }

        $query->latest();

        $paginator = $query->paginate($request->perPage())->appends($request->query());
        $items = NotificationResource::collection($paginator->items())->toArray($request);

        return ApiResponse::paginate($paginator, 'Notifications fetched successfully.', $items);
    }

    public function delete(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();

        $notification->delete();

        return ApiResponse::success(new NotificationResource($notification->fresh()), 'Notification deleted.');
    }

    public function unreadCount(Request $request)
    {
        $count = $request->user()->unreadNotifications()->count();

        return ApiResponse::success(['count' => $count], 'Unread notifications fetched.');
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return ApiResponse::success(new NotificationResource($notification->fresh()), 'Notification marked as read.');
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return ApiResponse::success(['count' => 0], 'All notifications marked as read.');
    }
}
