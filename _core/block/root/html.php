<?php

declare(strict_types=1);

namespace fan\core\block\root;
/**
 * Base abstract root html block
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
 * @version of file: 05.02.010 (28.09.2015)
 * @abstract
 */
abstract class html extends \fan\core\block\base
{
    /**
     * Name of block
     * @var string
     */
    protected string $modalWin = '';

    /**
     * External CSS
     * @var array
     */
    protected array $externalCSS = [
        'link'  => [
            'all' => [],
        ],
        'style' => [
            'all' => [],
        ],
        'ie'    => [
            'all' => [],
        ],
    ];

    /**
     * Embeded CSS-data by media-type
     * @var array
     */
    protected array $embedCSS = ['all' => ''];

    /**
     * External JS
     * @var array
     */
    protected array $externalJS = [
        'head' => [],
        'body' => [],
    ];

    /**
     * Embeded JS-data for head and body
     * @var type
     */
    protected array $embedJS = [
        'head' => null,
        'body' => null,
    ];

    /**
     * List of CSS-media type
     * @var array
     */
    protected array $cssMedia = ['all', 'braille', 'handheld', 'print', 'screen', 'speech', 'projection', 'tty', 'tv'];

    public function init(): void
    {
        $browserClass = '';
        foreach ($this->getMeta('browserClasses', []) as $browser => $param) {
            $match = null;
            if (preg_match((string)$param['regExp'], (string)$this->request->get('HTTP_USER_AGENT', 'H', ''), $match)) {
                $browserClass = (string)$browser;
                if (!empty($match[1])) {
                    foreach ($param['olderVer'] as $addClass => $beforeVer) {
                        if ($match[1] < $beforeVer) {
                            $browserClass .= ' ' . (string)$addClass;
                        }
                    }
                }
                break;
            }
        }
        $this->_setViewVar('bodyClass', $browserClass);
        $this->_setViewVar('poweredBy', $this->getMeta('show_power', true) ? $this->containerService('application')->getCoreVersion() : null);
    }

    public function runAfterInit(): void
    {
        if (!$this->view['title']) {
            $main = $this->_getBlock('main', false);
            if ($main) {
                if (method_exists($main, 'getTitle')) {
                    $title = $main->getTitle();
                }
                if (empty($title)) {
                    $title = $main->getMeta('title');
                }
            }
            if (empty($title)) {
                $app = $this->containerService('application');
                /* @var $app \fan\core\service\application */
                $title  = $app->getConfig('PROJECT_NAME');
                $title .= (empty($title) ? '' : ' | ') . $app->getAppName();
                if ($main) {
                    $title .= ' | ' . get_class_name($main);
                }
            }
            $this->view['title'] = $title;
        }
    }

    public function setTitle(string $title, bool $checkIsSet = false): static
    {
        if (!$checkIsSet || !$this->view['title']) {
            $this->view['title'] = $title;
        }
        return $this;
    }

    public function getTitle(): mixed
    {
        return $this->view['title'];
    }

    public function setMetaTag(mixed $meta): void
    {
        if (is_object($meta) && method_exists($meta, 'toArray')) {
            $meta = $meta->toArray();
        }
        if (!is_array($meta)) {
            error_log('Incorrect value for meta-tag.', E_USER_NOTICE);
            return;
        }

        $metaData = $this->view->get('meta', []);
        foreach ($metaData as $v) {
            if ($this->_compareArray($v, $meta, ['name', 'property', 'content', 'http_equiv', 'scheme', 'id'])) {
                return;
            }
        }
        $metaData[] = $meta;
        $this->view->set('meta', $metaData);
    }

    public function getMetaTag(): array
    {
        return empty($this->view['meta']) ? [] : $this->view['meta'];
    }

    public function setLinkTag(string $rel, string $type, string $href, string $title = ''): static
    {
        $link = ['rel' => $rel, 'type' => $type, 'href' => $href];
        if ($title) {
            $link['title'] = $title;
        }
        $tagLink   = $this->view->get('tagLink', []);
        $tagLink[] = $link;
        $this->view->set('tagLink', $tagLink);
        return $this;
    }

