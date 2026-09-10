<?php

namespace App\Http\Controllers;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed',
                function (string $attribute, mixed $value, Closure $fail) use ($request) {
                    if (Hash::check($value, $request->user()->password)) {
                        $fail(__('The new password must be different from the current password.'));
                    }
                },
            ],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return redirect()->route('settings.edit')->with('status_password', 'Password updated');
    }
}
