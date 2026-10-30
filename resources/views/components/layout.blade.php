<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? __('My blogs') }}</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
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
                        <a href="{{ route('posts.mine') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap">{{ __('My posts') }}</a>
                        <a href="{{ route('posts.participated') }}"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 whitespace-nowrap">{{ __('My participations') }}</a>
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

{{-- verification-link-sent 是验证提示页的专用协议键（页内绿条消费），不经全局 Toast 广播 --}}
@if (session('status') && session('status') !== 'verification-link-sent')
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

{{-- 全局删除确认弹窗：原生 <dialog>，删除表单通过 onsubmit="return confirmSubmit(this)" 调起 --}}
<dialog id="confirm-dialog"
    class="fixed inset-0 m-auto w-88 max-w-[calc(100vw-2rem)] rounded-xl border-0 bg-white p-6 shadow-2xl backdrop:bg-gray-900/60 backdrop:backdrop-blur-[2px]">
    <div class="flex items-start gap-3.5">
        <div class="h-11 w-11 shrink-0 rounded-full bg-red-50 flex items-center justify-center">
            <svg class="h-5.5 w-5.5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-900">{{ __('Delete') }}</h3>
            <p id="confirm-dialog-text" class="mt-1.5 text-sm leading-relaxed text-gray-500">{{ __('Confirm to delete?') }}</p>
        </div>
    </div>
    <div class="mt-6 flex justify-end gap-2.5">
        <button type="button" id="confirm-dialog-cancel"
            class="h-9 px-4 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">{{ __('Cancel') }}</button>
        <button type="button" id="confirm-dialog-confirm"
            class="h-9 px-4 rounded-lg text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">{{ __('Delete') }}</button>
    </div>
</dialog>

<script>
    (function () {
        var dialog = document.getElementById('confirm-dialog');
        var pendingForm = null;

        // 删除表单的 onsubmit 入口：拦下本次提交，记录表单并弹出确认框
        window.confirmSubmit = function (form, message) {
            pendingForm = form;
            if (message) document.getElementById('confirm-dialog-text').textContent = message;
            dialog.showModal();
            return false; // 返回 false 取消原生提交，等用户在弹窗里做决定
        };

        document.getElementById('confirm-dialog-confirm').addEventListener('click', function () {
            // form.submit() 不触发 submit 事件，因此不会再次进入 onsubmit 造成死循环
            pendingForm.submit();
        });

        document.getElementById('confirm-dialog-cancel').addEventListener('click', function () {
            pendingForm = null;
            dialog.close();
        });

        // 点击遮罩区域等同取消（dialog 自身铺满整个弹层，点到它说明点在内容之外）
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                pendingForm = null;
                dialog.close();
            }
        });
    })();
</script>
</body>
</html>