    public function setExternalCss(mixed $cssFile, string $type = 'style'): static
    {
        if (is_object($cssFile) && method_exists($cssFile, 'toArray')) {
            $cssFile = $cssFile->toArray();
        } elseif (!is_array($cssFile)) {
            $cssFile = [$type => [$cssFile]];
        }

        $css =& $this->externalCSS;
        foreach ($cssFile as $k => $v1) {

            $tmp = explode('_', (string)$k, 2);
            if (empty($tmp[1])) {
                $tmp[1] = 'all';
            } elseif (!in_array($tmp[1], $this->cssMedia)) {
                throw new \InvalidArgumentException('Unknown media "' . $tmp[1] .'" for External CSS "' . $type .'".');
            }
            list($k0, $k1) = $tmp;
            if (!isset($css[$k0][$k1])) {
                $css[$k0][$k1] = [];
            }

            foreach ($v1 as $v2) {
                if (empty($v2)) {
                    continue;
                }
                $v2 = $this->tab->getURI((string)$v2, 'css', false);
                if (!in_array($v2, $css[$k0][$k1])) {
                    $css[$k0][$k1][] = $v2;
                }
            }
        }
        return $this;
    }

    public function setEmbedCss(mixed $css, string $media = 'all'): static|null
    {
        if (is_object($css)) {
            if (!method_exists($css, '__toString')) {
                error_log('Incorrect value for Embed Css.', E_USER_NOTICE);
                return null;
            }
            $css = $css->__toString();
        }
        if (!in_array($media, $this->cssMedia)) {
            error_log('Incorrect Media type of CSS: "' . $media . '".', E_USER_WARNING);
            return null;
        }
        $css = (string)$css;

        if (isset($this->embedCSS[$media])) {
            $this->embedCSS[$media] = '';
        }

        $embedCss =& $this->embedCSS[$media];
        if (!strstr($embedCss, $css)) {
            $embedCss .= empty($embedCss) ? $css : "\n" . $css;
        }
        return $this;
    }

    public function setEmbedCssByMeta(mixed $meta): static
    {
        if (is_array_alt($meta)) {
            foreach ($meta as $k => $v) {
                $this->setEmbedCss($v, (string)$k);
            }
        } elseif (is_string($meta)) {
            $this->setEmbedCss($meta);
        }

        return $this;
    }

    public function setExternalJs(mixed $jsFile, string $pos = 'head'): static
    {
        if (is_object($jsFile) && method_exists($jsFile, 'toArray')) {
            $jsFile = $jsFile->toArray();
        } elseif (!is_array($jsFile)) {
            $jsFile = [$pos => [$jsFile]];
        }

        foreach ($jsFile as $k => $v1) {
            if (!isset($this->externalJS[$k])) {
                throw new \InvalidArgumentException('Unknown position key "' . $k .'" for External JS.');
            }
            foreach ($v1 as $v2) {
                if (empty($v2)) {
                    continue;
                }
                $v2 = $this->tab->getURI((string)$v2, 'js', false);
                $this->_addJsFile($v2, (string)$k);
            }
        }
        return $this;
    }

    public function setEmbedJs(mixed $js, string $pos = 'head', int $ord = 0, bool $allowDebug = true): static
    {
        if (!array_key_exists($pos, $this->embedJS)) {
            throw new \InvalidArgumentException('Unknown position key "' . $pos .'" for Embeded JS.');
        }
        if (empty($this->embedJS[$pos])) {
            $this->embedJS[$pos] = ['', '', ''];
        }

        if (is_object($js) && method_exists($js, 'toArray')) {
            $js = $js->toArray();
        }

        if (is_array($js)) {
            $jsArgs = $js;
            $js = (string)array_shift($jsArgs) . '(';
            $json = $this->containerService('json');
            foreach ($jsArgs as $v) {
                $js .= $json->encode($v) . ', ';
            }
            $js = substr($js, 0, -2) . ');';
        } else {
            $js = (string)$js;
        }

        $embedJS =& $this->embedJS[$pos][$ord < 0 ? 0 : ($ord > 0 ? 2 : 1)];
        if (!empty($embedJS)) {
            $embedJS .= "\n";
        }
        if (!preg_match('/^\s*try\s*\{.+?\}\s*catch\s*\(.*?\)\s*\{.*?\}\s*$/is', $js)) {
            $js = 'try{' . $js . '}catch(e){' . ($this->tab->isDebugAllowed() && $allowDebug ? 'alert((e.fileName ? "Error in " + e.fileName : "") + (e.lineNumber ? " line " + e.lineNumber : "")+ (e.fileName || e.lineNumber ? "\n" : "") + (e.name ? e.name + ": " : "") + e.message);' : '') . '}';
        }
        $embedJS .= $js;
        return $this;
    }

