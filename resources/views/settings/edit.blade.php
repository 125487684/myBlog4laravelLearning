<x-layout title="{{ __('Settings') }}">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Settings') }}</h1>

        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
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
    </div>
</x-layout>
