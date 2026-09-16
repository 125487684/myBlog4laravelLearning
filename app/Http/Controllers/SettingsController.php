<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        $tab = $request->query('tab', 'profile');

        if ($tab === 'admin' && ! Gate::allows('admin')) {
            $tab = 'profile';
        }

        return view('settings.edit', ['tab' => $tab]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('settings.edit')->with('status_profile', 'Profile updated');
    }
}
