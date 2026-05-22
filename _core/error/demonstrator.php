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

    protected array $tplVars = [];

    protected array $headers = [
        'Response'      => null,
        'ContentType'   => null,
        'AcceptRanges'  => 'Accept-Ranges: bytes',
        'ContentLength' => null,
    ];

    public function __construct($tplVars = [], $tplName = 'error_500')
    {
        $this->setTplVars($tplVars)
             ->setTplName((string)$tplName);
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
            if (file_exists($v)) {
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
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (strstr($userAgent, 'Opera') || isset($_REQUEST['notX'])) {
                $type = 'html';
            } elseif (preg_match('/application\/xhtml\+xml(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT'] ?? '', $matches1)) {
                $matches1[1] = isset($matches1[1]) ? floatval($matches1[1]) : 1;

                if (preg_match('/text\/html(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT'], $matches2)) {
                    $matches2[1] = isset($matches2[1]) ? floatval($matches2[1]) : 1;
                } elseif (preg_match('/\*\/\*(?:\s*\;\s*q=(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT'], $matches2)) {
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
        if (!headers_sent()) {
            foreach ($this->headers as $v) {
                if (!empty($v)) {
                    if (headers_sent($file, $line)) {
                        error_log('Cannot send error demonstrator header "' . $v . '": headers already sent in "' . $file . '" on line ' . $line . '.');
                    } else {
                        header($v);
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
        $data   = empty($this->dataFile) || !file_exists($this->dataFile) ? [] : (array)\fan\project\adapter\php_array_file::load($this->dataFile, []);
        $result = (string)file_get_contents($this->tplFile);
        foreach ($data as $k => $v) {
            $result = str_replace('{{' . strtoupper((string)$k) . '}}', (string)$v, $result);
        }
        $this->headers['ContentLength'] = 'Content-Length: ' . strlen($result);
        return $result;
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
            if (!strstr($_SERVER['HTTP_USER_AGENT'] ?? '', 'MSIE 6')) {
                return '<?xml version="1.0" encoding="' . $this->charset . '"?>';
            }
            return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        } elseif ($this->contentType === 'html') {
            return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
        }
        return '';
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
