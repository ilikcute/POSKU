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
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'tax' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:1024', // Validasi untuk upload logo
            'heroimage' => 'nullable|image|max:2048', // Validasi untuk upload hero image
            'favicon' => 'nullable|image|max:512', // Validasi untuk upload favicon
        ]);

        $store->update([
            'name' => $validated['name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'website' => $validated['website'],
            'tax' => $validated['tax'],
        ]);

        // Upload logo
        if ($request->file('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $store->update(['logo_path' => $path]);
        }

        // Upload hero image
        if ($request->file('heroimage')) {
            $path = $request->file('heroimage')->store('heroimages', 'public');
            $store->update(['heroimage_path' => $path]);
        }

        // Upload favicon
        if ($request->file('favicon')) {
            $path = $request->file('favicon')->store('favicons', 'public');
            $store->update(['favicon_path' => $path]);
        }

        return Redirect::route('store.profile.edit')->with('success', 'Profil toko berhasil diperbarui.');
    }
}
