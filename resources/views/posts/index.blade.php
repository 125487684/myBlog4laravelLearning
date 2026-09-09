<x-layout>
    <x-slot name="title"> article list </x-slot>

    <h1>list</h1>
    @foreach ($posts as $post)
        <article>
            <h2>
                <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
            </h2>
            <p>{{ $post->user->name }}</p>
            <p>{{ $post->created_at->format('Y-m-d') }}</p>
            <p>{{ Str::limit($post->body, 100) }}</p>
        </article>
    @endforeach

    {{ $posts->links() }}
</x-layout>