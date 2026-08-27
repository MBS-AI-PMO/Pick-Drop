<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use App\Support\AppPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::whereNotIn('role', ['Admin', 'Super Admin']);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('details', 'like', "%{$search}%");
                });
            }

            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            $users = $query->paginate(AppPagination::PER_PAGE)->withQueryString();

            return view('pickdrop.users.index', compact('users'));
        } catch (\Throwable $e) {
            Log::error('Failed to load users index', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to load users: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role'     => ['required', 'in:Driver,Parent,Student'],
            'status'   => 'nullable|string',
            'details'  => 'nullable|string',
        ]);

        try {
            $user = User::create([
    'name'     => $request->name,
    'email'    => $request->email,
    'password' => Hash::make($request->password),
    'role'     => $request->role,
    'status'   => $request->status ?? 'Active',
    'details'  => $request->details,
]);
Notification::create([
    'title' => 'New User',
    'message' => $user->name . ' has been added.',
    'type' => 'success',
]);

            return redirect()->route('users.index')->with('success', 'User added successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to create user', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    public function update(Request $request, User $user)
    {
        if ($user->isPanelAdmin()) {
            return redirect()->route('users.index')->with('error', 'Admin accounts cannot be updated from here.');
        }
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'    => ['required', 'in:Driver,Parent,Student'],
            'status'  => 'nullable|string',
            'details' => 'nullable|string',
        ]);

        $data = $request->only(['name', 'email', 'role', 'status', 'details']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        try {
            $user->update($data);
            Notification::create([
    'title' => 'User Updated',
    'message' => $user->name . ' profile has been updated.',
    'type' => 'info',
]);

            return redirect()->route('users.index')->with('success', 'User updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to update user', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        if ($user->isPanelAdmin()) {
            return redirect()->route('users.index')->with('error', 'Admin accounts cannot be deleted from here.');
        }
        try {
            $user->delete();
            Notification::create([
    'title' => 'User Deleted',
    'message' => $user->name . ' has been deleted.',
    'type' => 'danger',
]);
            return redirect()->route('users.index')->with('success', 'User deleted successfully!');
        } catch (\Throwable $e) {
            Log::error('Failed to delete user', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('users.index')->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }
}
