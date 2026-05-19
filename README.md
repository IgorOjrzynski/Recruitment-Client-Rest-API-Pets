# Recruitment - Client REST API Pets (Laravel)

## Opis zadania
Aplikacja została przygotowana jako zadanie rekrutacyjne na stanowisko Junior PHP Developer (Laravel).  
Zakres:
- operacje CRUD na zasobie `pet`,
- proste formularze Blade (UI celowo uproszczone),
- obsługa błędów walidacji i błędów komunikacji z zewnętrznym API.

Integracja opiera się o Swagger Petstore: `https://petstore.swagger.io/v2`.

## Repozytoria
- Aplikacja (to repo):  
  `https://github.com/IgorOjrzynski/Recruitment-Client-Rest-API-Pets`
- Środowisko Docker (osobne repo):  
  `https://github.com/IgorOjrzynski/recruitment-pets-docker`

## Uruchomienie przez Docker (rekomendowane)
To repo (aplikacja) należy uruchamiać przez repo Docker.

1. Sklonuj repo Docker:
   ```bash
   git clone https://github.com/IgorOjrzynski/recruitment-pets-docker.git
   cd recruitment-pets-docker
   ```
2. W katalogu `application` umieść kod aplikacji:
   ```bash
   git clone https://github.com/IgorOjrzynski/Recruitment-Client-Rest-API-Pets.git application
   ```
3. Uruchom kontenery:
   ```bash
   docker compose up -d --build
   ```
4. Zainstaluj zależności:
   ```bash
   docker compose exec app composer install
   ```
5. Przygotuj `.env` i klucz:
   ```bash
   docker compose exec app sh -lc "cp .env.example .env && php artisan key:generate"
   ```
6. Aplikacja będzie dostępna pod:
   `http://localhost:8080/pets`

## Konfiguracja `.env`
Wymagany parametr:

```env
PETSTORE_API_URL=https://petstore.swagger.io/v2
```

## Użyte endpointy Petstore
- `GET /pet/findByStatus?status=available|pending|sold`
- `GET /pet/{petId}`
- `POST /pet`
- `PUT /pet`
- `POST /pet/{petId}` (aktualizacja formularzowa: `name`, `status`)
- `DELETE /pet/{petId}`

## Obsługa błędów
- Walidacja wejścia: `StorePetRequest`, `UpdatePetRequest` (komunikaty PL).
- Błędy HTTP i błędy połączenia mapowane do `PetstoreApiException`.
- Komunikaty dla użytkownika przekazywane przez sesję (`success`, `error`, `api`).

## Testy
Uruchomienie testów w kontenerze:

```bash
docker compose exec app php artisan test
```

## Uwaga
Warstwa wizualna jest celowo prosta.  
W zadaniu priorytetem była logika backendowa, komunikacja z API i obsługa błędów.

