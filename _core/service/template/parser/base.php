<?php
declare(strict_types=1);

namespace fan\core\service\template\parser;
use fan\project\exception\service\fatal as fatalException;
/**
 * Template parser engine base
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
abstract class base
{
    /**
     * @var \fan\core\service\template Service template instance
     */
    protected ?object $facade = null;

    protected array $tagList = [];

    protected array $autoTagList = [];


    public function setFacade(\fan\core\service\template $facade): void
    {
        $this->facade = $facade;
    }

    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        $tagName = substr($method, 6);
        $autoData = $this->autoTagList[$tagName] ?? null;
        if (substr($method, 0, 6) === 'parse_' && $autoData) {
            return '$returnHtmlVal.=$this->' . $autoData['method'] . '(' . $this->getDynamicArray((string)($args[0] ?? ''), $autoData['require'] ?? []) . ');' . "\n";
        }
        $parseData = $this->facade->getParseData();
        throw new fatalException($this->facade, 'Call for undefined method "' . $method . '".
At the file "' . $parseData['template'] . '".
Near "' . $parseData['part'] . '". At the file "');
    }

    public function getTagList(): array
    {
        return $this->tagList;
    }

    public function setAutoTag(string $tagName, array $data): bool
    {
        $this->autoTagList[(string)$tagName] = $data;
        return true;
    }

    protected function getSimpleParam(string $data): array
    {
        $ret = [];
        if (preg_match_all(\fan\project\service\template::paramSimplePcre, $data, $matches)) {
            foreach ($matches[0] as $i => $val) {
                $ret[] = $matches[2][$i] === '"' ?
                    stripslashes(substr($matches[1][$i], 1, -1)) :
                    (empty($matches[2][$i]) && substr($matches[1][$i], 0, 1) !== '$' ?
                        '\'' . $matches[1][$i] . '\'' :
                        $matches[1][$i]
                    );
            }
        }
        return $ret;
    }

    protected function getStandardParam(string $data, array $require = [], array $strip = []): array
    {
        $ret = [];
        if (preg_match_all(\fan\project\service\template::paramStandardPcre, $data, $matches)) {
            foreach ($matches[0] as $i => $val) {
                $ret[$matches[1][$i]] = $matches[3][$i] === '"' ?
                    stripslashes(substr($matches[2][$i], 1, -1)) :
                    (empty($matches[3][$i]) && substr($matches[2][$i], 0, 1) !== '$' ?
                        '\'' . $matches[2][$i] . '\'' :
                        $matches[2][$i]
                    );
            }
        }
        if ($require) {
            foreach ($require as $k) {
                if (!isset($ret[$k])) {
                    $parseData = $this->facade->getParseData();
                    throw new fatalException($this->facade, 'Undefined required control key "' . $k . '".
At the file "' . $parseData['template'] . '".
Near "' . $parseData['part'] . '". At the file "');
                }
            }
        }
        foreach ($strip as $k) {
            if (isset($ret[$k]) && substr($ret[$k], 0, 1) === '\'') {
                $ret[$k] = substr($ret[$k], 1, -1);
            }
        }
        return $ret;
    }

    protected function getDynamicArray(string $data, array $require = []): string
    {
        if (!$data) {
            return '';
        }
        $ret = '[';
        foreach ($this->getStandardParam($data, $require) as $k => $v) {
            $ret .= '\'' . $k . '\'=>' . $v . ',';
        }
        return substr($ret, 0, -1) . ']';
    }

}
