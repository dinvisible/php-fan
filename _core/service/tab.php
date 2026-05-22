<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * Class of tab handler
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
 * @version of file: 05.02.006 (20.04.2015)
 *
 * @method boolean isUseHttps() isUseHttps(array|string $key)
 * @method string getCurrentURI() getCurrentURI(boolean $corLng, boolean $addExt, boolean $addQueryStr, boolean $addFirstSlash)
 * @method string getURI() getURI(string $urn, string $type, boolean $useSid, boolean $protocol)
 * @method string addQuery() addQuery(string $urn, string $key, string $val)
 * @method string getDefaultExtension() getDefaultExtension()
 */
class tab extends \fan\core\base\service\single
{
    /**
     *  Marker of URN application prefix
     */
    public const URN_AP = '~';

    protected static array $errTransfer = [];

    protected array $engine = [];

    protected array $delegateRule = [
        'urlMaker' => ['isUseHttps', 'getCurrentURI', 'getModifiedCurrentURI', 'getURI', 'addQuery', 'getDefaultExtension'],
    ];

    /**
     * @var \fan\core\service\matcher
     */
    protected ?object $matcher = null;

    /**
     * View Type definder
     * @var \fan\core\view\definer
     */
    protected ?object $viewDefiner = null;
    /**
     * Value of View Type
     * @var string
     */
    protected ?string $viewClass = null;

    protected ?string $appName = null;

    /**
     * Current parsed data
     * @var \fan\core\service\matcher\item\parsed
     */
    protected ?object $currentData = null;

    /**
     * Last parsed data
     * @var \fan\core\service\matcher\item\parsed
     */
    protected ?object $lastData = null;

    /**
     * @var \fan\core\block\base Main Tab block
     */
    protected ?object $mainBlock = null;

    /**
     * @var \fan\core\block\base Root Tab block
     */
    protected ?object $rootBlock = null;

    /**
     * @var \fan\core\block\base Current (performent at this time) Tab block
     */
    protected ?object $currentBlock = null;

    /**
     * Meta-data for this Tab
     * @var array
     */
    protected array $tabMeta = [];

    /**
     * Blocks Meta-data from the Main-block
     * @var array
     */
    protected array $blocksMeta = [];

    /**
     * Default Meta-data by Tab-configuration according to View-Type
     * @var array
     */
    protected array $defaultMeta = [];

    /**
     * One-dimensional hash-array for call other blocks
     * @var array
     */
    protected array $blocks = [];

    /**
     * Two-dimensional array for init data blocks
     * @var array
     */
    protected array $initOrder = [];

    /**
     * It is made content for show
     * @var string|array
     */
    protected mixed $content = '';

    /**
     * Stage of Tab processing
     * @var string
     */
    protected ?string $stage = null;

    protected bool $cacheEnable = true;
    /**
     * @var number Cache File Time
     */
    protected int|float|null $cacheFileTime = null;
    /**
     * @var number Cache Expire Time
     */
    protected int|float|null $cacheExpireTime = null;

    protected ?array $timesStamp = null;
    protected ?bool $checkPerformance = null;
    protected ?bool $allowDebug = null;


    protected function __construct(bool $allowIni = true)
    {
        parent::__construct($allowIni);
        $this->matcher = \fan\project\service\matcher::instance();
    }

    // ======== Static methods ======== \\

    public static function getContent(): mixed
    {
        return self::staticContainerService('tab')->_controlTabTransfer()->content;
    }

    // ======== Main Interface methods ======== \\

    public function getMainBlock(): ?object
    {
        return $this->mainBlock;
    }

    public function getAppName(): ?string
    {
        return $this->appName;
    }

    public function getRootBlock(): ?object
    {
        return $this->rootBlock;
    }

    public function getViewDefiner(): object
    {
        if (empty($this->viewDefiner)) {
            $this->viewDefiner = new \fan\project\view\definer($this->config->get('VIEW_DEFINER', [])->toArray());
        }
        return $this->viewDefiner;
    }
    public function getViewClass(): ?string
    {
        return $this->viewClass;
    }

    public function checkBlockStatus(\fan\core\block\base $block): array
    {
        return [$block === $this->rootBlock, $block === $this->mainBlock];
    }

