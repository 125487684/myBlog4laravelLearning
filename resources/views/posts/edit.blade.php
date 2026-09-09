
<x-layout>
    <x-slot name="title"> edit </x-slot>

    <h1>edit article</h1>
    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('posts.update', $post) }}">
        @csrf
        @method('PUT')
        @include('posts._form')
    </form>
</x-layout>