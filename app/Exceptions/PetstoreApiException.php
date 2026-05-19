<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PetstoreApiException extends Exception
{
    public function __construct(
        string $message,
        int $statusCode = 0,
        protected ?array $responseBody = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function statusCode(): int
    {
        return $this->getCode();
    }

    public function responseBody(): ?array
    {
        return $this->responseBody;
    }
}

