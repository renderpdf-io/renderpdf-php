<?php

namespace RenderPdfIoPhp;

use GuzzleHttp\Client;
use Throwable;

class RenderPdfIoService
{
    public function __construct(
        /**
         * Your RenderPDF.io API Key
         *
         * Get one here
         * @see https://renderpdf.io/app/api-keys
         */
        protected readonly string $apiKey,

        /**
         * Don't set this param on your own, we do this to help us to write Unit Tests
         *
         * @internal
         */
        protected ?Client $customClient = null
    ) {
    }

    public static function make(string $apiKey): self
    {
        return new self($apiKey);
    }

    /**
     * Convert HTML to PDF file using sync mode
     *
     * @param RenderPdfOptions $options
     *
     * @return string
     *
     * @throws RenderPdfIoException on errors (validation, generic)
     */
    public function render(RenderPdfOptions $options): string
    {
        try {
            $res = $this->createHttpClient()
                ->post('render-sync', [
                    'json' => $options->toRequestData(),
                ]);

            $body = json_decode((string) $res->getBody(), true);

            return $body['fileUrl'];
        } catch (Throwable $exception) {
            throw RenderPdfIoException::fromException($exception);
        }
    }

    /**
     * Convert HTML to PDF file using sync mode
     * @note you need to create a webhook URL before using this mode https://renderpdf.io/app/webhooks
     *
     * @param RenderPdfOptions $options
     *
     * @return bool true on success
     *
     * @throws RenderPdfIoException on errors (validation, generic)
     */
    public function renderAsync(RenderPdfOptions $options): bool
    {
        if (!$options->identifier) {
            throw RenderPdfIoException::forMissingIdentifierForAsyncFlow();
        }

        try {
            $res = $this->createHttpClient()
                ->post('render-sync', [
                    'json' => $options->toRequestData(),
                ]);

            $body = json_decode((string) $res->getBody(), true);

            return $body['outcome'] === 'SUCCESS';
        } catch (Throwable $exception) {
            throw RenderPdfIoException::fromException($exception);
        }
    }

    protected function createHttpClient(): Client
    {
        return $this->customClient ?? new Client([
            'base_uri' => 'https://renderpdf.io/api/pdfs',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'User-Agent' => 'RenderPdfIoHTTP/1.0',
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
