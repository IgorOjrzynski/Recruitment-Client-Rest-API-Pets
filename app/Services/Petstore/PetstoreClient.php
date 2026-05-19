<?php

namespace App\Services\Petstore;

use App\Exceptions\PetstoreApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PetstoreClient
{
    public function get(string $uri, array $query = []): array
    {
        return $this->sendRequest('get', $uri, $query);
    }

    public function post(string $uri, array $data = []): array
    {
        return $this->sendRequest('post', $uri, $data);
    }

    public function put(string $uri, array $data = []): array
    {
        return $this->sendRequest('put', $uri, $data);
    }

    public function delete(string $uri, array $data = []): array
    {
        return $this->sendRequest('delete', $uri, $data);
    }

    public function postForm(string $uri, array $data = []): array
    {
        return $this->sendRequest('post', $uri, $data, true);
    }

    /**
     * @throws PetstoreApiException
     */
    private function sendRequest(string $method, string $uri, array $payload = [], bool $asForm = false): array
    {
        try {
            $request = Http::baseUrl((string) config('petstore.base_url'))
                ->acceptJson()
                ->timeout((int) config('petstore.timeout', 10));

            if ($asForm) {
                $request = $request->asForm();
            }

            $response = $this->send($request, $method, ltrim($uri, '/'), $payload);

            $response->throw();

            return $this->responseToArray($response);
        } catch (RequestException $exception) {
            $response = $exception->response;
            $responseBody = $response->json();

            throw new PetstoreApiException(
                message: sprintf(
                    'Petstore API returned HTTP %d for [%s] %s.',
                    $response->status(),
                    strtoupper($method),
                    '/'.ltrim($uri, '/')
                ),
                statusCode: $response->status(),
                responseBody: is_array($responseBody) ? $responseBody : null,
                previous: $exception
            );
        } catch (ConnectionException $exception) {
            throw new PetstoreApiException(
                message: 'Connection to Petstore API failed.',
                previous: $exception
            );
        }
    }

    private function send(PendingRequest $request, string $method, string $uri, array $payload): Response
    {
        return match (strtolower($method)) {
            'get' => $request->get($uri, $payload),
            'post' => $request->post($uri, $payload),
            'put' => $request->put($uri, $payload),
            'delete' => $request->delete($uri, $payload),
            default => throw new PetstoreApiException(
                message: sprintf('Unsupported HTTP method: %s', $method),
                statusCode: 0
            ),
        };
    }

    private function responseToArray(Response $response): array
    {
        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}
