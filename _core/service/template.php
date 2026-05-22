<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Template manager service
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
 * @version of file: 05.02.008 (15.09.2015)
 */
class template extends \fan\core\base\service\single
{
    /**
     * Constant of PCRE for plain code
     */
    public const plainPcre   = '/(.*?)(?:\{(?:(\@?)(\$)|(\-\>)|\=)([^\}]+)\s*\}|$)/s';

    /**
     * Constant of PCRE for control structure
     */
    public const controlPcre = '/(.*?)(?:\{(?:({TAG_LIST})(?:\s+([^\}]*))?|(else)|\/(if|for|foreach))\s*\}|$)/s';

    /**
     * Constant of PCRE for simple parameters
     */
    public const paramSimplePcre   = '/\s*(([\'\"])?(?(2).+?(?<!\\\\)\2|\$?\w+(?:\[[^\]]+\])*))(?:\s+|$)/si';

    /**
     * Constant of PCRE for standard parameters (as hash)
     */
    public const paramStandardPcre = '/([a-z_0-9]+)\s*\=\s*(([\'\"])?(?(3).+?(?<!\\\\)\3|\$?\w+(?:\[[^\]]+\])*))(?:\s+|$)/si';

    /**
     * List of corresspond tags to Engines
     * @var array
     */
    protected array $tags = [];

    /**
     * List of literals
     * @var array
     */
    protected array $literals = [];

    /**
     * Parse data
     * @var array
     */
    protected array $parseData = [];

    private string $templateClass = '<?php namespace {NAMESPACE};
//SRC: {SOURCE_PATH}
class {CLASS_NAME} extends {PARENT_CLASS}{
	protected function parseHtml(): mixed{
	foreach($this->tplVar as $assignTplKey=>&$assignTplVal){$$assignTplKey=&$assignTplVal;}
	$returnHtmlVal = \'\';
	{COMPILE_CODE}
return $returnHtmlVal;}
{ADD_METHODS}
}
?>';

    /**
     * @throws fatalException
     */
    public function get(string $templatePath, ?string $parent = null, ?\fan\core\block\base $block = null): \fan\core\service\template\type\base
    {
        if (!is_readable($templatePath)) {
            throw new fatalException($this, 'Template file "' . $templatePath . '".');
        }
        list ($nameSpace, $className, $fileName) = $this->_getClassAttributes($templatePath, $block);

        $compilePath = \bootstrap::parsePath((string)$this->getConfig('CACHE_DIR')) . $fileName . '.php';
        if (
            !file_exists($compilePath) ||
            filemtime($compilePath) < filemtime($templatePath) ||
            !$this->isCompiledTemplateCurrent($compilePath)
        ) {
            $this->_makeClass($templatePath, $compilePath, $nameSpace, $className, (string)($parent ? $parent : $this->getConfig('PARENT_CLASS')));
        }
        $className = '\\' . $nameSpace . '\\' . $className;
        \fan\project\adapter\compiled_template_loader::load($className, $compilePath);
        return new $className($block);
    }

    public function getParseData(): array
    {
        return $this->parseData;
    }

    public function disableStrip(bool $allowStrip = false): void
    {
        $this->config['USE_STRIP'] = !empty($allowStrip);
    }

    // ------------------------- \\

    protected function parse_literal(string $data): string
    {
        return !empty($this->literals[$data]) ? '$returnHtmlVal.=' . $this->_addSlashes($this->literals[$data]) . ";\n" : '';
    }

    protected function parse_plain(string $data): string
    {
        $ret = '';
        if (preg_match_all(self::plainPcre, $data, $matches)) {
            foreach ($matches[0] as $i => $val) {
                if (!empty($matches[1][$i])) {
                    $ret .=  $this->_addSlashes($matches[1][$i]) . '.';
                }
                if (!empty($matches[5][$i])) {
                    $ret .= '(';
                    if (!empty($matches[3][$i])) {
                        $ret .= ($matches[2][$i] ?? '') . '$';
                    } elseif (!empty($matches[4][$i])) {
                        $ret .= '$this->';
                    }
                    $ret .= $matches[5][$i] . ').';
                }
            }
        }
        return $ret ? '$returnHtmlVal.=' . substr($ret, 0, -1) . ";\n" : '';
    }

    // ------------------------- \\

