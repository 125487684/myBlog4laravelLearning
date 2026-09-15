<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $verified = $request->user()->hasVerifiedEmail();

        if (! $request->expectsJson()) {
            return redirect()->route('verification.notice');
        }

        return response()->json(['verified' => $verified]);
    }
}
