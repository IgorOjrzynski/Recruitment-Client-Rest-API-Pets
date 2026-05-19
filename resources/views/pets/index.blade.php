<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista petów</title>
</head>
<body>
    <h1>Lista petów</h1>

    <p>
        <a href="{{ route('pets.create') }}">Dodaj nowego peta</a>
    </p>

    @include('pets._messages')

    <form method="GET" action="{{ route('pets.index') }}">
        <label for="status">Status:</label>
        <select id="status" name="status">
            <option value="available" @selected(($status ?? 'available') === 'available')>available</option>
            <option value="pending" @selected(($status ?? 'available') === 'pending')>pending</option>
            <option value="sold" @selected(($status ?? 'available') === 'sold')>sold</option>
        </select>
        <button type="submit">Filtruj</button>
    </form>

    @php
        $petsList = is_array($pets ?? null) ? $pets : [];
    @endphp

    @if (count($petsList) === 0)
        <p>Brak petów dla wybranego statusu.</p>
    @else
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nazwa</th>
                    <th>Status</th>
                    <th>Kategoria</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($petsList as $pet)
                    @php
                        $id = $pet['id'] ?? null;
                        $categoryName = $pet['category']['name'] ?? '-';
                    @endphp
                    <tr>
                        <td>{{ is_scalar($id) ? $id : '-' }}</td>
                        <td>{{ $pet['name'] ?? '-' }}</td>
                        <td>{{ $pet['status'] ?? '-' }}</td>
                        <td>{{ $categoryName }}</td>
                        <td>
                            @if (is_numeric($id))
                                <a href="{{ route('pets.show', (int) $id) }}">Pokaż</a>
                                <a href="{{ route('pets.edit', (int) $id) }}">Edytuj</a>
                                <form method="POST" action="{{ route('pets.destroy', (int) $id) }}" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">Usuń</button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
