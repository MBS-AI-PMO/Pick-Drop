<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        User::ensureSuperAdminExists();
        $user = Auth::user()->fresh();
        $canManageAdmins = $user?->canManageAdmins() ?? false;
        $admins = $canManageAdmins
            ? User::whereIn('role', ['Admin', 'Super Admin'])->orderByRaw("role = 'Super Admin' DESC")->orderBy('name')->get()
            : collect();

        return view('pickdrop.profile.index', compact('user', 'admins', 'canManageAdmins'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($user->id)],
        ]);

        try {
            $user->update([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone') ?: null,
            ]);

            return redirect()
                ->route('general.profile')
                ->with('success', 'Profile updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update profile', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = Auth::user();
            $user->update([
                'password' => $request->input('password'),
            ]);

            return redirect()
                ->route('general.profile')
                ->with('success', 'Password updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update password', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }

    public function storeAdmin(Request $request)
    {
        $request->validateWithBag('createAdmin', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $admin = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone') ?: null,
                'password' => $request->input('password'),
                'role' => 'Admin',
                'status' => 'Active',
            ]);

            Notification::create([
                'title' => 'New Admin',
                'message' => $admin->name . ' has been added as Admin.',
                'type' => 'success',
            ]);

            return redirect()
                ->route('general.profile')
                ->with('success', 'Admin created successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to create admin', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create admin: ' . $e->getMessage());
        }
    }

    public function updateAdmin(Request $request, User $user)
    {
        if ($user->isSuperAdmin() || $user->role !== 'Admin') {
            return redirect()
                ->route('general.profile')
                ->with('error', 'This account cannot be edited here.');
        }

        $request->validateWithBag('editAdmin', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($user->id)],
            'status' => ['nullable', 'in:Active,Inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        try {
            $data = [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone') ?: null,
                'status' => $request->input('status', $user->status),
            ];

            if ($request->filled('password')) {
                $data['password'] = $request->input('password');
            }

            $user->update($data);

            Notification::create([
                'title' => 'Admin Updated',
                'message' => $user->name . ' admin profile has been updated.',
                'type' => 'info',
            ]);

            return redirect()
                ->route('general.profile')
                ->with('success', 'Admin updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update admin', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update admin: ' . $e->getMessage());
        }
    }

    public function destroyAdmin(User $user)
    {
        if ($user->isSuperAdmin() || $user->id === Auth::id()) {
            return redirect()
                ->route('general.profile')
                ->with('error', 'Super Admin cannot be deleted.');
        }

        if ($user->role !== 'Admin') {
            return redirect()
                ->route('general.profile')
                ->with('error', 'Only Admin accounts can be removed here.');
        }

        try {
            $name = $user->name;
            $user->delete();

            Notification::create([
                'title' => 'Admin Removed',
                'message' => $name . ' has been removed.',
                'type' => 'danger',
            ]);

            return redirect()
                ->route('general.profile')
                ->with('success', 'Admin removed successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete admin', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('general.profile')
                ->with('error', 'Failed to remove admin: ' . $e->getMessage());
        }
    }
}
