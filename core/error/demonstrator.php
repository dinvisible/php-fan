<?php

declare(strict_types=1);

namespace fan\core\error;
/**
 * Description of demonstrator
 *
 * This file is part PHP-FAN (php-framework from Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.001 (10.03.2014)
 */
class demonstrator
{

    public const CORE_TEMPLATE    = '{CORE_DIR}/error/template/{TPL_NAME}.html';
    public const PROJECT_TEMPLATE = '{PROJECT_DIR}/error/template/{TPL_NAME}.html';
    public const DEFAULT_NAME     = 'default';

    protected array $responseCode = [
        200 => 'HTTP/1.1 200 OK',
        400 => 'HTTP/1.1 400 Bad Request',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        500 => 'HTTP/1.1 500 Internal Server Error',
    ];

    protected array $contentTypes = [
        'text'  => 'text/plain',
        'html'  => 'text/html',
        'xhtml' => 'application/xhtml+xml',
        'xml'   => 'text/html',
    ];

    protected ?string $contentType = null;

    protected string $charset = 'utf-8';

    protected ?string $dataFile = null;
    protected ?string $tplFile = null;
    protected ?string $tplName = null;

    protected array $tplVars = [];

    protected array $headers = [
        'Response'      => null,
        'ContentType'   => null,
        'AcceptRanges'  => 'Accept-Ranges: bytes',
        'ContentLength' => null,
    ];

    private ?object $input = null;

    private $phpArrayFileLoader = null;

    private ?object $headerWriter = null;

    private ?object $errorLogWriter = null;

    private ?object $fileStorage = null;

    public function __construct(
        $tplVars = [],
        $tplName = 'error_500',
        ?object $input = null,
        ?callable $phpArrayFileLoader = null,
        ?object $headerWriter = null,
        ?object $errorLogWriter = null,
        ?object $fileStorage = null
    )
    {
        $this->input = $input;
        if ($phpArrayFileLoader !== null) {
            $this->setPhpArrayFileLoader($phpArrayFileLoader);
        }
        if ($headerWriter !== null) {
            $this->setHeaderWriter($headerWriter);
        }
        if ($errorLogWriter !== null) {
            $this->setErrorLogWriter($errorLogWriter);
        }
        if ($fileStorage !== null) {
            $this->setFileStorage($fileStorage);
        }
        $this->setTplVars($tplVars)
             ->setTplName((string)$tplName);
    }

    public function setPhpArrayFileLoader(callable $phpArrayFileLoader): static
    {
        $this->phpArrayFileLoader = $phpArrayFileLoader;

        return $this;
    }

    public function setHeaderWriter(object $headerWriter): static
    {
        $this->headerWriter = $headerWriter;

        return $this;
    }

    public function setErrorLogWriter(object $errorLogWriter): static
    {
        $this->errorLogWriter = $errorLogWriter;

        return $this;
    }

    public function setFileStorage(object $fileStorage): static
    {
        $this->fileStorage = $fileStorage;
        if ($this->tplName !== null && $this->tplFile === null) {
            $this->setTplName($this->tplName);
        }

        return $this;
    }


    public function setTplVars(mixed $tplVars): static
    {
        if (!is_array($tplVars)) {
            $tplVars = is_scalar($tplVars) ? ['var' => $tplVars] : (array)$tplVars;
        }
        $this->tplVars = array_merge($this->tplVars, $tplVars);
        return $this;
    }

    public function getTplVar(?string $key = null): mixed
    {
        return empty($key) ? $this->tplVars : (isset($this->tplVars[$key]) ? $this->tplVars[$key] : '');
    }

    public function setTplName(mixed $tplFile = null): static
    {
        $this->tplName = $tplFile === null ? null : (string)$tplFile;
        if ($this->fileStorage === null) {
            return $this;
        }

        if (empty($tplFile)) {
            $math = [
                str_replace(['{CORE_DIR}', '{TPL_NAME}'], [CORE_DIR, self::DEFAULT_NAME], self::CORE_TEMPLATE),
            ];
        } else {
            $tplFile = (string)$tplFile;
            $math = [
                str_replace(['{PROJECT_DIR}', '{TPL_NAME}'], [PROJECT_DIR, $tplFile], self::PROJECT_TEMPLATE),
                str_replace(['{CORE_DIR}',    '{TPL_NAME}'], [CORE_DIR,    $tplFile], self::CORE_TEMPLATE),
                $tplFile,
                str_replace(['{CORE_DIR}',    '{TPL_NAME}'], [CORE_DIR,    self::DEFAULT_NAME], self::CORE_TEMPLATE),
            ];
        }
        foreach ($math as $v) {
            if ($this->fileStorage()->exists($v)) {
                $this->dataFile = substr($v, 0, -4) . 'php';
                $this->tplFile  = $v;
                break;
            }
        }
        return $this;
    }

