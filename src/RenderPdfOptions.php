<?php

namespace RenderPdfIoPhp;

class RenderPdfOptions
{
    public function __construct(
        public string $htmlContent,
        public ?string $headerHtmlContent = null,
        public ?string $footerHtmlContent = null,
        public ?string $paperWidth = null,
        public ?string $paperHeight = null,
        public ?string $marginTop = null,
        public ?string $marginLeft = null,
        public ?string $marginRight = null,
        public ?string $marginBottom = null,
        public ?string $scale = null,
        public ?string $identifier = null,
        public bool $landscape = false,
        public bool $printBackground = false,
    ) {
    }

    public function toRequestData(): array
    {
        return array_filter(get_object_vars($this));
    }
}
