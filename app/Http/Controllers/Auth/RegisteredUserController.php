<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. CARI TOKO UTAMA
        $mainStore = Store::where('is_main_store', true)->firstOrFail();

        // 2. BUAT USER BARU DENGAN STORE_ID
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'store_id' => $mainStore->id, // <-- BAGIAN INI SANGAT PENTING
        ]);

        // 3. BERI ROLE 'KASIR' SECARA OTOMATIS
        $cashierRole = Role::where('name', 'Kasir')->first();
        if ($cashierRole) {
            $user->assignRole($cashierRole);
        }

        event(new Registered($user));

        Auth::login($user);

        // 4. REDIRECT KE DASHBOARD DENGAN NOTIFIKASI
        return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang.');
    }
}
