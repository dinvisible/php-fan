<?php
declare(strict_types=1);

namespace fan\core\service\template\parser;
/**
 * Template parser engine main
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
 * @version of file: 05.02.003 (16.04.2014)
 */
class main extends base
{
    protected array $tagList = ['assign', 'if', 'elseif', 'foreach', 'for', 'msg', 'uri', 'get_url'];

    public function parse_assign(string $data): string
    {
        $param = $this->getStandardParam($data, ['var', 'value'], ['var']);
        $code = '';
        if (preg_match('/^[a-z]\w+/i', $param['var'], $matches)) {
            $code .= '$' . $matches[0] . '=&$this->linkForAssign(\'' . $matches[0] . '\');';
        }
        return $code . '$' . $param['var'] . '=' . $param['value'] . ';' . "\n";
    }

    public function parse_if(string $data): string
    {
        return 'if (' . $data . "):\n";
    }

    public function parse_elseif(string $data): string
    {
        return 'elseif (' . $data . "):\n";
    }

    public function parse_foreach(string $data): string
    {
        $param = $this->getStandardParam($data, ['from', 'item'], ['item', 'key']);
        $code = empty($param['name']) ? '' : '$this->setObjectData(\'foreach\', ' . $param['name'] . ', ' . $param['from'] . ');';
        $code .= 'foreach (' . $param['from'] . ' as ' . (empty($param['key']) ? '' : '$' . $param['key'] . '=>') . '$' . $param['item'] . '):' . "\n";
        if (!empty($param['name'])) {
            $code .= '$this->setIteration(' . $param['name'] . ');' . "\n";
        }
        return $code;
    }

    public function parse_for(string $data): string
    {
        $param = $this->getStandardParam($data);
        $code  = empty($param['name']) ? '' : '$this->setObjectData(\'for\', ' . $param['name'] . ');';
        $code .= 'for (' . (empty($param['start']) ? '' : $param['start']) . ';';
        $code .= (empty($param['condition']) ? '' : $param['condition']) . ';';
        $code .= (empty($param['each']) ? '' : $param['each']) . '):' . "\n";
        if (!empty($param['name'])) {
            $code .= '$this->setIteration(' . $param['name'] . ');' . "\n";
        }
        return $code;
    }

    public function parse_msg(string $data): string
    {
        return '$returnHtmlVal.=msg(' . implode(',', $this->getSimpleParam($data)) . ");\n";
    }

    public function parse_uri(string $data): string
    {
        return '$returnHtmlVal.=$this->block->getTab()->getURI(' . implode(',', $this->getSimpleParam($data)) . ");\n";
    }

    public function parse_getURI(string $data): string
    {
        return $this->parse_get_url($data);
    }

    public function parse_get_url(string $data): string
    {
        $param = $this->getStandardParam($data, ['url'], ['type']);
        if (!isset($param['type'])) {
            $param['type'] = 'link';
        }
        $param['use_sid']  = array_key_exists('use_sid',  $param) ? (empty($param['use_sid'])  ? 'false' : 'true') : 'null';
        $param['protocol'] = array_key_exists('protocol', $param) ? (empty($param['protocol']) ? 'false' : 'true') : 'null';
        return '$returnHtmlVal.=service_container()->get(\'tab\')->getURI(' . $param['url'] . ',\'' . $param['type'] . '\',' . $param['use_sid'] . ',' . $param['protocol'] . ");\n";
    }

}
