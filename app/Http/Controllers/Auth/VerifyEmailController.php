<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403);
        }

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if (Auth::id() === $id) {
            return redirect()->route('posts.index');
        }

        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('status', __('Your email has been verified. Please log in to continue.'));
        }

        return redirect()->route('posts.index')
            ->with('status', __('This email address has been verified.'));
    }
}
