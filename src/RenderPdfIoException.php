<?php

namespace RenderPdfIoPhp;

use GuzzleHttp\Exception\ClientException;
use RuntimeException;
use Throwable;

class RenderPdfIoException extends RuntimeException
{
    public static function fromException(Throwable $exception): self
    {
        if ($exception instanceof ClientException) {
            return match ($exception->getCode()) {
                422 => self::forValidationErrors(
                    json_decode((string) $exception->getResponse()->getBody(), true)
                ),
                429 => self::forRateLimited(),
                default => self::forGeneric($exception),
            };
        }

        return self::forGeneric($exception);
    }

    public static function forGeneric(?Throwable $previous = null): self
    {
        return new self('Failed to render your PDF file', previous: $previous);
    }

    public static function forValidationErrors(array $errorsBag): self
    {
        $firstErrors = array_shift($errorsBag['errors']);

        return new self($firstErrors[0]);
    }

    public static function forRateLimited(): self
    {
        return new self('You have exceeded the API usage for the current minute.');
    }

    public static function forMissingIdentifierForAsyncFlow(): self
    {
        return new self('You must set an unique identifier when using the async mode.');
    }
}
