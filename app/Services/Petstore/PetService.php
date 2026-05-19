<?php

namespace App\Services\Petstore;

class PetService
{
    public function __construct(
        private readonly PetstoreClient $petstoreClient
    ) {
    }

    public function findByStatus(string $status = 'available'): array
    {
        return $this->petstoreClient->get('/pet/findByStatus', [
            'status' => $status,
        ]);
    }

    public function find(int $id): array
    {
        return $this->petstoreClient->get("/pet/{$id}");
    }

    public function create(array $data): array
    {
        $petId = $this->parseNullableInt($data['id'] ?? null);

        return $this->petstoreClient->post('/pet', $this->mapPetPayload($data, $petId));
    }

    public function update(int $id, array $data): array
    {
        return $this->petstoreClient->put('/pet', $this->mapPetPayload($data, $id));
    }

    public function updateForm(int $id, array $data): array
    {
        $payload = array_filter([
            'name' => $this->normalizeNullableString($data['name'] ?? null),
            'status' => $this->normalizeNullableString($data['status'] ?? null),
        ], static fn (?string $value): bool => $value !== null);

        return $this->petstoreClient->postForm("/pet/{$id}", $payload);
    }

    public function delete(int $id): bool
    {
        $this->petstoreClient->delete("/pet/{$id}");

        return true;
    }

    private function mapPetPayload(array $data, ?int $id): array
    {
        return [
            'id' => $id,
            'category' => [
                'id' => $this->resolveCategoryId($data),
                'name' => $this->resolveCategoryName($data),
            ],
            'name' => $this->normalizeString($data['name'] ?? ''),
            'photoUrls' => $this->normalizePhotoUrls(
                $data['photoUrls'] ?? $data['photo_urls'] ?? $data['photoUrl'] ?? $data['photo_url'] ?? []
            ),
            'tags' => $this->normalizeTags($data['tags'] ?? []),
            'status' => $this->normalizeString($data['status'] ?? 'available'),
        ];
    }

    private function resolveCategoryId(array $data): int
    {
        $category = $data['category'] ?? null;

        if (is_array($category)) {
            return $this->parseInt($category['id'] ?? 0, 0);
        }

        return $this->parseInt($data['category_id'] ?? 0, 0);
    }

    private function resolveCategoryName(array $data): string
    {
        $category = $data['category'] ?? null;

        if (is_array($category)) {
            return $this->normalizeString($category['name'] ?? '');
        }

        if (is_string($category)) {
            return $this->normalizeString($category);
        }

        return $this->normalizeString($data['category_name'] ?? '');
    }

    private function normalizePhotoUrls(mixed $photoUrls): array
    {
        if (is_string($photoUrls)) {
            $photoUrls = preg_split('/[\r\n,]+/', $photoUrls) ?: [];
        }

        if (! is_array($photoUrls)) {
            return [];
        }

        $normalized = [];

        foreach ($photoUrls as $photoUrl) {
            if (! is_scalar($photoUrl)) {
                continue;
            }

            $value = trim((string) $photoUrl);

            if ($value === '') {
                continue;
            }

            $normalized[] = $value;
        }

        return array_values($normalized);
    }

    private function normalizeTags(mixed $tags): array
    {
        if (is_string($tags)) {
            return $this->mapTagNamesToPayload($this->splitCommaSeparated($tags));
        }

        if (! is_array($tags)) {
            return [];
        }

        if (! array_is_list($tags) && array_key_exists('name', $tags)) {
            $tags = [$tags];
        }

        $normalized = [];

        foreach ($tags as $tag) {
            if (is_string($tag)) {
                $name = $this->normalizeNullableString($tag);

                if ($name === null) {
                    continue;
                }

                $normalized[] = [
                    'id' => 0,
                    'name' => $name,
                ];

                continue;
            }

            if (! is_array($tag)) {
                continue;
            }

            $name = $this->normalizeNullableString($tag['name'] ?? null);

            if ($name === null) {
                continue;
            }

            $normalized[] = [
                'id' => $this->parseInt($tag['id'] ?? 0, 0),
                'name' => $name,
            ];
        }

        return array_values($normalized);
    }

    private function mapTagNamesToPayload(array $names): array
    {
        $payload = [];

        foreach ($names as $name) {
            $payload[] = [
                'id' => 0,
                'name' => $name,
            ];
        }

        return $payload;
    }

    private function splitCommaSeparated(string $value): array
    {
        $parts = preg_split('/[\r\n,]+/', $value) ?: [];
        $normalized = [];

        foreach ($parts as $part) {
            $name = $this->normalizeNullableString($part);

            if ($name === null) {
                continue;
            }

            $normalized[] = $name;
        }

        return array_values($normalized);
    }

    private function normalizeString(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function parseInt(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (! is_scalar($value) || ! is_numeric((string) $value)) {
            return $default;
        }

        return (int) $value;
    }

    private function parseNullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_scalar($value) || ! is_numeric((string) $value)) {
            return null;
        }

        return (int) $value;
    }
}
