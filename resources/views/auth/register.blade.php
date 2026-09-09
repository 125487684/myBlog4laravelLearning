<x-layout>
    <x-slot name="title">{{ __('Register') }}</x-slot>

    <div class="max-w-md mx-auto mt-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Register') }}</h1>

        <form method="POST" action="{{ route('register.store') }}"
            class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('name')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }}</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('email')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Password') }}</label>
                <input id="password" type="password" name="password"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Confirm password') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                {{ __('Register') }}
            </button>
        </form>

        <p class="text-sm text-gray-500 mt-4 text-center">
            {{ __('Already have an account?') }}
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">{{ __('Log in') }}</a>
        </p>
    </div>
</x-layout>
