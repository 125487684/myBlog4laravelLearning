<x-layout>
    <x-slot name="title">{{ __('My posts') }}</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('My posts') }}</h1>
        <a href="{{ route('posts.create') }}"
            class="text-sm font-medium text-blue-600 hover:underline">{{ __('Write article') }}</a>
    </div>

    @if ($posts->isEmpty())
        <p class="text-sm text-gray-400">{{ __('No posts yet.') }}</p>
    @else
        <div class="space-y-6">
            @foreach ($posts as $post)
                <x-posts.card :post="$post" />
            @endforeach
        </div>
        {{ $posts->links() }}
    @endif
</x-layout>
