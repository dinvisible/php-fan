<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;

/**
 * Description of JSON
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class json extends multi
{
    public const DECODE_OPT_PHP_VERSION = '5.4.0';

    /**
     * Error Code
     * @var integer
     */
    protected ?int $errorCode = null;

    /**
     * Use Base64
     * @var boolean
     */
    protected bool $useBase64 = false;

    private mixed $errorFactory = null;

    public function __construct(
        bool $useBase64,
        ?callable $errorFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        $this->errorFactory = $errorFactory;
        parent::__construct(true, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);
        $this->useBase64 = (bool)$useBase64;
    }

    // ======== Main Interface methods ======== \\

    public function decode(string $json, bool $array = true, mixed $depth = null, mixed $options = null): mixed
    {
        $this->errorCode = null;
        if (is_null($depth)) {
            $depth = $this->getConfig('DEPTH', 25);
        }
        if (is_null($options)) {
            $options = $this->getConfig('DECODE_OPTIONS', 0);
        }
        if ($this->getConfig('ALLOW_INTERNAL', true)) {
            $result = version_compare(PHP_VERSION, self::DECODE_OPT_PHP_VERSION) < 0 ?
                    json_decode($json, $array, (int)$depth) :
                    json_decode($json, $array, (int)$depth, (int)$options);
            $this->errorCode = json_last_error();
            if ($this->useBase64 && is_array($result)) {
                array_walk_recursive($result, [$this, '_code64'], 'decode');
            }
            return $result;
        }
        // ToDo: make special procedures for JSON-decode
        throw new \LogicException('JSON-decode is supported by internal functions yet.');
    }

    public function encode(mixed $sourse, mixed $options = null, mixed $logError = true): string|false
    {
        $this->errorCode = JSON_ERROR_NONE;
        if (is_null($options)) {
            $options = $this->getConfig('ENCODE_OPTIONS', 0);
        }
        if ($this->getConfig('ALLOW_INTERNAL', true)) {
            if ($this->useBase64 && is_array($sourse)) {
                array_walk_recursive($sourse, [$this, '_code64'], 'encode');
                // ToDo: This method doesn't work with object
            }
            $result = json_encode($sourse, (int)$options);
            $this->errorCode = json_last_error();
            if ((int)$this->errorCode !== JSON_ERROR_NONE && $logError) {
                $this->errorService()->logErrorMessage(
                        $this->getErrorText(),
                        'JSON error',
                        '',
                        true,
                        false
                );
            }
            return $result;
        }
        // ToDo: make special procedures for JSON-encode
        throw new \LogicException('JSON-encode is supported by internal functions yet.');
    }

    public function fromXml(mixed $xml, bool $ignoreXmlAttributes = true): void
    {
        // ToDo: make procedures for encode XML to JSON
    }

    public function fromYaml(mixed $yaml): void
    {
        // ToDo: make procedures for encode YAML to JSON
    }

    public function isError(): bool
    {
        return (int)$this->errorCode !== JSON_ERROR_NONE;
    }

    public function getError(): ?int
    {
        return $this->errorCode;
    }

    public function getErrorText(): string
    {
        switch ($this->getError()) {
        case JSON_ERROR_DEPTH:
            return 'The maximum stack depth has been exceeded';
        case JSON_ERROR_STATE_MISMATCH:
            return 'Invalid or malformed JSON';
        case JSON_ERROR_CTRL_CHAR:
            return 'Control character error, possibly incorrectly encoded';
        case JSON_ERROR_SYNTAX:
            return 'Syntax error';
        case JSON_ERROR_UTF8:
            return 'Malformed UTF-8 characters, possibly incorrectly encoded';
        }
        return 'No error has occurred';
    }

    public function prettyPrint(string $json, string $indent = "\t"): string
    {
        $json = (string)$json;
        $indent = (string)$indent;
        $tokens  = preg_split('/([\{\}\]\[,])/', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result  = '';
        $indSize = 0;

        foreach ($tokens as $v) {
            if ($v === '') {
                continue;
            }
            $prefix = str_repeat($indent, $indSize);
            if ($v === '{' || $v === '[') {
                $indSize++;
                if ($result !== '' && $result[strlen($result) - 1] === "\n") {
                    $result .= $prefix;
                }
                $result .= $v . "\n";
            } else if ($v === '}' || $v === ']') {
                $indSize--;
                $prefix = str_repeat($indent, $indSize);
                $result .= "\n" . $prefix . $v;
            } else if ($v === ',') {
                $result .= $v . "\n";
            } else {
                $result .= $prefix . $v;
            }
        }
        return $result;
   }

    // ======== Private/Protected methods ======== \\
    protected function _code64(mixed &$v, mixed $k, string $op): void
    {
        if (is_scalar($v)) {
            $v = $op === 'encode' ? base64_encode((string)$v) : base64_decode((string)$v);
        }
    }

    private function errorService(): object
    {
        if (!is_callable($this->errorFactory)) {
            throw new \RuntimeException('Error service factory is not configured for json service.');
        }

        return ($this->errorFactory)();
    }
}
