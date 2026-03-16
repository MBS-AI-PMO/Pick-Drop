<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = User::where('role', '!=', 'Admin');

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

            $users = $query->paginate(10)->withQueryString();

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
            'role'     => 'required|string',
            'status'   => 'nullable|string',
            'details'  => 'nullable|string',
        ]);

        try {
            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'status'   => $request->status ?? 'Active',
                'details'  => $request->details,
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
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role'    => 'required|string',
            'status'  => 'nullable|string',
            'details' => 'nullable|string',
        ]);

        $data = $request->only(['name', 'email', 'role', 'status', 'details']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        try {
            $user->update($data);

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
        try {
            $user->delete();
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
