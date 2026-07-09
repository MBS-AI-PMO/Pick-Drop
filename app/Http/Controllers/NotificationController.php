<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(20);

        return view('pickdrop.notifications.index', compact('notifications'));
    }
    public function clear()
{
    Notification::query()->delete();

    return redirect()->back()->with('success', 'All notifications cleared successfully.');
}
}