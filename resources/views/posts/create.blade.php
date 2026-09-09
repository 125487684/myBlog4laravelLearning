
<x-layout>
    <x-slot name="title"> create </x-slot>

    <h1>new article</h1>
    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('posts.store') }}">
        @csrf
        @include('posts._form')
    </form>
</x-layout>