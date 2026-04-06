<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        return Inertia::render('Profile/EditUmkm', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'business_name'   => ['required', 'string', 'max:255'],
            'monthly_revenue' => ['required', 'numeric', 'min:100000'],
        ]);

        $user->update($validated);

        return redirect()->route('dashboard')
            ->with('success', 'Data UMKM berhasil diperbarui.');
    }
}