    public function setResponseHeader($code): string
    {
        $code = is_numeric($code) ? (int)$code : 500;
        if (!isset($this->responseCode[$code])) {
            $code = 500;
        }
        $this->headers['Response'] = $this->responseCode[$code];
        return '';
    }

    public function setContentType($type): string
    {
        $type = (string)$type;
        if (!isset($this->contentTypes[$type])) {
            $type = 'text';
        } elseif ($type === 'xhtml') {
            $userAgent = (string)$this->input()->serverValue('HTTP_USER_AGENT', '');
            $accept = (string)$this->input()->serverValue('HTTP_ACCEPT', '');
            if (strstr($userAgent, 'Opera') || $this->input()->requestValue('notX') !== null) {
                $type = 'html';
            } elseif (preg_match('/application\/xhtml\+xml(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $accept, $matches1)) {
                $matches1[1] = isset($matches1[1]) ? floatval($matches1[1]) : 1;

                if (preg_match('/text\/html(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $accept, $matches2)) {
                    $matches2[1] = isset($matches2[1]) ? floatval($matches2[1]) : 1;
                } elseif (preg_match('/\*\/\*(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $accept, $matches2)) {
                    $matches2[1] = isset($matches2[1]) ? floatval($matches2[1]) : 1;
                } else {
                    $matches2[1] = 0;
                }

                if ($matches1[1] < $matches2[1]) {
                    $type = 'html';
                }
            } else {
                $type = 'html';
            }
        }
        $this->contentType = $type;
        $this->headers['ContentType'] = 'Content-Type: ' . $this->contentTypes[$type] . '; charset=' . $this->charset;
        return '';
    }

    public function setOptionalHeader($header): static
    {
        if (!empty($header)) {
            $this->headers[] = (string)$header;
        }
        return $this;
    }

    public function outputHeaders(): void
    {
        $headerWriter = $this->headerWriter();
        if (!$headerWriter->sent()) {
            foreach ($this->headers as $v) {
                if (!empty($v)) {
                    $file = null;
                    $line = null;
                    if ($headerWriter->sent($file, $line)) {
                        $this->errorLogWriter()->write('Cannot send error demonstrator header "' . $v . '": headers already sent in "' . $file . '" on line ' . $line . '.');
                    } else {
                        $headerWriter->send($v);
                    }
                }
            }

        }
    }

    public function getTplContent(): ?string
    {
        if (empty($this->tplFile)) {
            return null;
        }
        $data   = empty($this->dataFile) || !$this->fileStorage()->exists($this->dataFile) ? [] : (array)$this->loadPhpArrayFile($this->dataFile, []);
        $result = (string)$this->fileStorage()->read($this->tplFile);
        foreach ($data as $k => $v) {
            $result = str_replace('{{' . strtoupper((string)$k) . '}}', (string)$v, $result);
        }
        $this->headers['ContentLength'] = 'Content-Length: ' . strlen($result);
        return $result;
    }

    private function headerWriter(): object
    {
        if ($this->headerWriter === null) {
            throw new \RuntimeException('Header writer dependency is not configured for error demonstrator.');
        }

        return $this->headerWriter;
    }

    private function errorLogWriter(): object
    {
        if ($this->errorLogWriter === null) {
            throw new \RuntimeException('Error log writer dependency is not configured for error demonstrator.');
        }

        return $this->errorLogWriter;
    }

    private function fileStorage(): object
    {
        if ($this->fileStorage === null) {
            throw new \RuntimeException('File storage dependency is not configured for error demonstrator.');
        }

        return $this->fileStorage;
    }

    private function loadPhpArrayFile(string $path, mixed $default = null): mixed
    {
        if (!$this->fileStorage()->exists($path)) {
            return $default;
        }

        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP-array file loader is not configured for error demonstrator.');
        }

        return ($this->phpArrayFileLoader)($path, $default, $this);
    }

    public function showTplContent(): ?string
    {
        $content = $this->getTplContent();
        $this->outputHeaders();
        echo $content;
        return $content;
    }

    public function setDoctype(): string
    {
        if ($this->contentType === 'xhtml') {
            if (!strstr((string)$this->input()->serverValue('HTTP_USER_AGENT', ''), 'MSIE 6')) {
                return '<?xml version="1.0" encoding="' . $this->charset . '"?>';
            }
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        } elseif ($this->contentType === 'html') {
            return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        }
        return '';
    }

    private function input(): object
    {
        return $this->input ?? throw new \RuntimeException('Input dependency is not configured for error demonstrator.');
    }

    public function convArrayToSting(mixed $src, string $glue = "\n"): string
    {
        $ret = '';
        if (is_array($src)) {
            foreach ($src as $v) {
                if (!empty($ret)) {
                    $ret .= $glue;
                }
                if (is_scalar($v)) {
                    $ret .= $v;
                } elseif (is_array($v)) {
                    $ret .= $this->convArrayToSting($v, $glue);
                } else {
                    $ret .= strval($v);
                }
            }
        } elseif (is_scalar($src)) {
            $ret = (string)$src;
        }
        return $ret;
    }
}
