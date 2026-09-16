<x-layout title="{{ __('Settings') }}">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Settings') }}</h1>

        {{-- 页签导航：管理员可见两个页签，普通用户只见个人设置（无切换入口） --}}
        <div class="flex items-center gap-1 mb-6 border-b border-gray-200">
            <a href="{{ route('settings.edit') }}"
                class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === 'profile' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                {{ __('Profile settings') }}
            </a>
            @can('admin')
                <a href="{{ route('settings.edit', ['tab' => 'admin']) }}"
                    class="px-4 py-2 text-sm font-medium border-b-2 -mb-px {{ $tab === 'admin' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    {{ __('Admin settings') }}
                </a>
            @endcan
        </div>

        @if ($tab === 'admin')
            {{-- 管理员设置：邮件服务器配置 --}}
            @can('admin')
                <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-1">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('Admin settings') }}</h2>
                        <a href="{{ route('mail-settings.edit') }}"
                            class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline">
                            {{ __('Mail settings') }} →
                        </a>
                    </div>
                    <p class="text-sm text-gray-500">{{ __('Configure the SMTP server for outgoing emails.') }}</p>
                </div>
            @endcan
        @else
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">{{ __('Profile') }}</h2>

            @if (session('status_profile'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ __(session('status_profile')) }}
                </div>
            @endif

            @if ($errors->has('name'))
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @error('name')
                            <li>{{ $message }}</li>
                        @enderror
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                        class="w-full border {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 {{ $errors->has('name') ? 'focus:ring-red-500 focus:border-red-500' : 'focus:ring-blue-500 focus:border-blue-500' }}">
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>

        {{-- 邮箱卡：当前邮箱 + 验证状态 + 修改邮箱（独立表单，禁止与姓名表单嵌套） --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">{{ __('Email') }}</h2>

            <div>
                <div class="flex items-center gap-3 mb-3">
                        <input id="email" type="email" value="{{ auth()->user()->email }}" disabled
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500">
                        @if (auth()->user()->hasVerifiedEmail())
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2 whitespace-nowrap">
                                ✓ {{ __('Verified') }}
                            </span>
                        @else
                            <a href="{{ route('verification.notice') }}"
                                class="inline-flex items-center gap-1 text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 whitespace-nowrap hover:bg-amber-100">
                                {{ __('Unverified — verify now') }}
                            </a>
                        @endif
                    </div>

                    {{-- 修改邮箱：独立小表单（验证当前密码 + 新邮箱）。错误挂 email 袋（validateWithBag），不与改密码卡串门 --}}
                    {{-- 布尔属性存在即生效，{{ }} 会把 null 渲染成 open=""（恒真），必须条件输出裸属性 --}}
                    <details class="text-sm" @if ($errors->email->any()) open @endif>
                        <summary class="cursor-pointer select-none text-gray-500 hover:text-gray-700">{{ __('Change email') }}</summary>

                        @if ($errors->email->any())
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->email->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('settings.email.update') }}" class="mt-3 space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label for="new-email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New email') }}</label>
                                <input id="new-email" type="email" name="email" value="{{ old('email') }}" required
                                    class="w-full border {{ $errors->email->has('email') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 {{ $errors->email->has('email') ? 'focus:ring-red-500 focus:border-red-500' : 'focus:ring-blue-500 focus:border-blue-500' }}"
                                    placeholder="{{ auth()->user()->email }}">
                            </div>

                            <div>
                                <label for="email-current-password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current password') }}</label>
                                <input id="email-current-password" type="password" name="current_password" required autocomplete="current-password"
                                    class="w-full border {{ $errors->email->has('current_password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 {{ $errors->email->has('current_password') ? 'focus:ring-red-500 focus:border-red-500' : 'focus:ring-blue-500 focus:border-blue-500' }}">
                            </div>

                            <p class="text-xs text-gray-400">{{ __('Changing your email will require verifying the new address.') }}</p>

                            <button type="submit"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                {{ __('Update email') }}
                            </button>
                        </form>
                    </details>
                </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mt-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-5">{{ __('Change password') }}</h2>

            @if (session('status_password'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ __(session('status_password')) }}
                </div>
            @endif

            @if ($errors->has('current_password') || $errors->has('password'))
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @error('current_password')
                            <li>{{ $message }}</li>
                        @enderror
                        @error('password')
                            <li>{{ $message }}</li>
                        @enderror
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.password.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Current password') }}</label>
                    <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                        class="w-full border {{ $errors->has('current_password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 {{ $errors->has('current_password') ? 'focus:ring-red-500 focus:border-red-500' : 'focus:ring-blue-500 focus:border-blue-500' }}">
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('New password') }}</label>
                    <input id="new_password" type="password" name="password" autocomplete="new-password"
                        class="w-full border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'focus:ring-red-500 focus:border-red-500' : 'focus:ring-blue-500 focus:border-blue-500' }}">
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm password') }}</label>
                    <input id="new_password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                        class="w-full border {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }} rounded-lg px-3 py-2 focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'focus:ring-red-500 focus:border-red-500' : 'focus:ring-blue-500 focus:border-blue-500' }}">
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Change password') }}
                    </button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-layout>
