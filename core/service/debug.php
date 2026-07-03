<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\single;
use fan\core\block\base;

/**
 * debug manager service
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
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
class debug extends single {

    /**
     * @var \fan\core\service\tab
     */
    protected ?object $tab = null;
    /**
     * @var HTML-code of blocks
     */
    protected ?array $blockCode = null;

    private ?object $input = null;
    private ?object $metaFileStorage = null;
    private ?\Closure $arrayAdducer = null;
    private ?object $reflectionClassFactory = null;

    public function __construct(
        bool $allowIni = true,
        ?object $tab = null,
        ?object $input = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?object $metaFileStorage = null,
        ?callable $arrayAdducer = null,
        ?object $reflectionClassFactory = null
    )
    {
        $this->tab = $tab;
        $this->input = $input;
        $this->metaFileStorage = $metaFileStorage;
        $this->arrayAdducer = \Closure::fromCallable(
            $arrayAdducer ?? static function (): array {
                throw new \RuntimeException('Array adducer is not configured for debug service.');
            }
        );
        $this->reflectionClassFactory = $reflectionClassFactory;
        parent::__construct($allowIni, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);

        $this->config['ENABLED'] = $this->isEnabled() && preg_match((string)$this->getConfig('DEBUG_IP', '/^127\.0\.0\.1$/'), (string)$this->input()->serverValue('SERVER_ADDR', ''));
    }

    private function input(): object
    {
        if ($this->input !== null) {
            return $this->input;
        }

        throw new \RuntimeException('Request input service is not configured for debug service.');
    }

    private function metaFileStorage(): object
    {
        return $this->metaFileStorage ?? throw new \RuntimeException('Meta file storage is not configured for debug service.');
    }

    public function setExtFiles(object $root, bool $mode): void
    {
        return;
    }

    public function setBlockCode(string $name, string $code): void
    {
        $this->blockCode[$name] = $code;
    }

    public function wrapHtmlCode(string $code, base $block): string
    {
        if (!$this->isEnabled()) {
            return $code;
        }
        $intColor = $block->getBlockName() === 'main' ? $this->getConfig('BORDER_MAIN', '#6600FF') : $this->getConfig('BORDER_INT', '#7F7971');

        $code = '<div><div style="background-color: ' . $intColor . '; color: ' . $this->getConfig('HEAD_TEXT', '#D6D1CA') . ';" class="debug_header"><b>' . $block->getMeta('initOrder', $this->tab->getDefaultInitNum()) . ':</b> ' . $block->getBlockName() . '</div>' . $this->_getBlockDetail($block) . '</div>' . $code;

        $code = '<div class="debug_block">' . $code . '</div>';

        return $code;
    }

    public function getSecondDebugCode(string $blockInfo, string $title): string
    {
        return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>' . $title . '</title>
<style type="text/css">
<!--/*--><![CDATA[/*><!--*/
/*]]>*/-->
</style>
</head><body>
<div id="debug2"><div>
<ul class="debug2_list">' . $blockInfo . '</ul>
</div></div>
</body></html>';
    }

    public function getSecondDebugRow(base $block, string $incl, bool $isView): string
    {
        $name = $block->getBlockName();
        $ret = '<li class="debug2_row"><span class="debug2_label"><b>' . $block->getMeta('initOrder', $this->tab->getDefaultInitNum()) . ':</b> ' . $name . '</span>';
        $ret .= '<div class="debug2_button debug_info_but">info</div>' . $this->_getBlockDetail($block);

        if (isset($this->blockCode[$name])) {
            $blockCode = htmlspecialchars($this->blockCode[$name]);
            $blockCode = str_replace(['[[[[div]]]]', '[[[[/div]]]]'], ['<div class="debug2_incl">', '</div>'], $blockCode);
        } else {
            $blockCode = '';
        }
        $ret .= '<div class="debug2_button debug_html_but">html</div><div class="debug_html">' . str_replace("\n", "<br />\n", $blockCode) . '</div>';
        if ($isView) {
            if (isset($this->blockCode[$name])) {
                $blockCode = $this->blockCode[$name];
                $blockCode = str_replace(['[[[[div]]]]', '[[[[/div]]]]'], ['<div class="debug2_incl">', '</div>'], $blockCode);
                $blockCode = preg_replace('/\<script([^>]+\/\>|[^<]+\<\/script\>)/i', '[JavaScript]', $blockCode);
            } else {
                $blockCode = '';
            }
            $ret .= '<div class="debug2_button debug_view_but">view</div><div class="debug_view">' . $blockCode . '</div>';
        }

        if ($incl) {
            $ret .= '<ul class="debug2_list">';
            foreach ($incl as $v) {
                $ret .= $v;
            }
            $ret .= '<li class="debug2_clear">&nbsp;</li></ul>';
        }

        return $ret . '</li>';
    }



    protected function _getBlockDetail(base $block): string
    {
        $refl = [
            $this->reflectionClass($block)
        ];

        $debug = $block->getDebugInfo();
        $code = '';
        $code .= $this->_getFileInfo('php-file', (string)$refl[0]->getFileName());
        $code .= $this->_getFileInfo('meta-file', (string)$debug['metaFile']);
        $code .= $this->_getFileInfo('tpl-file', (string)$debug['templateFile']);

        for ($i = 1; $i <= 10; $i++) {
            $refl[$i] = $refl[$i-1]->getParentClass();
            if (!$refl[$i]) {
                unset($refl[$i]);
                break;
            }
        }
        $code .= '<div class="debug_parents">List of parents: <ul>';
        foreach ($refl as $k => $v) {
            $code .= $this->_getParentInfo($v, $k !== 0);
        }
        $code .= '</ul></div>';

        $code .= $this->_getMetaData('Result merged META-data',  $debug['meta'], null);
        $code .= $this->_getMetaData('Container META-data',      $debug['containerMeta'], $debug['meta']);
        $code .= $this->_getMetaData('Block file META-data',     $this->_reduceMetaArray($debug['fileMeta']),   $debug['meta']);
        $code .= $this->_getMetaData('Parent classes META-data', $this->_reduceMetaArray($debug['parentMeta']), $debug['meta']);
        $code .= $this->_getMetaData('Folder META-data',         $debug['folderMeta'], $debug['meta']);


        return '<div class="debug_detail">' . $code . '</div>';
    }

    protected function _reduceMetaArray(array $meta): array
    {
        foreach (($this->arrayAdducer())($meta) as $k => $v) {
            if ($k !== 'common' && $k !== 'own') {
                unset($meta[$k]);
            }
        }

        return $meta;
    }

    private function arrayAdducer(): callable
    {
        if (!isset($this->arrayAdducer)) {
            $this->arrayAdducer = \Closure::fromCallable(
                static function (): array {
                    throw new \RuntimeException('Array adducer is not configured for debug service.');
                }
            );
        }

        return $this->arrayAdducer;
    }

    private function reflectionClass(object|string $object): \ReflectionClass
    {
        if ($this->reflectionClassFactory === null || !method_exists($this->reflectionClassFactory, 'create')) {
            throw new \RuntimeException('Reflection class factory must expose create().');
        }

        $reflection = $this->reflectionClassFactory->create($object);
        if (!$reflection instanceof \ReflectionClass) {
            throw new \UnexpectedValueException('Reflection class factory must return a ReflectionClass.');
        }

        return $reflection;
    }

    /**
     * @param string $file File path or file descriptor handled by the operation.
     */
    protected function _getFileInfo(string $label, string $file): string
    {
        $file = $file ? $this->_correctPath($file) . '<b>' . basename($file) . '</b> &nbsp;' : '<b class="debug_darkred">NONE</b>';
        return '<div class="debug_row"><label>' . $label . ':</label><span>' . $file . '</span></div>';
    }

    protected function _getParentInfo(\ReflectionClass $refl, bool $isParent): string
    {
        if ($isParent) {
            $ret = $isParent ? '-&gt; ' : '';
            $ret .= '<i class="debug_parent_class">' . $refl->getName() . '</i>';

            $file = (string)$refl->getFileName();
            $ret .= '<div>';
            $ret .= '<span>' . $this->_correctPath($file) . '<b>' . basename($file) . '</b> &nbsp;</span>';
            $file = substr($file, 0, -4) . '.meta.php';
            if ($this->metaFileStorage()->exists($file)) {
                $ret .= '<span>' . $this->_correctPath($file) . '<b>' . basename($file) . '</b> &nbsp;</span>';
            }
            $ret .= '</div>';
        } else {
            $ret = '<i>' . $refl->getName() . '</i>';
        }
        return '<li>' . $ret . '</li>';
    }

    protected function _getMetaData(string $label, array $meta, mixed $resultMeta): string
    {
        if (!is_null($resultMeta) && (!$meta || $meta === ['common' => [], 'own' => []] || $meta === ['common' => []] || $meta === ['own' => []])) {
            return '<div class="debug_meta"><div class="debug_meta_none">' . $label . '</div></div>';
        }
        return '<div class="debug_meta' . (is_null($resultMeta) ? ' debug_result_meta' : '') . '" title="Important! There are data has been formed after &quot;init-operation&quot;"><div class="debug_meta_label">' . $label . ':</div><div class="debug_array">' . $this->_showMetaArray($meta, $resultMeta, []) . '</div></div>';
    }

    protected function _showMetaArray(array $meta, mixed $resultMeta, array $keys): string
    {
        if (empty($meta)) {
            return '[]';
        }
        $ret = '[<ul>';
        foreach ($meta as $k => $v) {
            $ret .= '<li' . (!is_null($resultMeta) && !is_array($v) && $this->_checkRedefine($resultMeta, $keys, $k, $v) ? ' class="debug_redefined"' : '') . '><span class="debug_array_key">' . htmlspecialchars((string)$k) . '</span> =&gt; ';
            if (is_null($v)) {
                $ret .= 'NULL';
            } elseif (is_scalar($v)) {
                if (is_bool($v)) {
                    $v = $v ? 'true' : 'false';
                } elseif (is_string($v) && !is_numeric($v)) {
                    $v = '"' . str_replace('"', '\\"', $v) . '"';
                }
                $ret .= '<span class="debug_array_val">' . htmlspecialchars((string)$v) . '</span>';
            } elseif (is_array($v)) {
                $ret .= $this->_showMetaArray($v, $resultMeta, array_merge($keys, [$k]));
            } elseif (is_object($v)) {
                $ret .= '<span class="debug_array_instance">Instance of <b>' . get_class($v) . '</b> class</span>';
            } else {
                $ret .= var_export($v, true);
            }
            $ret .= '</li>';
        }
        return $ret . '</ul>]';
    }

    protected function _checkRedefine(mixed $resultMeta, array $keys, int|string $key, mixed $val): bool
    {
        $keys[] = $key;
        array_shift($keys);
        foreach ($keys as $v) {
            if (is_array($resultMeta) && !array_key_exists($v, $resultMeta)) {
                return true;
            }
            $resultMeta = $resultMeta[$v];
        }
        return $resultMeta !== $val;
    }

    protected function _correctPath(string $path): string
    {
        $sysSeparator = defined('DIR_SEPARATOR') ? DIR_SEPARATOR : '/';
        $path = dirname($path) . DIRECTORY_SEPARATOR;
        return str_replace($sysSeparator, DIRECTORY_SEPARATOR, $path);
    }

}
