<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Support\AppPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::latest()->paginate(AppPagination::PER_PAGE);

        return view('pickdrop.notifications.index', compact('notifications'));
    }

    public function show(Notification $notification): View
    {
        $notification->markAsRead();

        return view('pickdrop.notifications.show', compact('notification'));
    }

    public function markRead(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $notification->markAsRead();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => Notification::unreadCount(),
            ]);
        }

        return redirect()->back();
    }

    public function destroy(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $notificationId = $notification->id;
        $notification->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => Notification::unreadCount(),
            ]);
        }

        if (str_contains((string) url()->previous(), '/notifications/'.$notificationId)) {
            return redirect()->route('notifications.index');
        }

        return redirect()->back();
    }

    public function clear(): RedirectResponse
    {
        Notification::query()->delete();

        return redirect()->back()->with('success', 'All notifications cleared successfully.');
    }
}
