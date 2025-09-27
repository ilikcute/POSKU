<?php

namespace App\Http\Controllers;

use App\Models\Authorization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AuthorizationController extends Controller
{
    public function index()
    {
        $authorizations = Authorization::where('store_id', auth()->user()->store_id)
            ->latest()
            ->paginate(10);

        return Inertia::render('Shifts/Authorizations/Index', [
            'authorizations' => $authorizations,
        ]);
    }

    public function create()
    {
        return Inertia::render('Shifts/Authorizations/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('authorizations')->where('store_id', auth()->user()->store_id),
            ],
            'password' => 'required|string|min:4',
        ]);

        Authorization::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'store_id' => auth()->user()->store_id,
        ]);

        return redirect()->route('shifts.authorizations.index')->with('success', 'Authorization berhasil ditambahkan.');
    }

    public function show(Authorization $authorization)
    {
        // Ensure the authorization belongs to the user's store
        if ($authorization->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        return Inertia::render('Shifts/Authorizations/Show', [
            'authorization' => $authorization,
        ]);
    }

    public function edit(Authorization $authorization)
    {
        // Ensure the authorization belongs to the user's store
        if ($authorization->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        return Inertia::render('Shifts/Authorizations/Edit', [
            'authorization' => $authorization,
        ]);
    }

    public function update(Request $request, Authorization $authorization)
    {
        // Ensure the authorization belongs to the user's store
        if ($authorization->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('authorizations')
                    ->where('store_id', auth()->user()->store_id)
                    ->ignore($authorization->id),
            ],
            'password' => 'nullable|string|min:4',
        ]);

        $data = [
            'name' => $request->name,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $authorization->update($data);

        return redirect()->route('shifts.authorizations.index')->with('success', 'Authorization berhasil diperbarui.');
    }

    public function destroy(Authorization $authorization)
    {
        // Ensure the authorization belongs to the user's store
        if ($authorization->store_id !== auth()->user()->store_id) {
            abort(403);
        }

        $authorization->delete();

        return redirect()->route('shifts.authorizations.index')->with('success', 'Authorization berhasil dihapus.');
    }
}
