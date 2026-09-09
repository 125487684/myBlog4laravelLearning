<x-layout>
    <x-slot name="title">{{ __('Edit') }}</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Edit') }}</h1>

        <form method="POST" action="{{ route('posts.update', $post) }}"
            class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            @csrf
            @method('PUT')
            @include('posts._form')
        </form>
    </div>
</x-layout>
