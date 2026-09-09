<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'my blogs' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav class="flex items-center gap-4">
        <a href="{{ route('posts.index') }}"> list </a>

        @auth
            <a href="{{ route('posts.create') }}"> write article </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"> logout ({{ auth()->user()->name }}) </button>
            </form>
        @else
            <a href="{{ route('login') }}"> login </a>
            <a href="{{ route('register') }}"> register </a>
        @endauth
    </nav>
    <hr>

    {{ $slot }}
</body>
</html>