    public function setHeadBefore(string $htmlCode): static
    {
        $codeBefore = $this->view->get('headBefore', '');
        $this->view->set('headBefore', $codeBefore . $htmlCode);
        return $this;
    }

    public function setHeadAfter(string $htmlCode): static
    {
        $codeAfter = $this->view->get('headAfter', '');
        $this->view->set('headAfter', $codeAfter . $htmlCode);
        return $this;
    }

    public function setModalWindow(string $filePath, array $tplVars = [], mixed $cssFile = '/css/modal_win.css', mixed $jsFile = null): static
    {
        if (!empty($filePath)) {
            if (!\is_file($filePath)) {
                $filePath = \bootstrap::parsePath($filePath);
            }
            if (\is_readable($filePath)) {
                $template = $this->containerService('template')->get($filePath, null, $this);
                /* @var $template \fan\core\service\template\type\base */

                foreach ($tplVars as $k => $v) {
                    $template->assign($k, $v);
                }

                $this->modalWin .= $template->fetch();

                if (!empty($cssFile)) {
                    $this->setExternalCss($cssFile);
                }
                if (!empty($jsFile)) {
                    $this->setExternalJs($jsFile);
                }
            } else {
                throw new \RuntimeException('Unknown path to modal template "' . $filePath . '"');
            }
        }
        return $this;
    }

    public function isExtCSS(string $key): bool
    {
        $css = $this->view->get('externalCSS', []);
        if (!isset($css[$key])) {
            return false;
        } elseif (!is_array($css[$key])) {
            throw new \UnexpectedValueException('Values of CSS for key "' . $key . '" must be as array.');
        }
        foreach ($css[$key] as $v) {
            if (!empty($v)) {
                return true;
            }
        }
        return false;
    }
    // ==================== protected methods ==================== \\

    /**
     * @param string $uri URI used as the routing or request target.
     */
    protected function _addJsFile(string $uri, string $type): static
    {
        $js =& $this->externalJS[$type];
        if (!in_array($uri, $js)) {
            $jsFileContent = is_readable(BASE_DIR . $uri) ? file_get_contents(BASE_DIR . $uri) : false;
            if (is_string($jsFileContent) && preg_match('/\/\*\*include\s*(.+?)\s*\*\//is', $jsFileContent, $matches)) {
                $scripts = explode("\n", $matches[1]);
                foreach ($scripts as $scr) {
                    list($scrFile) = explode(';', $scr, 2);
                    $this->_addJsFile($scrFile, $type);
                }
            }
            if (!in_array($uri, $js)) {
                $js[] = $uri;
            }
        }
        return $this;
    }

    protected function _preOutput(): void
    {
        if (!empty($this->modalWin)) {
            $this->view->set('modal_win', $this->modalWin);
        }

        $externalCSS = service('obfuscator', 'css')->getNewList($this->externalCSS);
        $this->view->set('externalCSS', $externalCSS);
        $this->view->set('embedCSS',    $this->embedCSS);

        $externalJS = service('obfuscator', 'js')->getNewList($this->externalJS);
        $this->view->set('externalJS',  $externalJS);
        $this->view->set('embedJS',     $this->embedJS);
    }
    protected function _compareArray(array $array1, array $array2, array $keys): bool
    {
        foreach ($keys as $k) {
            if (!isset($array1[$k]) && !isset($array2[$k])) {
                continue;
            }
            $value1 = is_scalar($array1[$k] ?? null) || ($array1[$k] ?? null) === null ? (string)($array1[$k] ?? '') : $array1[$k];
            $value2 = is_scalar($array2[$k] ?? null) || ($array2[$k] ?? null) === null ? (string)($array2[$k] ?? '') : $array2[$k];
            if (!isset($array1[$k]) || !isset($array2[$k]) || $value1 !== $value2) {
                return false;
            }
        }
        return true;
    }
}
