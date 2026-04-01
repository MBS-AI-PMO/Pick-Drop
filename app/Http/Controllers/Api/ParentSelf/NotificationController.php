<?php

namespace App\Http\Controllers\Api\ParentSelf;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class NotificationController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $notifications = AppNotification::where('user_id', $request->user()->id)
                ->orderByDesc('id')
                ->paginate(20);

            return $this->successResponse($notifications, 'Notifications');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to fetch notifications');
        }
    }

    public function markRead(Request $request, AppNotification $notification): JsonResponse
    {
        try {
            if ($notification->user_id !== $request->user()->id) {
                return $this->errorResponse('Not found', 404);
            }

            $notification->read_at = now();
            $notification->save();

            return $this->successResponse($notification, 'Notification marked as read');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark notification read');
        }
    }

    public function markAllRead(Request $request): JsonResponse
    {
        try {
            AppNotification::where('user_id', $request->user()->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return $this->successResponse(null, 'All notifications marked as read');
        } catch (Throwable $e) {
            return $this->handleException($e, 'Unable to mark all notifications read');
        }
    }
}

