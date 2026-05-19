<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PetControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('petstore.base_url', 'https://petstore.swagger.io/v2');
    }

    public function test_index_calls_find_by_status_endpoint(): void
    {
        Http::fake([
            'https://petstore.swagger.io/v2/pet/findByStatus*' => Http::response([], 200),
        ]);

        $response = $this->get('/pets?status=pending');

        $response->assertOk();
        $response->assertViewHas('status', 'pending');

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'GET'
                && str_contains($request->url(), '/pet/findByStatus')
                && str_contains($request->url(), 'status=pending');
        });
    }

    public function test_store_maps_payload_and_redirects_with_success(): void
    {
        Http::fake([
            'https://petstore.swagger.io/v2/pet' => Http::response(['id' => 123], 200),
        ]);

        $response = $this->post('/pets', [
            'name' => 'Rex',
            'status' => 'available',
            'category_name' => 'Dogs',
            'photo_url' => 'https://example.com/rex.jpg',
            'tags' => 'friendly, active',
        ]);

        $response->assertRedirect(route('pets.show', 123));
        $response->assertSessionHas('success', 'Zwierzę zostało zapisane.');

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://petstore.swagger.io/v2/pet'
                && $request['name'] === 'Rex'
                && $request['status'] === 'available'
                && $request['category']['name'] === 'Dogs'
                && $request['photoUrls'] === ['https://example.com/rex.jpg']
                && $request['tags'] === [
                    ['id' => 0, 'name' => 'friendly'],
                    ['id' => 0, 'name' => 'active'],
                ];
        });
    }

    public function test_update_with_only_required_fields_uses_form_endpoint(): void
    {
        Http::fake([
            'https://petstore.swagger.io/v2/pet/123' => Http::response([], 200),
            'https://petstore.swagger.io/v2/pet' => Http::response([], 200),
        ]);

        $response = $this->put('/pets/123', [
            'name' => 'Rex',
            'status' => 'pending',
            'category_name' => '',
            'photo_url' => '',
            'tags' => '',
        ]);

        $response->assertRedirect(route('pets.show', 123));
        $response->assertSessionHas('success', 'Zwierzę zostało zaktualizowane.');

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'POST'
                && $request->url() === 'https://petstore.swagger.io/v2/pet/123'
                && str_contains(($request->header('Content-Type')[0] ?? ''), 'application/x-www-form-urlencoded')
                && $request['name'] === 'Rex'
                && $request['status'] === 'pending';
        });

        Http::assertNotSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === 'https://petstore.swagger.io/v2/pet';
        });
    }

    public function test_update_with_optional_fields_uses_put_endpoint(): void
    {
        Http::fake([
            'https://petstore.swagger.io/v2/pet' => Http::response([], 200),
            'https://petstore.swagger.io/v2/pet/123' => Http::response([], 200),
        ]);

        $response = $this->put('/pets/123', [
            'name' => 'Rex',
            'status' => 'sold',
            'category_name' => 'Dogs',
            'photo_url' => 'https://example.com/rex.jpg',
            'tags' => 'one, two',
        ]);

        $response->assertRedirect(route('pets.show', 123));

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'PUT'
                && $request->url() === 'https://petstore.swagger.io/v2/pet'
                && $request['id'] === 123
                && $request['category']['name'] === 'Dogs'
                && $request['photoUrls'] === ['https://example.com/rex.jpg']
                && $request['tags'] === [
                    ['id' => 0, 'name' => 'one'],
                    ['id' => 0, 'name' => 'two'],
                ];
        });
    }

    public function test_destroy_calls_delete_endpoint_and_redirects(): void
    {
        Http::fake([
            'https://petstore.swagger.io/v2/pet/123' => Http::response([], 200),
        ]);

        $response = $this->delete('/pets/123');

        $response->assertRedirect(route('pets.index'));
        $response->assertSessionHas('success', 'Zwierzę zostało usunięte.');

        Http::assertSent(function (Request $request): bool {
            return $request->method() === 'DELETE'
                && $request->url() === 'https://petstore.swagger.io/v2/pet/123';
        });
    }

    public function test_validation_error_prevents_api_call(): void
    {
        Http::fake();

        $response = $this->from('/pets/create')->post('/pets', [
            'name' => '',
            'status' => 'available',
        ]);

        $response->assertRedirect('/pets/create');
        $response->assertSessionHasErrors('name');

        Http::assertNothingSent();
    }

    public function test_api_error_returns_back_with_error_and_api_message(): void
    {
        Http::fake([
            'https://petstore.swagger.io/v2/pet' => Http::response(['message' => 'Petstore unavailable'], 503),
        ]);

        $response = $this->from('/pets/create')->post('/pets', [
            'name' => 'Rex',
            'status' => 'available',
            'category_name' => null,
            'photo_url' => null,
            'tags' => null,
        ]);

        $response->assertRedirect('/pets/create');
        $response->assertSessionHas('error', 'Nie udało się zapisać zwierzęcia.');
        $response->assertSessionHas('api', 'Petstore unavailable');
    }
}

