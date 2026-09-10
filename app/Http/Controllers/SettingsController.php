<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('settings.edit');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20'],
        ]);

        $request->user()->update($validated);

        return redirect()->route('settings.edit')->with('status', 'Profile updated');
    }
}
