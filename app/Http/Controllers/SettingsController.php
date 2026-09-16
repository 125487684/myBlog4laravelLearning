<?php

namespace App\Http\Controllers;

use Closure;
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

    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $request->validateWithBag('email', [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id,
                function (string $attribute, mixed $value, Closure $fail) use ($user) {
                    if (strcasecmp($value, $user->email) === 0) {
                        $fail(__('New email must be different from the current email.'));
                    }
                },
            ],
            'current_password' => ['required', 'current_password'],
        ], [], ['current_password' => __('Current password')]);

        $user->forceFill([
            'email' => $request->email,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
