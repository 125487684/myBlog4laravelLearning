<x-layout>
    <x-slot name="title">{{ __('Verify email') }}</x-slot>

    <div class="max-w-md mx-auto mt-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Verify email') }}</h1>

        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-5">
            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-sm text-gray-600">
                {{ __('Before proceeding, please check your email for a verification link.') }}
            </p>

            @if (session('status') === 'verification-link-sent')
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ __('A fresh verification link has been sent to your email address.') }}
                </div>
            @endif

            <div class="flex items-center justify-between">
                <a href="{{ route('posts.index') }}" class="text-sm text-gray-500 hover:underline">
                    {{ __('Back to list') }}
                </a>

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" id="resend-verification"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                        <span id="resend-verification-label">{{ __('Resend verification email') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @if (session('status') === 'verification-link-sent')
        <script>
            (function () {
                let seconds = 60;
                const button = document.getElementById('resend-verification');
                const label = document.getElementById('resend-verification-label');
                const template = @json(__('Resend in :s seconds'));

                const tick = function () {
                    if (seconds > 0) {
                        button.disabled = true;
                        label.textContent = template.replace(':s', seconds);
                        seconds -= 1;
                        setTimeout(tick, 1000);
                    } else {
                        button.disabled = false;
                        label.textContent = @json(__('Resend verification email'));
                    }
                };

                tick();
            })();
        </script>
    @endif

    {{-- 轮询验证状态：用户在另一个标签页/设备完成验证后，本页自动跳转 --}}
    @unless (auth()->user()->hasVerifiedEmail())
        <script>
            (function () {
                const poll = setInterval(function () {
                    fetch(@json(route('verification.status')))
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (data.verified) {
                                clearInterval(poll);
                                window.location.href = @json(route('posts.index'));
                            }
                        })
                        .catch(function () { /* 网络抖动时静默，下一轮会再试 */ });
                }, 3000);
            })();
        </script>
    @endunless
</x-layout>