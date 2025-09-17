<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class StoreProfileController extends Controller
{
    /**
     * Menampilkan form profil toko.
     */
    public function edit(Request $request)
    {
        // Ambil data toko berdasarkan user yang sedang login
        $store = Auth::user()->store;

        return Inertia::render('StoreProfile/Edit', [
            'store' => $store,
        ]);
    }

    /**
     * Memperbarui data profil toko.
     */
    public function update(Request $request)
    {
        $store = Auth::user()->store;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'logo_path' => 'nullable|image|max:1024', // Validasi untuk upload logo
        ]);

        $store->update($validated);

        // Logika untuk upload logo bisa ditambahkan di sini
        if ($request->file('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $store->update(['logo_path' => $path]);
        }

        return Redirect::route('store.profile.edit')->with('success', 'Profil toko berhasil diperbarui.');
    }
}