    protected function _getClassAttributes(string $templatePath, mixed $block): array
    {
        if (is_object($block) || is_string($block)) {
            $mainName = get_class_name($block);
        } else {
            $mainName = basename($templatePath, '.tpl');
            $mainName = preg_replace('/\W/', '_', $mainName);
        }
        $mainName = (string)$mainName;
        $className = $mainName . '__' . substr(md5($templatePath), 0, (int)$this->getConfig('UNIQUE_KEY_LENGH'));
        $fileName  = $className;

        $nameSpace = (string)$this->getConfig('NameSpace');

        return [$nameSpace, $className, $fileName];
    }

    protected function _makeClass(string $templatePath, string $compilePath, string $nameSpace, string $className, string $type): void
    {
        $this->parseData['template'] = $templatePath;
        // Prepare engines
        foreach (call_user_func([$type, 'getEngineList']) as $engineName) {
            $engine = $this->_getEngine('parser\\' . $engineName);
            foreach ($engine->getTagList() as $tagName) {
                $this->tags[$tagName] = $engine;
            }
        }
        foreach (call_user_func([$type, 'getAutoParseTag']) as $tagName => $autoData) {
            if (!isset($this->tags[$tagName]) && isset($engine) && $engine->setAutoTag($tagName, $autoData)) { // ToDo: Explore "setAutoTag" there
                $this->tags[$tagName] = $engine;
            }
        }
        $this->tags['literal'] = $this;

        $srcCode = (string)file_get_contents($templatePath);
        // Get methods
        $addMethods = '';
        if (preg_match_all('/\{method:\s*(.*?)[\n\r\s]+endmethod\}/s', $srcCode, $matches)) {
            foreach ($matches[0] as $i => $v) {
                $addMethods .= $matches[1][$i];
                $srcCode = str_replace($matches[0][$i], '', $srcCode);
            }
        }

        // Remove comments
            $srcCode = preg_replace('/\{\*.*?\*\}/s', '', $srcCode) ?? $srcCode;

        // Save literals
        if (preg_match_all('/\{literal\}(.*?)\{\/literal\}/s', $srcCode, $matches)) {
            foreach ($matches[0] as $i => $v) {
                $this->literals[$i] = $matches[1][$i];
                $srcCode = str_replace($matches[0][$i], '{literal ' . $i . '}', $srcCode);
            }
        }
        // Strip code, except "nostrip"
        if ($this->getConfig('USE_STRIP')) {
            $noStrip = [];
            if (preg_match_all('/\{nostrip\}(.*?)\{\/nostrip\}/s', $srcCode, $matches)) {
                foreach ($matches[0] as $i => $v) {
                    $k = '{nostrip ' . $i . '}';
                    $noStrip[$k] = $matches[1][$i];
                    $srcCode = str_replace($matches[0][$i], $k, $srcCode);
                }
            }
            $srcCode = preg_replace('/\s{2,}/', ' ', str_replace(["\r", "\n"], ['', ' '], $srcCode)) ?? $srcCode;
            foreach ($noStrip as $k => $v) {
                $srcCode = str_replace($k, $v, $srcCode);
            }
        }
        // get Compile Code by other tags
        $code = '';
        if (preg_match_all(str_replace('{TAG_LIST}', implode('|', array_keys($this->tags)), self::controlPcre), $srcCode, $matches)) {
            foreach ($matches[0] as $i => $val) {
                $this->parseData['part'] = $val;
                if (!empty($matches[1][$i])) {
                    $code .= $this->parse_plain($matches[1][$i]);
                }
                if (!empty($matches[2][$i])) {
                    $code .= call_user_func([$this->tags[$matches[2][$i]], 'parse_' . $matches[2][$i]], (string)($matches[3][$i] ?? ''));
                }
                if (!empty($matches[4][$i])) {
                    $code .= $matches[4][$i] . ":\n";
                }
                if (!empty($matches[5][$i])) {
                    $code .= 'end' . $matches[5][$i] . ";\n";
                }
            }
        }
        $code = str_replace(['{ldelim}', '{rdelim}'], ['{', '}'], $code);

        // Save file
        file_put_contents($compilePath, str_replace(['{SOURCE_PATH}', '{NAMESPACE}', '{CLASS_NAME}', '{PARENT_CLASS}', '{COMPILE_CODE}', '{ADD_METHODS}'], [$templatePath, $nameSpace, $className, $type, $code, $addMethods], $this->templateClass));
        $this->tags = $this->literals = [];
    }

    protected function _addSlashes(string $data): string
    {
        return '\'' . str_replace(['\\', '\''], ['\\\\', '\\\''], $data) . '\'';
    }

    protected function isCompiledTemplateCurrent(string $compilePath): bool
    {
        $source = file_get_contents($compilePath);

        return is_string($source) && str_contains($source, 'function parseHtml(): mixed');
    }
}
