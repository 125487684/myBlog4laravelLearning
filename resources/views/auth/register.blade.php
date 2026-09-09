<x-layout>
    <x-slot name="title"> register </x-slot>

    <h1> register </h1>

    @if ($errors->any())
        <ul style="color:red">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <label>name <input type="text" name="name" value="{{ old("name") }}"></label><br>
        <label>email <input type="email" name="email" value="{{ old("email") }}"></label><br>
        <label>password <input type="password" name="password" value="{{ old("password") }}"></label><br>
        <label>confirm password <input type="password" name="password_confirmation" value="{{ old("password_confirmation") }}"></label><br>
        <button type="submit">register</button>
    </form>
</x-layout>