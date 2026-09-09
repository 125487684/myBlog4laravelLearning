<x-layout>
    <x-slot name="title"> login </x-slot>

    <h1> login </h1>

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label> email <input type="email" name="email" value="{{ old("email") }}"></label><br>
        <label> pasword <input type="password" name="password" value="{{ old("password") }}"></label><br>
        <button type="submit"> login </button>
    </form>
</x-layout>