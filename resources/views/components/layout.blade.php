<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? __('My blogs') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">
<nav class="bg-white border-b border-gray-200">
    <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="{{ route('posts.index') }}" class="text-lg font-bold text-gray-900">{{ __('List') }}</a>
        <div class="flex items-center gap-4">
            @auth
                <a href="{{ route('posts.create') }}"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">{{ __('Write article') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                        {{ __('Log out') }} ({{ auth()->user()->name }})
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:underline">{{ __('Log in') }}</a>
                <a href="{{ route('register') }}" class="text-sm font-medium text-blue-600 hover:underline">{{ __('Register') }}</a>
            @endauth
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-8">
    {{ $slot }}
</main>
</body>
</html>
