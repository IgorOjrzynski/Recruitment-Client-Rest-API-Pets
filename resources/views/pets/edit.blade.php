<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edytuj peta</title>
</head>
<body>
    @php
        $petId = (int) request()->route('pet');
    @endphp

    <h1>Edytuj peta #{{ $petId }}</h1>

    <p>
        <a href="{{ route('pets.show', $petId) }}">Wróć do podglądu</a> |
        <a href="{{ route('pets.index') }}">Wróć do listy</a>
    </p>

    @include('pets._messages')

    <form method="POST" action="{{ route('pets.update', $petId) }}">
        @csrf
        @method('PUT')

        @include('pets._form', ['pet' => $pet, 'submitLabel' => 'Zapisz zmiany'])
    </form>
</body>
</html>
