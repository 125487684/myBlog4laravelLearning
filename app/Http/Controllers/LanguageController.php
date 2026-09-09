<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function switch(string $locale)
    {
        if (! in_array($locale, ['en', 'zh'])) {
            abort(400);
        }

        Cookie::queue('locale', $locale, 60 * 24 * 365);

        if ($user = Auth::user()) {
            $user->update(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
