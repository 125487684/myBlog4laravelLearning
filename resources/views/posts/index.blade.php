<x-layout>
    <x-slot name="title"> {{ __('article list') }} </x-slot>

    <div class="space-y-6">
        @foreach ($posts as $post)
            <article class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                <h2 class="text-xl font-semibold mb-2">
                    <a href="{{ route('posts.show', $post) }}"
                        class="text-gray-900 hover:text-blue-600">{{ $post->title }}</a>
                </h2>
                <p class="text-sm text-gray-500 mb-3">
                    {{ $post->user->name }} · {{ $post->created_at->format('Y-m-d') }}
                </p>
                <p class="text-gray-600 leading-relaxed">{{ Str::limit($post->body, 100) }}</p>
            </article>
        @endforeach
    </div>
    {{ $posts->links() }}
</x-layout>