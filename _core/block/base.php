<?php

declare(strict_types=1);

namespace fan\core\block;
use fan\core\di\container_interface;
use fan\project\exception\block\fatal as fatalException;
/**
 * Base abstract all type of block
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
 * @version of file: 05.02.011 (03.10.2015)
 *
 * @abstract
 *
 * @property-read string $blockName
 * @property-read \fan\core\block\base $container
 * @property-read \fan\core\block\base[] $embeddedBlocks
 * @property-read \fan\core\base\meta\row $meta
 * @property-read string $namespace
 * @property-read \fan\core\service\request $request
 * @property-read \fan\core\service\tab $tab
 * @property \fan\core\view\router $view
 *
 * @method mixed getSessionData() getSessionData(array|string $key, mixed $defaultValue = null, boolean $removeFromSes = false)
 * @method mixed setSessionData() setSessionData(array|string $key, mixed $value)
 * @method mixed removeSessionData() removeSessionData(array|string $key)
 *
 * @method \fan\core\service\tab\subscriber _subscribeForEvent() subscribeForEvent(string $eventName, string $listenerMethod = 'eventHandler')
 * @method \fan\core\service\tab\subscriber _subscribeByName() subscribeByName(string $broadcasterName, string $eventName, string $listenerMethod = 'eventHandler')
 * @method \fan\core\service\tab\subscriber _subscribeByClass() subscribeByClass(string $className, string $eventName, string $listenerMethod = 'eventHandler')
 * @method \fan\core\service\tab\subscriber _unSubscribeForEvent() unSubscribeForEvent(string $eventName, string $listenerMethod = 'eventHandler')
 * @method \fan\core\service\tab\subscriber _unSubscribeByName() unSubscribeByName(string $broadcasterName, string $eventName, string $listenerMethod = 'eventHandler')
 * @method \fan\core\service\tab\subscriber _unSubscribeByClass() unSubscribeByClass(string $className, string $eventName, string $listenerMethod = 'eventHandler')
 * @method \fan\core\service\tab\subscriber _broadcastEvent() broadcastEvent(string $eventName, array $data = [])
 */
abstract class base
{
    use \fan\core\di\container_aware_trait;

    /**
     * Name of block
     * @var string
     */
    protected string $blockName = '';
    /**
     * Flag: Current block is "Root"
     * @var boolean
     */
    protected bool $isRoot = false;
    /**
     * Flag: Current block is "Main"
     * @var boolean
     */
    protected bool $isMain = false;

    /**
     * Service Tab
     * @var \fan\core\service\tab
     */
    protected ?object $tab = null;

    /**
     * Meta-Maker instance
     * @var \fan\core\base\meta\maker
     */
    private ?object $metaMaker = null;
    /**
     * MetaData
     * @var \fan\core\base\meta\row|null
     */
    protected ?object $meta = null;
    /**
     * Flag indicating that the dynamic meta-data are formed
     * @var boolean
     */
    protected bool $isDynMeta = false;

    /**
     * Role Conditions
     * @var array
     */
    protected ?array $roleCondition = null;

    /**
     * View data router
     * @var \fan\core\view\router
     */
    private ?object $view = null;
    /**
     * Path to template
     * @var string
     */
    private ?string $template = null;

    /**
     * Container block
     * @var \fan\core\block\base
     */
    private ?object $container = null;

    /**
     * Embedded Blocks
     * @var \fan\core\block\base[]
     */
    private array $embeddedBlocks = [];

    /**
     * DB-operation ('rollback', 'commit', 'nothing' OR null) when the Exception is occurred
     * @var string
     */
    protected ?string $exceptionDbOper = null;

    /**
     * Request-service
     * @var \fan\core\service\request
     */
    private ?object $request = null;

    protected array $delegateRule = [
        'ordinary' => [
            'getSessionData'    => ['this', 'getSession', 'get'],
            'setSessionData'    => ['this', 'getSession', 'set'],
            'removeSessionData' => ['this', 'getSession', 'remove'],
        ],
        'identified' => [
            '_subscribeForEvent'   => ['tab', 'getSubscriber', 'subscribeForEvent'],
            '_subscribeByName'     => ['tab', 'getSubscriber', 'subscribeByName'],
            '_subscribeByClass'    => ['tab', 'getSubscriber', 'subscribeByClass'],
            '_unSubscribeForEvent' => ['tab', 'getSubscriber', 'unSubscribeForEvent'],
            '_unSubscribeByName'   => ['tab', 'getSubscriber', 'unSubscribeByName'],
            '_unSubscribeByClass'  => ['tab', 'getSubscriber', 'unSubscribeByClass'],
            '_broadcastEvent'      => ['tab', 'getSubscriber', 'broadcastEvent'],
        ],
    ];

