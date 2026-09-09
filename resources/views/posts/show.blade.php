<x-layout>
    <x-slot name="title">{{ $post->title }}</x-slot>

    <a href="{{ route('posts.index') }}" class="text-sm text-blue-600 hover:underline">&larr; {{ __('Back to list') }}</a>

    <article class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm mt-4">
        <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $post->title }}</h1>
        <p class="text-sm text-gray-500 pb-6 mb-6 border-b border-gray-100">
            {{ $post->user->name }} · {{ $post->created_at->format('Y-m-d') }}
        </p>

        <div class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $post->body }}</div>

        @can('update', $post)
            <div class="mt-6 pt-6 border-t border-gray-100 flex gap-6">
                <a href="{{ route('posts.edit', $post) }}"
                   class="text-sm font-medium text-blue-600 hover:underline">{{ __('Edit') }}</a>
                <form method="POST" action="{{ route('posts.destroy', $post) }}"
                      onsubmit="return confirm('{{ __('Confirm to delete?') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        @endcan
    </article>
</x-layout>
