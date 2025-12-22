<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Laravel 12 style controller middleware.
     * Implement HasMiddleware + static middleware() untuk menempel middleware per-action.
     */
    public static function middleware(): array
    {
        return [
            // Kalau permission Anda namanya berbeda, sesuaikan string-nya.
            new Middleware('check.permission:view_users', only: ['index']),
            new Middleware('check.permission:create_users', only: ['store']),
            new Middleware('check.permission:edit_users', only: ['update']),
            new Middleware('check.permission:delete_users', only: ['destroy']),

            // Khusus manajemen role
            new Middleware('check.permission:manage_roles', only: ['assignRole', 'revokeRole']),
        ];
    }

    public function index(Request $request)
    {
        $users = User::query()
            ->with(['roles', 'permissions'])
            ->when($request->string('search')->toString(), function ($query, $search) {
                // Grouping penting agar orWhere tidak "melebar"
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $authUser = $request->user();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => Role::all(),
            'permissions' => Permission::all(),
            'filters' => $request->only(['search']),
            'can' => [
                'create_users' => $authUser->can('create_users'),
                'edit_users' => $authUser->can('edit_users'),
                'delete_users' => $authUser->can('delete_users'),
                'manage_roles' => $authUser->can('manage_roles'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'store_id' => $request->user()->store_id,
        ]);

        if (!empty($validated['roles'])) {
            $user->assignRole($validated['roles']);
        }

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'min:8', 'confirmed'],

            // Catatan: ini sensitif. Kalau user biasa tidak boleh pindah store,
            // sebaiknya store_id hanya boleh untuk role tertentu.
            'store_id' => ['required', 'exists:stores,id'],

            'roles' => ['sometimes', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'store_id' => $validated['store_id'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update roles (kalau field roles dikirim)
        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles'] ?? []);
        }

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        // Prevent deleting self
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        // Prevent deleting Super Admin (opsional)
        if ($user->hasRole('Super Admin')) {
            return back()->with('error', 'Super Admin tidak dapat dihapus.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }

    public function assignRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,name'],
        ]);

        $user->syncRoles($validated['roles']);

        return back()->with('success', 'Role berhasil diperbarui.');
    }

    public function revokeRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->removeRole($validated['role']);

        return back()->with('success', 'Role berhasil dicabut.');
    }
}
