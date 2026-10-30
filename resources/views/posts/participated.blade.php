<x-layout>
    <x-slot name="title">{{ __('My participations') }}</x-slot>

    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ __('My participations') }}</h1>
    <p class="text-sm text-gray-500 mb-6">{{ __('Posts you authored or commented on.') }}</p>

    @if ($posts->isEmpty())
        <p class="text-sm text-gray-400">{{ __('No participations yet.') }}</p>
    @else
        <div class="space-y-6">
            @foreach ($posts as $post)
                <x-posts.card :post="$post" />
            @endforeach
        </div>
        {{ $posts->links() }}
    @endif
</x-layout>
