<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dodaj peta</title>
</head>
<body>
    <h1>Dodaj peta</h1>

    <p>
        <a href="{{ route('pets.index') }}">Wróć do listy</a>
    </p>

    @include('pets._messages')

    <form method="POST" action="{{ route('pets.store') }}">
        @csrf

        @include('pets._form', ['submitLabel' => 'Zapisz'])
    </form>
</body>
</html>
