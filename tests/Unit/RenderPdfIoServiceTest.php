<?php

namespace RenderPdfIoPhp\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use RenderPdfIoPhp\RenderPdfIoException;
use RenderPdfIoPhp\RenderPdfIoService;
use RenderPdfIoPhp\RenderPdfOptions;
use RenderPdfIoPhp\Tests\TestCase;

class RenderPdfIoServiceTest extends TestCase
{
    public static function renderThrowsExceptionOnErrorDataProvider(): array
    {
        return [
            'validation error' => [
                new Response(422, body: json_encode([
                    'errors' => [
                        'html_content' => [
                            'Invalid HTML Content',
                        ],
                    ],
                ])),
                'Invalid HTML Content'
            ],
            'rate limited error' => [
                new Response(429),
                'You have exceeded the API usage for the current minute.'
            ],
            'generic error' => [
                new Response(500),
                'Failed to render your PDF file'
            ],
        ];
    }

    /**
     * @dataProvider renderThrowsExceptionOnErrorDataProvider
     */
    public function testRenderThrowsExceptionOnError(Response $response, string $expectedErrorMsg): void
    {
        $this->expectException(RenderPdfIoException::class);
        $this->expectExceptionMessage($expectedErrorMsg);

        $mock = MockHandler::createWithMiddleware([$response]);
        $client = new Client(['handler' => $mock]);

        $service = new RenderPdfIoService('fake-api-key', $client);
        $service->render(new RenderPdfOptions(''));
    }

    public function testRenderReturnsFileUrl(): void
    {
        $mock = MockHandler::createWithMiddleware([
            new Response(201, body: json_encode([
                'outcome' => 'SUCCESS',
                'fileUrl' => 'https://renderpdf.io/fake.pdf',
            ])),
        ]);
        $client = new Client(['handler' => $mock]);

        $service = new RenderPdfIoService('fake-api-key', $client);
        $fileUrl = $service->render(new RenderPdfOptions('render this plz'));

        $this->assertSame('https://renderpdf.io/fake.pdf', $fileUrl);
    }

    public function testRenderAsyncThrowsExceptionOnMissingIdentifier(): void
    {
        $this->expectException(RenderPdfIoException::class);
        $this->expectExceptionMessage('You must set an unique identifier when using the async mode.');

        $service = RenderPdfIoService::make('fake-api-key');
        $service->renderAsync(new RenderPdfOptions('hihi'));
    }

    /**
     * @dataProvider renderThrowsExceptionOnErrorDataProvider
     */
    public function testRenderAsyncThrowsExceptionOnError(Response $response, string $expectedErrorMsg): void
    {
        $this->expectException(RenderPdfIoException::class);
        $this->expectExceptionMessage($expectedErrorMsg);

        $mock = MockHandler::createWithMiddleware([$response]);
        $client = new Client(['handler' => $mock]);

        $service = new RenderPdfIoService('fake-api-key', $client);
        $service->renderAsync(new RenderPdfOptions(
            '',
            identifier: 'hehe'
        ));
    }

    public function testRenderAsyncReturnsOk(): void
    {
        $mock = MockHandler::createWithMiddleware([
            new Response(200, body: json_encode([
                'outcome' => 'SUCCESS',
            ])),
        ]);
        $client = new Client(['handler' => $mock]);

        $service = new RenderPdfIoService('fake-api-key', $client);
        $isQueued = $service->renderAsync(new RenderPdfOptions(
            'render this plz',
            identifier: 'hihi'
        ));

        $this->assertTrue($isQueued);
    }
}
