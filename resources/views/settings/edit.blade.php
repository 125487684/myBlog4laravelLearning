<x-layout title="{{ __('Settings') }}">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Settings') }}</h1>

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

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                    <input id="email" type="email" value="{{ auth()->user()->email }}" disabled
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">{{ __('Email cannot be changed yet') }}</p>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100">
                    <button type="submit"
                        class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
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
    </div>
</x-layout>
