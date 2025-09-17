<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // $this->authorize('viewAny', User::class); // Optional: create policy

        $users = User::with(['roles', 'permissions'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'filters' => $request->only(['search']),
            'can' => [
                'create_users' => Auth::user()->can('create_users'),
                'edit_users' => Auth::user()->can('edit_users'),
                'delete_users' => Auth::user()->can('delete_users'),
                'manage_roles' => Auth::user()->can('manage_roles'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'store_id' => Auth::user()->store_id,
        ]);

        if (isset($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        return redirect()->back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|min:8|confirmed',
            'store_id' => 'required|exists:stores,id',
            'roles' => 'array',
            'roles.*' => 'exists:roles,name',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'store_id' => $validated['store_id'],
        ];
        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }
        $user->update($updateData);

        // Update roles
        if (isset($validated['roles'])) {
            $user->syncRoles($validated['roles']);
        }

        return redirect()->back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting self
        if ($user->id === Auth::user()->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        // Prevent deleting Super Admin (optional)
        if ($user->hasRole('Super Admin')) {
            return redirect()->back()->with('error', 'Super Admin tidak dapat dihapus.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->back()->with('success', 'Role berhasil diperbarui.');
    }

    public function revokeRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->removeRole($validated['role']);

        return redirect()->back()->with('success', 'Role berhasil dicabut.');
    }
}
