<div class="space-y-5">
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }}</label>
        <input id="title" type="text" name="title" value="{{ old('title', $post->title ?? '') }}"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        @error('title')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Slug') }}</label>
        <input id="slug" type="text" name="slug" value="{{ old('slug', $post->slug ?? '') }}"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        @error('slug')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Body') }}</label>
        <textarea id="body" name="body" rows="10"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('body', $post->body ?? '') }}</textarea>
        @error('body')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <button type="submit"
            class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            {{ $buttonText ?? __('Submit') }}
        </button>
    </div>
</div>
