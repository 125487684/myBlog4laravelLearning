<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        Auth::login($user);

        return redirect()->route('posts.index');
    }
}
