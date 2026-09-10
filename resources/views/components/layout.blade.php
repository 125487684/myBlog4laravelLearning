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
            @auth
                <a href="{{ route('posts.create') }}"
                    class="leading-8 text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">{{ __('Write article') }}</a>
                <details class="relative">
                    <summary class="inline-flex h-8 items-center gap-1 cursor-pointer list-none [&::-webkit-details-marker]:hidden text-sm text-gray-600 hover:text-gray-900 select-none">
                        <span class="max-w-[10rem] truncate">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-gray-400">▾</span>
                    </summary>
                    <div class="absolute right-0 mt-2 w-full min-w-max bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10">
                        <a href="{{ route('settings.edit') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap">{{ __('Settings') }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-100 mt-1 pt-1">
                            @csrf
                            <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 whitespace-nowrap">{{ __('Log out') }}</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="leading-8 text-sm font-medium text-blue-600 hover:underline">{{ __('Log in') }}</a>
                <a href="{{ route('register') }}" class="leading-8 text-sm font-medium text-blue-600 hover:underline">{{ __('Register') }}</a>
            @endauth
        </div>
    </div>
</nav>

<main class="max-w-4xl mx-auto px-4 py-8">
    {{ $slot }}
</main>

@if (session('status'))
    <div id="flash-toast"
        class="fixed bottom-6 right-6 z-50 max-w-sm rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700 shadow-lg transition-all duration-500">
        {{ __(session('status')) }}
    </div>

    <script>
        (function () {
            const toast = document.getElementById('flash-toast');

            setTimeout(function () {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(0.5rem)';
                // 等淡出动画播完再把节点从 DOM 移除，避免残留一个透明的空盒子
                setTimeout(function () {
                    toast.remove();
                }, 500);
            }, 5000);
        })();
    </script>
@endif
</body>
</html>
