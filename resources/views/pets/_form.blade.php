@php
    $petData = is_array($pet ?? null) ? $pet : [];

    $nameValue = old('name', $petData['name'] ?? '');
    $statusValue = old('status', $petData['status'] ?? 'available');
    $categoryNameValue = old('category_name', $petData['category']['name'] ?? '');
    $photoUrlValue = old('photo_url', $petData['photoUrls'][0] ?? '');

    if (old('tags') !== null) {
        $tagsValue = (string) old('tags');
    } else {
        $tagNames = [];
        $tags = $petData['tags'] ?? [];

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

        $tagsValue = implode(', ', $tagNames);
    }
@endphp

<div>
    <label for="name">Nazwa *</label><br>
    <input id="name" name="name" type="text" maxlength="255" value="{{ $nameValue }}" required>
</div>

<div>
    <label for="status">Status *</label><br>
    <select id="status" name="status" required>
        <option value="available" @selected($statusValue === 'available')>available</option>
        <option value="pending" @selected($statusValue === 'pending')>pending</option>
        <option value="sold" @selected($statusValue === 'sold')>sold</option>
    </select>
</div>

<div>
    <label for="category_name">Nazwa kategorii</label><br>
    <input id="category_name" name="category_name" type="text" maxlength="255" value="{{ $categoryNameValue }}">
</div>

<div>
    <label for="photo_url">URL zdjęcia</label><br>
    <input id="photo_url" name="photo_url" type="url" value="{{ $photoUrlValue }}">
</div>

<div>
    <label for="tags">Tagi (po przecinku)</label><br>
    <input id="tags" name="tags" type="text" maxlength="500" value="{{ $tagsValue }}">
</div>

<div>
    <button type="submit">{{ $submitLabel ?? 'Zapisz' }}</button>
</div>

