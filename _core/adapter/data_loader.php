<?php

declare(strict_types=1);

namespace fan\core\adapter;

class data_loader
{
    private array $json = [];
    private string $text = '';
    private string $html = '';

    /**
     * @var callable
     */
    private $arrayAdducer;

    /**
     * @var callable
     */
    private $recursiveMerger;

    public function __construct(
        private object $input,
        private object $jsonEncoder,
        callable $arrayAdducer,
        callable $recursiveMerger,
        private ?object $headerWriter = null
    ) {
        $this->arrayAdducer = $arrayAdducer;
        $this->recursiveMerger = $recursiveMerger;
    }

    public function getData(): array
    {
        return $this->input->request();
    }

    public function setJson(mixed $json, bool $merge = true): static
    {
        $json = ($this->arrayAdducer)($json);
        $this->json = $merge ? ($this->recursiveMerger)($this->json, $json) : $json;

        return $this;
    }

    public function setText(mixed $text, bool $merge = true): static
    {
        $this->text = $merge ? $this->text . (string)$text : (string)$text;

        return $this;
    }

    public function setHtml(mixed $html, bool $merge = true): static
    {
        $this->html = $merge ? $this->html . (string)$html : (string)$html;

        return $this;
    }

    public function send(bool $sendHeaders = true): string
    {
        $content = $this->jsonEncoder->encode([
            'json' => $this->json,
            'text' => $this->text,
            'html' => $this->html,
        ]);

        if ($sendHeaders && !$this->headerWriter()->sent()) {
            $this->headerWriter()->send('Content-Type: application/json; charset=utf-8');
        }

        return $content;
    }

    public function getContentType(array $headers = [], bool $withCharset = false, bool $asHeader = true): string
    {
        $contentType = 'application/json' . ($withCharset ? '; charset=utf-8' : '');

        return $asHeader ? 'Content-Type: ' . $contentType : $contentType;
    }

    private function headerWriter(): object
    {
        if ($this->headerWriter === null) {
            throw new \RuntimeException('Header writer dependency is not configured for data loader.');
        }

        return $this->headerWriter;
    }
}