    public function getTabMeta(mixed $key = null, mixed $defautValue = null): mixed
    {
        if (is_null($key)) {
            return $this->tabMeta;
        }
        $ret = array_get_element($this->tabMeta, $key);
        return is_null($ret) ? $defautValue : $ret;
    }

    public function getBlocksMetaByMain(string $name): array
    {
        return isset($this->blocksMeta[$name]) && is_array($this->blocksMeta[$name]) ? $this->blocksMeta[$name] : [];
    }

    public function getDefaultMeta(): array
    {
        return $this->defaultMeta;
    }

    public function checkTabRoles(?string $dbOper = null, bool $allowTransfer = true): bool
    {
        $cond = $this->getTabMeta('roles');
        if ($cond) {
            do {
                foreach ($cond as $v) {
                    if (!role($v['condition'])) {
                        $transfer = $v;
                        break 2;
                    }
                }
                return true;
            } while (false);

            if ($allowTransfer) {
                $servSes = $this->containerService('session');
                $expire_URL = $servSes->isExpired() && !$this->getTabMeta('notRedirectByExpire', false) ? $this->config['EXPIRE_URL'] : null;

                if ($expire_URL) {
                    transfer_out($expire_URL, null, $dbOper);
                } elseif (!empty($transfer['transfer_sham'])) {
                    transfer_sham($this->getURI($transfer['transfer_sham']), null, $dbOper);
                } elseif (!empty($transfer['transfer_int'])) {
                    transfer_int($this->getURI($transfer['transfer_int']), null, $dbOper);
                } elseif (!empty($transfer['transfer_out'])) {
                    transfer_out($this->getURI($transfer['transfer_out']), null, $dbOper);
                } else {
                    $this->_parseError403();
                }
            } else {
                return false;
            }
        }
        return true;
    }

    public function loadBlock(string $path): ?string
    {
        $path = (string)$path;
        if ($path[0] !== '{') {
            $path = '{CAPP}/' . $path;
        }
        if (substr($path, -4) !== '.php') {
            $path .= '.php';
        }
        $loader = \bootstrap::getLoader();
        return $loader->loadBlockByPath($path);
    }

    public function setCurrentBlock(\fan\core\block\base $block): static
    {
        $this->currentBlock = $block;
        return $this;
    }

