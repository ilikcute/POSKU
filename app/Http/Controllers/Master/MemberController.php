<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Member::where('store_id', $user->store_id);

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('member_code', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            });
        }

        $members = $query->latest()->paginate(10)->withQueryString();

        // Get store data for member card
        $store = Store::find($user->store_id);

        return Inertia::render('Master/Members/Index', [
            'members' => $members,
            'store' => $store,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'member_code' => 'required|string|unique:members,member_code,NULL,id,store_id,'.$user->store_id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'points' => 'nullable|integer|min:0',
        ]);

        Member::create([
            'store_id' => $user->store_id,
            'member_code' => $validated['member_code'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'address' => $validated['address'],
            'points' => $validated['points'] ?? 0,
        ]);

        return redirect()->route('master.members.index')
            ->with('success', 'Member berhasil ditambahkan.');
    }

    public function show(Member $member)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        //
    }

    public function update(Request $request, Member $member)
    {
        $user = Auth::user();

        // Ensure user can only update members from their store
        if ($member->store_id !== $user->store_id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'member_code' => 'required|string|unique:members,member_code,'.$member->id.',id,store_id,'.$user->store_id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'points' => 'nullable|integer|min:0',
        ]);

        $member->update($validated);

        return redirect()->route('master.members.index')
            ->with('success', 'Member berhasil diupdate.');
    }

    public function destroy(Member $member)
    {
        $user = Auth::user();

        // Ensure user can only delete members from their store
        if ($member->store_id !== $user->store_id) {
            abort(403, 'Unauthorized');
        }

        $member->delete();

        return redirect()->route('master.members.index')
            ->with('success', 'Member berhasil dihapus.');
    }

    public function generateCard(Member $member)
    {
        $user = Auth::user();

        // Ensure user can only generate cards for members from their store
        if ($member->store_id !== $user->store_id) {
            abort(403, 'Unauthorized');
        }

        $store = Store::find($user->store_id);

        return response()->json([
            'member' => $member,
            'store' => $store,
        ]);
    }
}
