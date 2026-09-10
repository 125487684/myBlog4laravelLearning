<x-layout>
    <x-slot name="title">{{ __('Forgot password?') }}</x-slot>

    <div class="max-w-md mx-auto mt-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Forgot password?') }}</h1>

        <form method="POST" action="{{ route('password.email') }}"
              class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autofocus
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            @php
                // 发送成功后闪存 status 存在时启动倒计时；秒数取自节流配置，避免两处硬编码
                $justSent = session('status') === __('passwords.sent');
            @endphp
            <button type="submit" id="send-reset-link"
                data-throttle="{{ config('auth.passwords.users.throttle') }}"
                @if ($justSent) disabled @endif
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600">
                <span id="send-reset-label">{{ __('Send password reset link') }}</span>
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-4 text-center">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">{{ __('Log in') }}</a>
        </p>
    </div>

    @if ($justSent)
        <script>
            (function () {
                let seconds = parseInt(document.getElementById('send-reset-link').dataset.throttle, 10) || 60;
                const button = document.getElementById('send-reset-link');
                const label = document.getElementById('send-reset-label');
                const template = @json(__('Resend in :s seconds'));

                const tick = function () {
                    if (seconds > 0) {
                        button.disabled = true;
                        label.textContent = template.replace(':s', seconds);
                        seconds -= 1;
                        setTimeout(tick, 1000);
                    } else {
                        button.disabled = false;
                        label.textContent = @json(__('Send password reset link'));
                    }
                };

                tick();
            })();
        </script>
    @endif
</x-layout>
