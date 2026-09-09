<x-layout>
    <x-slot name="title">{{ __('Create') }}</x-slot>

    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Create') }}</h1>

        <form method="POST" action="{{ route('posts.store') }}"
            class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
            @csrf
            @include('posts._form')
        </form>
    </div>
</x-layout>
