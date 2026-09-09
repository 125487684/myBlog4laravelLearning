<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
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
                    class="leading-8 text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">{{ __('Write article') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex h-8 items-center text-sm text-gray-500 hover:text-gray-700">
                        {{ __('Log out') }} ({{ auth()->user()->name }})
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="leading-8 text-sm font-medium text-blue-600 hover:underline">{{ __('Log in') }}</a>
                <a href="{{ route('register') }}" class="leading-8 text-sm font-medium text-blue-600 hover:underline">{{ __('Register') }}</a>
            @endauth
            <div class="flex items-center rounded-lg border border-gray-300 bg-white overflow-hidden text-sm font-medium" role="group" aria-label="Language">
                @foreach (['en' => 'EN', 'zh' => '中文'] as $code => $label)
                    @if (app()->getLocale() === $code)
                        <span class="px-2.5 py-1 bg-gray-900 text-white" aria-current="true">{{ $label }}</span>
                    @else
                        <a href="{{ route('language.switch', $code) }}"
                            class="px-2.5 py-1 text-gray-600 hover:bg-gray-100">{{ $label }}</a>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-8">
    {{ $slot }}
</main>
</body>
</html>
