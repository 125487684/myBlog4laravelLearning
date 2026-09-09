<x-layout>
    <x-slot name="title"> show </x-slot>

    <a href="{{ route('posts.index') }}"><- back list</a>
    <h1>{{ $post->title }}</h1>
    <p>{{ $post->user->name }} · {{ $post->created_at->format('Y-m-d') }}</p>
    <div>{{ $post->body }}</div>

    @can('update',$post)
        <a href="{{ route('posts.edit', $post) }}"> edit </a>

        <form method="POST" action="{{ route('posts.destroy', $post) }}"
            onsubmit="return confirm('comfirm to delete?')">
            @csrf
            @method('DELETE')
            <button type="submit">delete</button>
        </form>
    @endcan
</x-layout>