    public function getCurrentBlock(): ?object
    {
        return $this->currentBlock;
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    public function setTabBlock(\fan\core\block\base $block, string $name): static
    {
        if (isset($this->blocks[$name])) {
            throw new fatalException($this, 'Set dublicate of block with name "' . $name . '"');
        }
        $this->blocks[$name] = $block;
        $order = $block->getMeta('initOrder', $this->getDefaultInitNum());
        if (!isset($this->initOrder[$order])) {
            $this->initOrder[$order] = [];
        }
        $this->initOrder[$order][] = $block;
        return $this;
    }

    public function getTabBlock(string $blockName, bool $allowException = true): ?object
    {
        if (!isset($this->blocks[$blockName])) {
            if ($allowException) {
                throw new fatalException($this, 'Call undefined block with name "' . $blockName . '"');
            }
            return null;
        }
        return $this->blocks[$blockName];
    }

    public function isSetBlock(string $blockName): bool
    {
        return isset($this->blocks[$blockName]);
    }

    public function getTabStage(): ?string
    {
        return $this->stage;
    }

    public function getDefaultInitNum(): mixed
    {
        return $this->getConfig('INIT_ORDER_NUM', 1000);
    }

    public function isDebugAllowed(): bool
    {
        if (is_null($this->allowDebug)) {
            $debugConfig      = $this->containerService('config')->get('debug');
            $this->allowDebug = $debugConfig['ENABLED'] && $debugConfig['DEBUG_IP'] && !empty($_SERVER['SERVER_ADDR']) && preg_match($debugConfig['DEBUG_IP'], $_SERVER['SERVER_ADDR']);
        }
        return $this->allowDebug;
    }

    public function getSubscriber(): object
    {
        if (empty($this->engine['subscriber'])) {
            $this->engine['subscriber'] = $this->_getEngine('subscriber');
        }
        return $this->engine['subscriber'];
    }

    // ----------- Methods of block cache ------------- \\
    public function setFileTime(int|float $fileTime, int|float $expireTime): static
    {
        $this->cacheFileTime = $this->cacheFileTime ? $fileTime : max($this->cacheFileTime, $fileTime);
        if ($expireTime) {
           $this->cacheExpireTime = $this->cacheExpireTime ? $expireTime : min($this->cacheExpireTime, $expireTime);
        }
        return $this;
    }

    public function isCacheEnabled(): bool
    {
        return $this->cacheEnable;
    }
    public function disableCache(): static
    {
        $this->cacheEnable = false;
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @throws fatalException
     */
    protected function _controlTabTransfer(): static
    {
        $maxQttTransfer = $this->getConfig('MAX_QTT_TRANSFER', 10);
        do {
            try {
                // Preparing Tab-property. Parse error of request and set Main block
                $this->_checkAlias()
                     ->_resetProperty()
                     ->_parseError();

                if (empty($this->content)) {
                    // Set Tab meta. Check Tab Roles
                    $this->_setTabMeta()
                         ->checkTabRoles();

                    // Set View Class. Set Meta data for Other block by Main block. Set Default Meta by View-type. Make Tab-content.
                    $this->_setViewClass($this->mainBlock->getViewParserName())
                         ->_setBlocksMetaByMain()
                         ->_setDefaultMeta()
                         ->_makeContent();
                }

                return $this;
            } catch (\fan\core\base\transfer $e) {
                // Catch and make transfer
                $transferType = $e->getTransferType();

                $modify = $transferType === 'out' ? null : false;
                $uri = $this->getURI($e->getRequest(), 'link', $modify, $modify);

                // Out transfer
                if ($transferType === 'out') {
                    $this->containerService('header')->sendLocation($uri);
                }

                $this->matcher->setUri($uri, $e->getHost(), $e->isShiftCurrent());
            }
        } while ($this->matcher->getLastIndex() < $maxQttTransfer);

        // Exception if quantity of Transfer is more Max
        $trList = '';
        foreach ($this->matcher->getStack() as $v) {
            $trList .= "\n" . $v['source'];
        }
        throw new fatalException($this, 'To many transfers: ' . $trList);
    }

    protected function _checkAlias(): static
    {
        if ($this->matcher->getCurrentIndex() === 0) {
            $reqData   = $this->matcher->getLastItem()->getParsedSrc();
            $aliasFile = (string)\bootstrap::parsePath((string)$this->getConfig('ALIAS_FILE_PATH', '{PROJECT}/data/url_alias.php'));
            if (!empty($reqData) && is_readable($aliasFile)) {
                $aliasData = \fan\project\adapter\php_array_file::load($aliasFile, []);
                if (!empty($aliasData)) {
                    $reqPath = '/' . implode('/', $reqData);
                    array_unshift($reqData, $reqPath);
                    $prev = '';
                    foreach ($reqData as $k => $v) {
                        // Define $check
                        if (empty($k)) {
                            $check = $v;
                        } else {
                            $check = $prev . '/' . $v . '/*';
                            $prev .= '/' . $v;
                        }

                        if (isset($aliasData[$check])) {
                            // Define $destPath
                            [$type, $destPath] = array_pad(explode(':', $aliasData[$check], 2), 2, null);
                            if (!empty($type) && empty($destPath)) {
                                $destPath = $type;
                                $type = 'sham';
                            }
                            if ($k) {
                                if (substr($destPath, -1) !== '/') {
                                    $destPath .= '/';
                                }
                                $destPath .= substr($reqPath, strlen($check) - 1);
                            }

                            // Validate data
                            if (empty($type) || !in_array($type, ['out', 'int', 'sham'])) {
                                throw new fatalException($this, 'Alias transfer type has incorrect value "' . $type . '" for request "' . $reqPath . '".');
                            }
                            if (empty($destPath)) {
                                throw new fatalException($this, 'Alias transfer doesn\'t have path for "' . $reqPath . '".');
                            }
                            if (!is_string($destPath)) {
                                throw new fatalException($this, 'Alias transfer has icorrect path for "' . $reqPath . '".');
                            }

                            if (substr($destPath, -1) !== '/') {
                                $destPath = $this->_getPathWithExt($destPath);
                            }
                            call_user_func('transfer_' . $type, $destPath);
                        }
                    }
                }
            }
        }
        return $this;
    }

    protected function _resetProperty(): static
    {
        $this->stage       = 'preparing';
        $this->mainBlock   = null;
        $this->rootBlock   = null;
        $this->tabMeta     = [];
        $this->blocksMeta  = [];
        $this->blocks      = [];
        $this->initOrder   = [];
        $this->viewClass   = null;
        $this->content     = '';
        $this->appName     = $this->containerService('application')->getAppName();
        $this->currentData = $this->matcher->getCurrentParsedData();
        $this->lastData    = $this->matcher->getLastParsedData();
        $this->timesStamp  = [];
        $this->checkPerformance = $this->readBooleanFlag($this->getConfig('CHECK_PERFORMANCE', 0), 'CHECK_PERFORMANCE');
        return $this;
    }

    public function _parseError(): static
    {
        $mainRequest = $this->lastData['main_request'];
        if (empty($mainRequest) && (empty(self::$errTransfer) || (int)end(self::$errTransfer) === 404)) {
            $transferor = $this->getConfig('transferor');
            if (!empty($transferor)) {
                if (!is_array_alt($transferor)) {
                    throw new fatalException($this, 'Point transferor isn\'t array.');
                }
                foreach ($transferor as $class) {
                    call_user_func([$class, 'checkRequest'], $this->lastData['src_path'], $this->lastData['app_prefix']);
                }
            }
            $this->content = $this->_parseError404(false);
            return $this;
        }

        if (!empty($mainRequest)) {
            $file = $this->lastData['file'];
            if (!empty($file)) {
                $loader = \bootstrap::getLoader();
                if (!$loader->loadBlockByPath($file)) {
                    $this->content = $this->_parseError404(true);
                    return $this;
                }
            }
        }
        // Define Main Block.
        $this->_setMainBlock();
        return $this;
    }

    public function _makeContent(): static
    {
        // Start creating blocks
        $this->stage = 'creating';
        $startTime   = microtime(true);
        if ($this->_createRootBlock()) {
            $this->timesStamp['creating'] = microtime(true) - $startTime;

            // Init data blocks
            $this->stage = 'init';
            $this->_initBlocks($this->_getInitBlocks());

            // Additional init for base blocks
            $this->stage = 'after_init';
            $this->_runAfterInit();

            $initTime = microtime(true);

            // Get output Content
            $this->stage   = 'output';
            $this->content = $this->_getFinalContent($this->rootBlock);
            $this->_fixPerformance($startTime, $initTime);
        } else {
            $this->content = $this->_parseError500('Root block isn\'t defined.');
        }

        return $this;
    }

    protected function _setViewClass(string $viewClass): static
    {
        if (empty($viewClass)) {
            throw new fatalException($this, 'Type of view can\'t be empty.');
        }
        $class = '\fan\project\view\parser\\' . $viewClass;
        if (!class_exists($class, true)) {
            throw new fatalException($this, 'Class "' . $class . '" isn\'t found. Please check your "View definer"');
        }
        $this->viewClass = $class;
        return $this;
    }

    protected function _createRootBlock(): bool
    {
        $rootMeta  = $this->_getRootMeta();
        $rootBlock = $this->_defineRootBlock($rootMeta);
        if (empty($rootBlock)) {
            return false;
        }
        $this->rootBlock = $rootBlock;
        return true;
    } // _createRootBlock

    protected function _initBlocks(array $initOrderBlock): static
    {
        $prevTime = microtime(true);
        foreach ($initOrderBlock as $blocks) {
            foreach ($blocks as $block) {
                $this->setCurrentBlock($block);
                $block->setDynamicMeta();
                if (!$block->getRoleCondition()) {
                    if ($block->checkRunInit()) {
                        $block->init();
                    }
                    if (method_exists($block, 'initRequired')) {
                        $block->initRequired();
                    }
                }
                if ($this->checkPerformance) {
                    $blName = $block->getBlockName();
                    $curTime = microtime(true);
                    $this->timesStamp['init'][$blName] = $curTime - $prevTime;
                    $prevTime = $curTime;
                }
            }
        }
        return $this;
    } // _initBlocks
    protected function _runAfterInit(): static
    {
        $startTime = microtime(true);
        foreach (['main', 'carcass', 'root'] as $blockName) {
            $block = $this->getTabBlock($blockName, false);
            if ($block) {
                $block->runAfterInit();
            }
        }
        $this->timesStamp['after_init'] = microtime(true) - $startTime;
        return $this;
    } // _runAfterInit

    protected function _getFinalContent(\fan\core\block\base $rootBlock): mixed
    {
        $debugMode = $this->_getDebugMode();
        if ($debugMode > 0) {
            $class = '\fan\project\view\parser\\' . ($debugMode === 1 && $this->mainBlock->getViewFormat() === 'html' ? 'debug1' : 'debug2');
        } else {
            $class = (string)$this->getViewClass();
            if ($this->isDebugAllowed()) {
                $debug = \fan\project\service\debug::instance();
                /* @var $debug \fan\core\service\debug */
                $debug->setExtFiles($rootBlock, false);
            }
        }
        /* @var $viewParser \fan\core\view\parser */
        $viewParser = new $class($this->mainBlock);
        $viewParser->startParsing($rootBlock);
        return $viewParser->getFinalContent();
    }

    protected function _defineRootBlock(array $rootMeta): ?object
    {
        $defaultMeta = $this->getDefaultMeta();
        $rootPath = $this->getTabMeta(
                'root',
                array_val($defaultMeta, ['main', 'root'])
        );
        $carcassPath = $this->getTabMeta(
                'carcass',
                array_val($defaultMeta, ['main', 'carcass'])
        );

        $blockName = 'root';
        if (empty($rootPath)) {
            $rootPath    = $carcassPath;
            $carcassPath = null;
            $blockName   = 'carcass';
        }

        if (empty($rootPath)) {
            $this->mainBlock->finishConstruct(null, $rootMeta, true);
            return $this->mainBlock;
        }

        if (empty($carcassPath)) {
            $rootMeta['own']['embeddedBlocks']['main'] = '{MAIN}';
        } elseif (!isset($rootMeta['own']['embeddedBlocks']['carcass'])) {
            $rootMeta['own']['embeddedBlocks']['carcass'] = $carcassPath;
        }

        $rootClass = $this->loadBlock($rootPath);
        if (empty($rootClass)) {
            return null;
        }

        $rootBlock = new $rootClass($blockName, $this, null, $rootMeta, true);
        return $rootBlock;
    }

    protected function _getRootMeta(): array
    {
        $defaultMeta = $this->getDefaultMeta();
        if (!empty($defaultMeta) && is_object($defaultMeta)) {
            $defaultMeta = method_exists($defaultMeta, 'toArray') ? $defaultMeta->toArray() : [];
        }

        $locale = $this->containerService('locale');
        if ($locale->isEnabled() && empty($defaultMeta['common']['tplVars']['sLng'])) {
            $defaultMeta['common']['tplVars']['sLng'] = $locale->getLanguage();
        }

        return [
            'own'    => array_val($defaultMeta, 'root',   []),
            'common' => array_val($defaultMeta, 'common', []),
        ];
    }

    protected function _getInitBlocks(): array
    {
        ksort($this->initOrder);
        return $this->initOrder;
    }

    protected function _parseError403(): mixed
    {
        $this->containerService('header')->error403(false);
        if (!in_array(403, self::$errTransfer)) {
            array_push(self::$errTransfer, 403);
            $urn = $this->getConfig('error_403', self::URN_AP . '/error403');
            transfer_sham($this->_getPathWithExt($urn));
        }

        $firstItem = $this->matcher->getItem(0);
        $runner    = \bootstrap::getRunner();
        return $runner->showError(['urn', $firstItem['source']['request']], 'error_403', false);
    }

    protected function _parseError404(bool $forse): mixed
    {
        $this->containerService('header')->error404(false);
        if (empty($forse) && empty(self::$errTransfer)) {
            array_push(self::$errTransfer, 404);
            $urn = $this->getConfig('error_404', self::URN_AP . '/error404');
            transfer_sham($this->_getPathWithExt($urn));
        }

        $firstItem = $this->matcher->getItem(0);
        \bootstrap::logError(var_export($firstItem->parsed->toArray(), true));

        $runner = \bootstrap::getRunner();
        return $runner->showError(['urn', $firstItem['source']['request']], 'error_404', false);
    }

    protected function _parseError500(?string $errorLog = null): mixed
    {
        if (!empty($errorLog)) {
            $this->containerService('error')->logErrorMessage($errorLog, 'Tab Error');
        }
        $this->containerService('header')->error500(false);
        if (!in_array(500, self::$errTransfer)) {
            array_push(self::$errTransfer, 500);
            $urn = $this->getConfig('error_500', self::URN_AP . '/error500');
            transfer_sham($this->_getPathWithExt($urn));
        }

        $runner = \bootstrap::getRunner();
        return $runner->showError([], 'error_500', false);
    }

    protected function _getPathWithExt(string $urn, string $defaultExt = 'html'): string
    {
        $urn = (string)$urn;
        $ext = (string)$this->getConfig('default_extension', $defaultExt);
        if (empty($ext)) {
            return $urn;
        }
        $ext = '.' . $ext;
        return substr($urn, -strlen($ext)) === $ext ?
                $urn :
                $urn . $ext;
    }

    protected function _setMainBlock(): static
    {
        $mainRequest = $this->lastData['main_request'];
        $class = \bootstrap::getLoader()->loadBlockByMR($this->appName, $mainRequest);
        if (empty($class)) { // ToDo: check it!
            $this->content = $this->_parseError500('Main class for Main Request "' . implode('/', $mainRequest) . '" isn\'t found.');
        } else {
            $this->mainBlock = new $class('main', $this, null, [], false);
        }
        return $this;
    }

    protected function _setTabMeta(): static
    {
        $this->tabMeta = $this->getMainBlock()->metaMaker->assembleTab();
        return $this;
    }

    protected function _setBlocksMetaByMain(): static
    {
        $this->blocksMeta = $this->getMainBlock()->metaMaker->assembleOther();
        return $this;
    }

    protected function _setDefaultMeta(): static
    {
        $this->defaultMeta = $this->readArrayConfigValue(
            $this->getConfig(['DEFAULT_META', $this->mainBlock->getViewFormat()], []),
            'DEFAULT_META.' . $this->mainBlock->getViewFormat()
        );
        return $this;
    }

    protected function readArrayConfigValue(mixed $value, string $configPath): array
    {
        if ($value instanceof \fan\core\base\data) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        throw new \UnexpectedValueException(
            sprintf('Configuration "%s" must be an array, %s given.', $configPath, get_debug_type($value))
        );
    }

    public function _getDebugMode(): int
    {
        $debugMode = 0;
        if ($this->isDebugAllowed()) {
            $sr    = $this->containerService('request');
            $sc    = \fan\project\service\cookie::instance();
            $key   = $this->getConfig('debug_key', 'debug');
            $debug = $sr->get($key, 'PGC', 0);
            if (in_array($debug, [1, 2, 10, 20])) {
                $debugMode = (int)substr((string)$debug, 0, 1);
                $debugG = $sr->get($key, 'PG', 0);
                if ($debugG > 9) {
                    $sc->set($key, $debugMode);
                } elseif (!is_null($debugG)) {
                    $sc->delete($key);
                }
            } elseif (!is_null($debug)) {
                $sc->delete($key);
            }
        }
        return $debugMode;
    }

    protected function readBooleanFlag(mixed $value, string $name): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (bool)$value;
        }
        $value = trim((string)$value);
        if (is_numeric($value)) {
            return (bool)(int)$value;
        }

        throw new \UnexpectedValueException('Boolean config "' . $name . '" must be numeric.');
    }

