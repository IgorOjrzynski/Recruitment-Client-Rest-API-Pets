<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podgląd peta</title>
</head>
<body>
    @php
        $petId = $pet['id'] ?? request()->route('pet');
        $categoryName = $pet['category']['name'] ?? '-';
        $photoUrls = is_array($pet['photoUrls'] ?? null) ? $pet['photoUrls'] : [];

        $tagNames = [];
        $tags = $pet['tags'] ?? [];

        if (is_array($tags)) {
            foreach ($tags as $tag) {
                if (! is_array($tag)) {
                    continue;
                }

                $tagName = $tag['name'] ?? null;

                if (! is_scalar($tagName)) {
                    continue;
                }

                $tagName = trim((string) $tagName);

                if ($tagName === '') {
                    continue;
                }

                $tagNames[] = $tagName;
            }
        }
    @endphp

    <h1>Podgląd peta #{{ $petId }}</h1>

    <p>
        <a href="{{ route('pets.index') }}">Wróć do listy</a>
        @if (is_numeric($petId))
            | <a href="{{ route('pets.edit', (int) $petId) }}">Edytuj</a>
        @endif
    </p>

    @include('pets._messages')

    <dl>
        <dt><strong>ID</strong></dt>
        <dd>{{ is_scalar($pet['id'] ?? null) ? $pet['id'] : '-' }}</dd>

        <dt><strong>Nazwa</strong></dt>
        <dd>{{ $pet['name'] ?? '-' }}</dd>

        <dt><strong>Status</strong></dt>
        <dd>{{ $pet['status'] ?? '-' }}</dd>

        <dt><strong>Kategoria</strong></dt>
        <dd>{{ $categoryName }}</dd>

        <dt><strong>Zdjęcia</strong></dt>
        <dd>
            @if (count($photoUrls) === 0)
                -
            @else
                <ul>
                    @foreach ($photoUrls as $photoUrl)
                        <li>{{ $photoUrl }}</li>
                    @endforeach
                </ul>
            @endif
        </dd>

        <dt><strong>Tagi</strong></dt>
        <dd>{{ count($tagNames) ? implode(', ', $tagNames) : '-' }}</dd>
    </dl>

    @if (is_numeric($petId))
        <form method="POST" action="{{ route('pets.destroy', (int) $petId) }}">
            @csrf
            @method('DELETE')
            <button type="submit">Usuń</button>
        </form>
    @endif
</body>
</html>
