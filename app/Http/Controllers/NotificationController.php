<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Support\AppPagination;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(AppPagination::PER_PAGE);

        return view('pickdrop.notifications.index', compact('notifications'));
    }
    public function clear()
{
    Notification::query()->delete();

    return redirect()->back()->with('success', 'All notifications cleared successfully.');
}
}