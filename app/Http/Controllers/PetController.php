<?php

namespace App\Http\Controllers;

use App\Exceptions\PetstoreApiException;
use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Services\Petstore\PetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetController
{
    private const ALLOWED_STATUSES = ['available', 'pending', 'sold'];

    public function __construct(
        private readonly PetService $petService
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $status = (string) $request->query('status', 'available');
        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            $status = 'available';
        }

        try {
            $pets = $this->petService->findByStatus($status);

            return view('pets.index', [
                'pets' => $pets,
                'status' => $status,
            ]);
        } catch (PetstoreApiException $exception) {
            return $this->apiErrorRedirect('Nie udało się pobrać listy zwierząt.', $exception);
        }
    }

    public function create(): View
    {
        return view('pets.create');
    }

    public function store(StorePetRequest $request): RedirectResponse
    {
        try {
            $pet = $this->petService->create($request->validated());

            $petId = $pet['id'] ?? null;

            if (is_numeric($petId)) {
                return redirect()
                    ->route('pets.show', (int) $petId)
                    ->with('success', 'Zwierzę zostało zapisane.');
            }

            return redirect()
                ->route('pets.index')
                ->with('success', 'Zwierzę zostało zapisane.');
        } catch (PetstoreApiException $exception) {
            return $this->apiErrorRedirect('Nie udało się zapisać zwierzęcia.', $exception);
        }
    }

    public function show(int $pet): View|RedirectResponse
    {
        try {
            return view('pets.show', [
                'pet' => $this->petService->find($pet),
            ]);
        } catch (PetstoreApiException $exception) {
            return $this->apiErrorRedirect('Nie udało się pobrać danych zwierzęcia.', $exception);
        }
    }

    public function edit(int $pet): View|RedirectResponse
    {
        try {
            return view('pets.edit', [
                'pet' => $this->petService->find($pet),
            ]);
        } catch (PetstoreApiException $exception) {
            return $this->apiErrorRedirect('Nie udało się pobrać danych do edycji.', $exception);
        }
    }

    public function update(UpdatePetRequest $request, int $pet): RedirectResponse
    {
        try {
            $validated = $request->validated();

            if ($this->shouldUseFormUpdate($validated)) {
                $this->petService->updateForm($pet, $validated);
            } else {
                $this->petService->update($pet, $validated);
            }

            return redirect()
                ->route('pets.show', $pet)
                ->with('success', 'Zwierzę zostało zaktualizowane.');
        } catch (PetstoreApiException $exception) {
            return $this->apiErrorRedirect('Nie udało się zaktualizować zwierzęcia.', $exception);
        }
    }

    public function destroy(int $pet): RedirectResponse
    {
        try {
            $this->petService->delete($pet);

            return redirect()
                ->route('pets.index')
                ->with('success', 'Zwierzę zostało usunięte.');
        } catch (PetstoreApiException $exception) {
            return $this->apiErrorRedirect('Nie udało się usunąć zwierzęcia.', $exception);
        }
    }

    private function apiErrorRedirect(string $message, PetstoreApiException $exception): RedirectResponse
    {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message)
            ->with('api', $this->resolveApiMessage($exception));
    }

    private function shouldUseFormUpdate(array $validated): bool
    {
        return ! filled($validated['category_name'] ?? null)
            && ! filled($validated['photo_url'] ?? null)
            && ! filled($validated['tags'] ?? null);
    }

    private function resolveApiMessage(PetstoreApiException $exception): string
    {
        $responseBody = $exception->responseBody();

        if (is_array($responseBody) && array_key_exists('message', $responseBody)) {
            $message = $responseBody['message'];

            if (is_scalar($message)) {
                return (string) $message;
            }
        }

        return $exception->getMessage();
    }
}
