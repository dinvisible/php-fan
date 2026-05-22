<?php

declare(strict_types=1);

namespace fan\core\adapter;

class data_loader
{
    use \fan\core\di\container_aware_trait;

    private array $json = [];
    private string $text = '';
    private string $html = '';

    public function getData(): array
    {
        return $_REQUEST;
    }

    public function setJson(mixed $json, bool $merge = true): static
    {
        $json = adduceToArray($json);
        $this->json = $merge ? array_merge_recursive_alt($this->json, $json) : $json;

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
        $content = $this->containerService('json')->encode([
            'json' => $this->json,
            'text' => $this->text,
            'html' => $this->html,
        ]);

        if ($sendHeaders && !headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        return $content;
    }

    public function getContentType(array $headers = [], bool $withCharset = false, bool $asHeader = true): string
    {
        $contentType = 'application/json' . ($withCharset ? '; charset=utf-8' : '');

        return $asHeader ? 'Content-Type: ' . $contentType : $contentType;
    }
}
