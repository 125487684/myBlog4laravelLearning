<x-layout>
    <x-slot name="title">{{ __('Verify email') }}</x-slot>

    <div class="max-w-md mx-auto mt-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Verify email') }}</h1>

        @php
            // 倒计时起点取两者较大值：闪存场景固定 60，冷却场景用服务端剩余秒数
            $initialCooldown = max(session('status') === 'verification-link-sent' ? 60 : 0, $cooldown ?? 0);
        @endphp

        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-5">
            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            <p class="text-sm text-gray-600">
                {{ __('Before proceeding, please check your email for a verification link.') }}
            </p>

            @php
                // 刚发过信（闪存）或服务端冷却期内（RateLimiter 记录），都显示已发提示
                $justSent = session('status') === 'verification-link-sent' || ($cooldown ?? 0) > 0;
            @endphp

            @if ($justSent)
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
                    <button type="submit"
                        data-countdown
                        data-countdown-seconds="{{ $initialCooldown }}"
                        data-countdown-label="{{ __('Resend verification email') }}"
                        data-countdown-template="{{ __('Resend in :s seconds') }}"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                        <span>{{ __('Resend verification email') }}</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

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