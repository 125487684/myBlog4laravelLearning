<x-layout>
    <x-slot name="title"> {{ __('article list') }} </x-slot>

    <div class="space-y-6">
        @foreach ($posts as $post)
            <x-posts.card :post="$post" />
        @endforeach
    </div>
    {{ $posts->links() }}
</x-layout>