    public function __construct(
        string|int|null $blockName = null,
        ?\fan\core\service\tab $tab = null,
        ?base $container = null,
        array $containerMeta = [],
        bool $fullConstr = true,
        ?container_interface $serviceContainer = null
    )
    {
        $this->setServiceContainer($serviceContainer);
        $this->_transferor(); // Unconditional transfer to another block

        $this->blockName = (string)$blockName;
        $this->tab       = empty($tab) ? $this->containerService('tab') : $tab;
        $this->request   = $this->containerService('request');

        if (!empty($blockName)) {
            $this->tab->setCurrentBlock($this);
        }

        $this->metaMaker = $this->_createMetaMaker();

        if ($fullConstr) {
            $this->finishConstruct($container, $containerMeta);
        }
    }

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    public function finishConstruct(?base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        if (!empty($container)) {
            $this->container = $container;
        }
        if (!empty($containerMeta)) {
            $this->metaMaker->setContainerMeta($containerMeta);
        }
        $this->metaMaker->setMainBlockMeta();

        $this->meta = $this->metaMaker->assembleBlock();

        $this->_makeDynamicMeta(false);

        $this->view = $this->_createViewRouter();

        if (!empty($this->blockName)) {
            $this->_doRoleOperations();
            if ($this->getRoleCondition()) {
                return;
            }

            // ToDo: Maybe this should be set Tab-block even if the role dosn't permit it
            $this->tab->setTabBlock($this, $this->blockName);
            list($this->isRoot, $this->isMain) = $this->tab->checkBlockStatus($this);
        }

        $this->_setTemplate();
        $this->_preparseMeta();

        if ($allowSetEmbedded) {
            $this->_setEmbeddedBlocks();
        }

        $this->_postCreate();
    }

    public function init(): void
    {
    }
    public function initRequired(): void
    {
    }
    public function runAfterInit(): void
    {
    }


    public function getBlockName(): string
    {
        return $this->blockName;
    }

    public function getTab(): ?object
    {
        return $this->tab;
    }

    public function getContainer(): ?object
    {
        return $this->container;
    }

    public function getMetaMaker(): object
    {
        return $this->metaMaker;
    }

    /**
     * @param mixed $default Fallback value returned when no explicit value is available.
     */
    public function getMeta(string|array|null $key = null, mixed $default = null, bool $convToArray = false): mixed
    {
        $meta = $this->metaMaker->getMeta($key, $default);
        return $convToArray && is_object($meta) && $meta instanceof \fan\core\base\meta\row ? $meta->toArray() : $meta;
    }

