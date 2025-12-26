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
        $authorizations = Authorization::latest()->paginate(10);

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
                Rule::unique('authorizations'),
            ],
            'password' => 'required|string|min:4',
        ]);

        Authorization::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('shifts.authorizations.index')->with('success', 'Authorization berhasil ditambahkan.');
    }

    public function show(Authorization $authorization)
    {
        return Inertia::render('Shifts/Authorizations/Show', [
            'authorization' => $authorization,
        ]);
    }

    public function edit(Authorization $authorization)
    {
        return Inertia::render('Shifts/Authorizations/Edit', [
            'authorization' => $authorization,
        ]);
    }

    public function update(Request $request, Authorization $authorization)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('authorizations')->ignore($authorization->id),
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
        $authorization->delete();

        return redirect()->route('shifts.authorizations.index')->with('success', 'Authorization berhasil dihapus.');
    }
}
