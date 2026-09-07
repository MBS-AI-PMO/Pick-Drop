<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Support\AppPagination;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginLog::query()
            ->with('user')
            ->latest('id');

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(AppPagination::PER_PAGE)->withQueryString();

        return view('pickdrop.login-logs.index', compact('logs'));
    }
}