    public function getMetaVar(string|array|null $key = null): mixed
    {
        trigger_error('Method "getMetaVar" is deprecated. Use "getMeta" instead.', E_USER_DEPRECATED);
        return $this->getMeta($key);
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function setMeta(string|array $key, mixed $value): static
    {
        $this->metaMaker->setMeta($key, $value);
        return $this;
    }

    /**
     * @param array|\fan\core\base\meta\row $value Value that should be applied or transformed.
     */
    protected function addMeta(array|\fan\core\base\meta\row $value): static
    {
        $maker = $this->metaMaker;
        foreach ($value as $k => $v) {
            $maker->setMeta($k, $v);
        }
        return $this;
    }

    /**
     * @param mixed $value Value that should be applied or transformed.
     */
    protected function setMetaVar(mixed $key, mixed $value): static
    {
        trigger_error('Method "setMetaVar" is deprecated. Use "setMeta" instead.', E_USER_DEPRECATED);
        return $this->setMeta($key, $value);
    }

    public function makeDelayedMeta(\fan\core\base\meta\row $meta): void
    {
        foreach ($meta as $k => $v) {
            if (is_object($v)) {
                if ($v instanceof \fan\core\base\meta\delayed) {
                    $meta[$k] = $v->getValue();
                } elseif ($v instanceof \fan\core\base\meta\row) {
                    $this->makeDelayedMeta($v);
                }
            }
        }
    }

    public function getDynamicMeta(mixed $meta): array
    {
        return [];
    }

    public function setDynamicMeta(): static
    {
        //ToDo: Replace \fan\core\base\meta\delayed to special Flag in \fan\core\base\meta\row
        if (class_exists('\fan\core\base\meta\delayed', false)) {
            $this->makeDelayedMeta($this->meta);
        }
        $this->_makeDynamicMeta(true);
        return $this;
    }

    public function getRoleCondition(): ?array
    {
        if (!is_null($this->roleCondition)) {
            return empty($this->roleCondition) ? null : $this->roleCondition;
        }
        $this->roleCondition = [];

        $roles = $this->getMeta('roles', [], true);
        if (!$roles) {
            return null;
        }

        foreach ($roles as $v) {
            if (isset($v['condition']) && !role($v['condition'])) {
                $this->roleCondition = $v;
                return $v;
            }
        }
        return null;
    }

    public function getViewParserName(): string
    {
        $definer = $this->tab->getViewDefiner();
        return $definer->getViewParserName();
    }

    public function getViewFormat(): string
    {
        return call_user_func([$this->tab->getViewClass(), 'getFormat']);
    }

    public function getView(): ?object
    {
        return $this->view;
    }

    public function getViewData(): array
    {
        $this->_preOutput();
        return $this->view->getAll();
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @throws \fan\core\exception\block\fatal
     */
    public function setTemplate(string $templatePath, bool $allowException = true): bool
    {
        if (is_file($templatePath)) {
            $this->template = $templatePath;
            return true;
        } elseif ($allowException) {
            throw new fatalException($this, 'Incorrect template path "' . $templatePath . '"');
        }
        return false;
    }

    public function getRequest(): ?object
    {
        return $this->request;
    }

    public function getNamespace(): ?string
    {
        $name = get_class($this);
        $pos  = strrpos((string)$name, '\\');
        return $pos === false ? null : substr((string)$name, 0, $pos + 1);
    }

    public function getEmbeddedBlocks(): array
    {
        return $this->embeddedBlocks;
    }

    public function getEmbeddedBlock(string|int $key): object
    {
        if (!isset($this->embeddedBlocks[$key])) {
            throw new \fan\project\exception\block\local($this, 'Call to unknown Embedded Block "' . $key . '"', E_USER_WARNING);
        }
        return $this->embeddedBlocks[$key];
    }

    public function getExceptionDbOper(): ?string
    {
        return $this->exceptionDbOper;
    }

    public function getSession(): object
    {
        return $this->containerService('session', get_class($this), 'block');
    }

    public function checkRunInit(): bool
    {
        return true;
    }

    public function getDebugInfo(): array
    {
        $metaSourse = $this->metaMaker->getSource();
        $parentPaths  = service('reflector')->getParentPaths($this);
        $currentPath = substr((string)reset($parentPaths), 0, -3);

        return [
            'blockName'      => $this->blockName,
            'className'      => get_class($this),
            'templateFile'   => $this->template,
            'metaFile'       => file_exists($currentPath . 'meta.php') ? $currentPath . 'meta.php' : null,
            'meta'           => $this->meta,
            'embeddedBlocks' => $this->embeddedBlocks,
            'parentPaths'    => $parentPaths,
            'metaSourse'     => $metaSourse,

            'folderMeta'     => $metaSourse['folder'],
            'fileMeta'       => $metaSourse['block'],
            'parentMeta'     => $metaSourse['parent'],
            'containerMeta'  => $metaSourse['container'],

        ];
    }

    // ======== Private/Protected methods ======== \\
    protected function _createMetaMaker(): object
    {
        return new \fan\project\base\meta\maker($this);
    }

    protected function _createViewRouter(): object
    {
        return call_user_func([$this->tab->getViewClass(), 'getRouter'], $this);
    }

    protected function _setViewVar(string $key, mixed $val): static
    {
        $this->view->set($key, $val);
        return $this;
    }

    protected function _preparseMeta(): static
    {
        $viewFormat = $this->getViewFormat();
        if ($viewFormat === 'html') {
            $root = $this->_getBlock('root', false);
            if ($root) {
                $this->_setRootBlockParameters($root);
            }
        }
        if (in_array($viewFormat, ['html', 'loader'])) {
            $tplVars = $this->getMeta('tplVars');
            if ($tplVars) {
                $this->_setTplVarsByMeta($tplVars);
            }
        }
        return $this;
    }

    protected function _makeDynamicMeta(bool $force): void
    {
        if (!$this->isDynMeta && ($force || $this->getMeta('force_dynamic_meta', false))) {
            $this->isDynMeta = true;
            $dynMeta = $this->getDynamicMeta($this->meta);
            if (!empty($dynMeta)) {
                $this->meta->mergeData($dynMeta);
            }
        }
    }

    protected function _doRoleOperations(): void
    {
    }

    protected function _transferor(): void
    {
    }

    protected function _postCreate(): void
    {
    }

    protected function _preOutput(): void
    {
    }

    protected function _setRootBlockParameters(?\fan\core\block\base $root = null, array $rootKeys = []): static
    {
        if (empty($root)) {
            $root  = $this->_getBlock('root');
        }
        if (!$rootKeys) {
            $rootKeys = [
                'externalCss' => 'setExternalCss',
                'embedCss'    => 'setEmbedCssByMeta',
            'externalJS'  => 'setExternalJs',
        ];
    }

        if (isset($this->meta['meta_tag'])) {
            foreach ($this->meta['meta_tag'] as $v) {
                $root->setMetaTag($v);
            }
        }
        foreach ($rootKeys as $k => $m) {
            if (isset($this->meta[$k])) {
                $value = $this->meta[$k];
                $root->$m(is_object($value) && method_exists($value, 'toArray') ? $value->toArray() : $value);
            }
        }
        if (isset($this->meta['embedJS'])) {
            foreach ($this->meta['embedJS'] as $pos => $v1) {
                if (is_array($v1) || is_object($v1) && $v1 instanceof \fan\core\base\meta\row) {
                    foreach ($v1 as $v2) {
                        $root->setEmbedJs($v2, $pos);
                    }
                } elseif (is_string($v1)) {
                    $root->setEmbedJs($v1, $pos);
                }
            }
        }
        return $this;
    }

    protected function _setTplVarsByMeta(array|\fan\core\base\meta\row $tplVars): static
    {
        foreach ($tplVars as $k => $v) {
            $this->view->set($k, is_object($v) && method_exists($v, 'toArray') ? $v->toArray() : $v);
        }
        return $this;
    }

    /**
     * @throws \fan\core\exception\block\fatal
     */
    protected function _setTemplate(string $templateName = ''): static
    {
        $paths    = service('reflector')->getParentPaths($this);
        $suffixes = $this->_getTplSuffixes();

        // If template-name isn't defined - try to get it from the Meta
        if (!$templateName) {
            $templateName = $this->getMeta('template');
        }
        // If template-name is defined - check and set it
        if ($templateName) {
            // If Template Name is set as full path
            if ($this->_checkTemplate('', \bootstrap::parsePath($templateName), $suffixes)) {
                return $this;
            }

            // If Template Name is set as base name (concat with block path)
            reset($paths);
            if ($this->_checkTemplate(current($paths), $templateName, $suffixes)) {
                return $this;
            }

            // Throw exception if defined template-name incorrect
            throw new fatalException($this, 'Incorrect template name "' . $templateName . '"');
        }

        // Try to find template by block-name
        foreach ($paths as $class => $path) {
            if ($this->_checkTemplate($path, get_class_name($class), $suffixes)) {
                return $this;
            }
        }
        return $this;
    }

    protected function _getTplSuffixes(string $separator = '_'): array
    {
        $suffixes = [''];
        if ($this->getMeta('useMultiLanguage')) {
            $lng = $this->containerService('locale')->getLanguage();
            if (!empty($lng)) {
                array_unshift($suffixes, $separator . $lng);
            }
        }
        return $suffixes;
    }

    protected function _checkTemplate(string $blockPath, string $templateName, array $suffixes, string $extension = 'tpl'): bool
    {
        $blockPath = empty($blockPath) ? '' : dirname($blockPath) . '/';
        $extension = '.' . $extension;

        $extLen = -strlen($extension);
        if (substr($templateName, $extLen) === $extension) {
            $templateName = substr($templateName, 0, $extLen);
        }

        foreach ($suffixes as $v) {
            $template = $blockPath . $templateName . $v . $extension;
            if ($this->setTemplate($template, false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws \fan\core\exception\block\fatal
     */
    protected function _setEmbeddedBlocks(): static
    {
        $embeddedBlocks = $this->getMeta('embeddedBlocks');
        if (!empty($embeddedBlocks)) {
            $srcMeta = $this->metaMaker->getMixSrcMeta();
            foreach ($embeddedBlocks as $k => $v) {
                if (!is_null($v)) {
                    $containerMeta = [
                        'common' => $srcMeta['common'],
                        'own'    => isset($srcMeta[$k]) && $this->blockName !== 'main' ? $srcMeta[$k] : [],
                    ];
                    if ((string)$v === '{MAIN}') {
                        $main = $this->tab->getMainBlock();
                        if (empty($main)) {
                            throw new fatalException($this, 'Main Block isn\'t set.');
                        } else {
                            $this->embeddedBlocks[$k] = $main;
                            $main->finishConstruct($this, $containerMeta);
                        }
                    } else{
                        $class = $this->_parseClassName($v);
                        if ($class) {
                            $this->embeddedBlocks[$k] = new $class($k, $this->tab, $this, $containerMeta, true);
                            $this->tab->setCurrentBlock($this);
                        }
                    }
                }
            }
        }
        return $this;
    }

    protected function _getBlock(string $blockName, bool $allowException = true): ?object
    {
        return $this->tab->getTabBlock($blockName, $allowException);
    }

    /**
     * @throws \fan\core\exception\block\local
     */
    protected function _makeBlockException(string $logErrMsg, string $type = 'local', ?string $exceptionDbOper = null, int $code = E_USER_NOTICE, ?\Exception $previous = null): never
    {
        $class = '\fan\project\exception\block\\' . $type;
        if (!class_exists($class)) {
            $class = '\fan\project\exception\block\fatal';
        }
        $this->exceptionDbOper = empty($exceptionDbOper) ? ($class === '\fan\project\exception\block\local' ? 'nothing' : 'rollback' ) : $exceptionDbOper;
        throw new $class($this, $logErrMsg, $code, $previous);
    }

    /**
     * @throws \fan\core\exception\block\fatal
     */
    protected function _parseClassName(string $blockPath, bool $allowException = true): ?string
    {
        $class = $this->tab->loadBlock($blockPath);
        if (empty($class) && $allowException) {
            throw new fatalException($this, 'Unknown block path "' . $blockPath . '" for Embedded Block');
        }
        return $class;
    }

    protected function _setCacheRole(mixed $role): static
    {
        if (is_array($role)) {
            foreach ($role as $v) {
                $this->_setCacheRole($v);
            }
            return $this;
        }
/*
//ToDo: Redesign it
        $cacheRole = $this->getMeta(['cache','considerRole'], [], true);

        if(!is_array($cacheRole)) {
            $cacheRole = [$role];
        } elseif (!in_array($role, $cacheRole)) {
            $cacheRole[] = $role;
        }
 */
        return $this;
    }

    protected function _callOrdinaryDelegate(object $object, string $method, array $args): mixed
    {
        return call_user_func_array([$object, $method], empty($args) ? [] : $args);
    }

    protected function _callIdentifiedDelegate(object $object, string $method, array $args): mixed
    {
        if (empty($args)) {
            $args = [];
        }
        array_unshift($args, $this);
        return call_user_func_array([$object, $method], $args);
    }

    // ======== The magic methods ======== \\

    /**
     * Handles dynamic property reads for this current component.
     */
    public function __get(string $key): mixed
    {
        $method = 'get' . ucfirst($key);
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        throw new \OutOfBoundsException('Get Undefined property "' . $key . '" in block "' . $this->blockName . '", class "' . get_class($this) . '".');
    }

    public function __call(string $method, array $args): mixed
    {
        foreach ($this->delegateRule as $type => $methods) {
            foreach ($methods as $name => $param) {
                if ($method === $name) {
                    if (substr($name, 0, 1) === '_') {
                        // ToDo: Check caller there - must be === $this, else "break";
                    }

                    $callMethod = array_pop($param);
                    $objectName = array_shift($param);
                    $object = $objectName === 'this' ? $this : ($objectName === 'tab' ? $this->getTab() : $this->containerService($objectName));
                    foreach ($param as $v) {
                        $object = call_user_func([$object, $v]);
                    }

                    $delegateMethod = '_call' . ucfirst($type) . 'Delegate';
                    return $this->$delegateMethod($object, $callMethod, $args);
                }
            }
        }
        $trace = debug_backtrace();
        throw new \BadMethodCallException(
                'Incorrect call unknown method "<b>' . $method . '</b>" ' .
                'in block "<b>'    . $this->blockName  . '</b>", ' .
                'class "<nobr><b>' . get_class($this)   . '</b></nobr>",<br />' .
                (isset($trace[1]['file']) ? 'file "<nobr><b>'  . $trace[1]['file'] . '</b></nobr>", ' : 'No file') .
                (isset($trace[1]['line']) ? 'line <b>'         . $trace[1]['line'] . '</b>.' : '')
        );
    }
    // ======== Required Interface methods ======== \\
}