    protected function _fixPerformance(float $startTime, float $initTime): void
    {
        if ($this->checkPerformance) {
            $curTime = microtime(true);
            $this->timesStamp['init_sum'] = sprintf('%01.6f', $initTime - $startTime - $this->timesStamp['consruct']);
            $this->timesStamp['add_init'] = sprintf('  %01.6f', $this->timesStamp['after_init']);
            $this->timesStamp['output']   = sprintf('  %01.6f', $curTime  - $initTime);
            $this->timesStamp['total']    = sprintf('   %01.6f', $curTime  - $startTime);
            $this->timesStamp['creating'] = sprintf('%01.6f', $this->timesStamp['consruct']);

            $len = 0;
            foreach (array_keys($this->timesStamp['init']) as $k) {
                $len = max($len, strlen($k));
            }

            arsort($this->timesStamp['init']);
            foreach ($this->timesStamp['init'] as $k => &$v) {
                $v = sprintf(str_repeat(' ', $len - strlen($k)) . '%01.6f', $v);
            }

            l('<pre style="font-family: Courier, monospace">' . htmlentities(var_export($this->timesStamp, true), ENT_NOQUOTES, 'UTF-8') . '</pre>', 'Estimate Performance by elements');
        }
    }

    // ======== The magic methods ======== \\

}
