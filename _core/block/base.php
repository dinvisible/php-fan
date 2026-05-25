<?php

declare(strict_types=1);

namespace fan\core\block;
use fan\core\di\container_interface;
use fan\core\base\meta\delayed;
use fan\core\base\meta\maker;
use fan\core\base\meta\maker_state;
use fan\core\base\meta\row;
use fan\core\block\base as block_base;
use fan\core\service\tab;
use fan\core\view\parser\loader;
use fan\core\view\router\loader_state;


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

    private mixed $roleFactory = null;

    private mixed $sessionFactory = null;

    private mixed $reflectorFactory = null;

    private ?object $runtime = null;

    private mixed $localeFactory = null;

    private mixed $entityFactory = null;
    private mixed $matcherFactory = null;
    private mixed $formFactory = null;
    private mixed $requestInputFactory = null;
    private mixed $jsonFactory = null;
    private mixed $dataLoaderFactory = null;
    private mixed $arrayAdducer = null;
    private mixed $recursiveMerger = null;
    private mixed $arrayValueReader = null;
    private mixed $arrayLikeChecker = null;
    private mixed $shortClassNameResolver = null;
    private mixed $pagerFactory = null;
    private mixed $templateFactory = null;
    private mixed $applicationFactory = null;
    private mixed $obfuscatorFactory = null;
    private mixed $imageModifyFactory = null;
    private ?object $imageMetadataReader = null;
    private mixed $configFactory = null;
    private mixed $databaseFactory = null;
    private mixed $userFactory = null;
    private mixed $logFactory = null;
    private mixed $transferFactory = null;
    private mixed $errorFactory = null;
    private mixed $dateFactory = null;
    private mixed $viewRouterFactory = null;
    private mixed $viewParserExceptionFactory = null;
    private ?object $viewLoaderState = null;
    private ?object $metaMakerState = null;
    private mixed $metaMakerFactory = null;
    private mixed $phpArrayFileLoader = null;
    private mixed $metaRowFactory = null;
    private mixed $blockFactory = null;
    private mixed $blockExceptionFactory = null;
    private ?object $errorLogWriter = null;
    private mixed $uploadSizeLimitProviderDependency = null;
    private ?object $fileStorage = null;
    private ?object $metaFileStorage = null;
    private ?object $projectToolFileStorage = null;

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
        ?tab $tab = null,
        ?base $container = null,
        array $containerMeta = [],
        bool $fullConstr = true,
        ?container_interface $serviceContainer = null,
        ?array $blockDependencies = null
    )
    {
        $blockDependencies ??= $this->resolveBlockDependencies($tab, $serviceContainer);
        $this->setBlockDependencies($blockDependencies);
        $this->_transferor(); // Unconditional transfer to another block

        $this->blockName = (string)$blockName;
        $this->tab       = empty($tab) ? $this->tabService() : $tab;
        $this->request   = $this->requestService();

        if (!empty($blockName)) {
            $this->tab->setCurrentBlock($this);
        }

        $this->metaMaker = $this->_createMetaMaker();

        if ($fullConstr) {
            $this->finishConstruct($container, $containerMeta);
        }
    }

    public function setBlockDependencies(array $dependencies): static
    {
        if (isset($dependencies['tab'])) {
            $this->tab = $dependencies['tab'];
        }
        if (isset($dependencies['requestFactory'])) {
            $this->request = ($dependencies['requestFactory'])();
        } elseif (isset($dependencies['request'])) {
            $this->request = $dependencies['request'];
        }
        if (isset($dependencies['roleFactory'])) {
            $this->roleFactory = $dependencies['roleFactory'];
        }
        if (isset($dependencies['sessionFactory'])) {
            $this->sessionFactory = $dependencies['sessionFactory'];
        }
        if (isset($dependencies['reflectorFactory'])) {
            $this->reflectorFactory = $dependencies['reflectorFactory'];
        }
        if (isset($dependencies['runtime'])) {
            $this->runtime = $dependencies['runtime'];
        }
        if (isset($dependencies['localeFactory'])) {
            $this->localeFactory = $dependencies['localeFactory'];
        }
        if (isset($dependencies['viewRouterFactory'])) {
            $this->viewRouterFactory = $dependencies['viewRouterFactory'];
        }
        if (isset($dependencies['viewLoaderState'])) {
            $this->viewLoaderState = $dependencies['viewLoaderState'];
        }
        if (isset($dependencies['metaMakerState'])) {
            $this->metaMakerState = $dependencies['metaMakerState'];
        }
        if (isset($dependencies['metaMakerFactory'])) {
            $this->metaMakerFactory = $dependencies['metaMakerFactory'];
        }
        if (isset($dependencies['phpArrayFileLoader'])) {
            $this->phpArrayFileLoader = $dependencies['phpArrayFileLoader'];
        }
        if (isset($dependencies['metaRowFactory'])) {
            $this->metaRowFactory = $dependencies['metaRowFactory'];
        }
        if (isset($dependencies['blockFactory'])) {
            $this->blockFactory = $dependencies['blockFactory'];
        }
        if (isset($dependencies['blockExceptionFactory'])) {
            $this->blockExceptionFactory = $dependencies['blockExceptionFactory'];
        }
        if (isset($dependencies['imageMetadataReader'])) {
            $this->imageMetadataReader = $dependencies['imageMetadataReader'];
        }
        if (isset($dependencies['errorLogWriter'])) {
            $this->errorLogWriter = $dependencies['errorLogWriter'];
        }
        if (isset($dependencies['blockFileStorage'])) {
            $this->fileStorage = $dependencies['blockFileStorage'];
        }
        if (isset($dependencies['metaFileStorage'])) {
            $this->metaFileStorage = $dependencies['metaFileStorage'];
        }
        if (isset($dependencies['projectToolFileStorage'])) {
            $this->projectToolFileStorage = $dependencies['projectToolFileStorage'];
        }
        if (isset($dependencies['uploadSizeLimitProvider'])) {
            $this->uploadSizeLimitProviderDependency = $dependencies['uploadSizeLimitProvider'];
            if (is_callable($this->uploadSizeLimitProviderDependency) && method_exists($this, 'setUploadSizeLimitProvider')) {
                $this->setUploadSizeLimitProvider($this->uploadSizeLimitProviderDependency);
            }
        }
        foreach ([
            'entityFactory',
            'matcherFactory',
            'formFactory',
            'requestInputFactory',
            'jsonFactory',
            'dataLoaderFactory',
            'arrayAdducer',
            'recursiveMerger',
            'arrayValueReader',
            'arrayLikeChecker',
            'shortClassNameResolver',
            'pagerFactory',
            'applicationFactory',
            'obfuscatorFactory',
            'imageModifyFactory',
            'configFactory',
            'databaseFactory',
            'userFactory',
            'logFactory',
            'transferFactory',
            'errorFactory',
            'dateFactory',
            'viewParserExceptionFactory',
        ] as $factoryKey) {
            if (isset($dependencies[$factoryKey])) {
                $this->$factoryKey = $dependencies[$factoryKey];
            }
        }

        return $this;
    }

    private function resolveBlockDependencies(?object $tab, ?container_interface $container): array
    {
        if ($tab !== null && method_exists($tab, 'getBlockDependencies')) {
            return $tab->getBlockDependencies();
        }

        if ($container === null) {
            return [];
        }

        return [
            'tab' => $container->get('tab'),
            'requestFactory' => static fn(): mixed => $container->get('request'),
            'roleFactory' => static fn(): mixed => $container->get('role'),
            'sessionFactory' => static fn(string $nameSpace, string $group = 'block'): mixed => $container->get('session', $nameSpace, $group),
            'reflectorFactory' => static fn(): mixed => $container->get('reflector'),
            'runtime' => $container->get('bootstrap_runtime'),
            'localeFactory' => static fn(): mixed => $container->get('locale'),
            'entityFactory' => static fn(mixed ...$arguments): mixed => $container->get('entity', ...$arguments),
            'matcherFactory' => static fn(): mixed => $container->get('matcher'),
            'requestInputFactory' => static fn(): mixed => $container->get('request_input'),
            'jsonFactory' => static fn(mixed ...$arguments): mixed => $container->get('json', ...$arguments),
            'dataLoaderFactory' => static fn(): mixed => $container->get('data_loader'),
            'arrayAdducer' => $container->get('array_adducer'),
            'recursiveMerger' => $container->get('recursive_merger'),
            'arrayValueReader' => $container->get('array_value_reader'),
            'arrayLikeChecker' => $container->get('array_like_checker'),
            'shortClassNameResolver' => $container->get('short_class_name_resolver'),
            'pagerFactory' => static fn(mixed ...$arguments): mixed => $container->get('pager', ...$arguments),
            'applicationFactory' => static fn(): mixed => $container->get('application'),
            'obfuscatorFactory' => static fn(mixed ...$arguments): mixed => $container->get('obfuscator', ...$arguments),
            'imageModifyFactory' => static fn(mixed ...$arguments): mixed => $container->get('image_modify', ...$arguments),
            'imageMetadataReader' => $container->get('image_metadata_reader'),
            'errorLogWriter' => $container->get('error_log_writer'),
            'blockFileStorage' => $container->has('block_file_storage') ? $container->get('block_file_storage') : null,
            'metaFileStorage' => $container->has('meta_file_storage') ? $container->get('meta_file_storage') : null,
            'projectToolFileStorage' => $container->has('project_tool_file_storage') ? $container->get('project_tool_file_storage') : null,
            'rootHtmlFileStorage' => $container->has('root_html_file_storage') ? $container->get('root_html_file_storage') : null,
            'configFactory' => static fn(mixed ...$arguments): mixed => $container->get('config', ...$arguments),
            'databaseFactory' => static fn(mixed ...$arguments): mixed => $container->get('database', ...$arguments),
            'userFactory' => static fn(mixed ...$arguments): mixed => $container->get('user', ...$arguments),
            'logFactory' => null,
            'transferFactory' => static fn(mixed ...$arguments): mixed => $container->get('transfer', ...$arguments),
            'errorFactory' => static fn(): mixed => $container->get('error'),
            'dateFactory' => static fn(mixed ...$arguments): mixed => $container->get('date', ...$arguments),
            'viewParserExceptionFactory' => $container->get('error500_exception_factory'),
            'viewRouterFactory' => $container->get('view_router_factory'),
            'viewLoaderState' => $container->get('view_loader_state'),
            'metaMakerState' => $container->get('meta_maker_state'),
            'metaMakerFactory' => $container->get('meta_maker_factory'),
            'phpArrayFileLoader' => $container->get('php_array_file_loader'),
            'metaRowFactory' => $container->get('meta_row_factory'),
            'blockFactory' => $container->get('block_factory'),
            'blockExceptionFactory' => $container->get('block_exception_factory'),
            'uploadSizeLimitProvider' => $container->has('upload_size_limit_provider') ? $container->get('upload_size_limit_provider') : null,
        ];
    }

    protected function getBlockDependencyArguments(): array
    {
        return [[
            'tab' => $this->tab,
            'request' => $this->request,
            'roleFactory' => $this->roleFactory,
            'sessionFactory' => $this->sessionFactory,
            'reflectorFactory' => $this->reflectorFactory,
            'runtime' => $this->runtime,
            'localeFactory' => $this->localeFactory,
            'entityFactory' => $this->entityFactory,
            'matcherFactory' => $this->matcherFactory,
            'formFactory' => $this->formFactory,
            'requestInputFactory' => $this->requestInputFactory,
            'jsonFactory' => $this->jsonFactory,
            'dataLoaderFactory' => $this->dataLoaderFactory,
            'arrayAdducer' => $this->arrayAdducer,
            'recursiveMerger' => $this->recursiveMerger,
            'arrayValueReader' => $this->arrayValueReader,
            'arrayLikeChecker' => $this->arrayLikeChecker,
            'shortClassNameResolver' => $this->shortClassNameResolver,
            'pagerFactory' => $this->pagerFactory,
            'templateFactory' => $this->templateFactory,
            'applicationFactory' => $this->applicationFactory,
            'obfuscatorFactory' => $this->obfuscatorFactory,
            'imageModifyFactory' => $this->imageModifyFactory,
            'imageMetadataReader' => $this->imageMetadataReader,
            'errorLogWriter' => $this->errorLogWriter,
            'blockFileStorage' => $this->fileStorage,
            'metaFileStorage' => $this->metaFileStorage,
            'projectToolFileStorage' => $this->projectToolFileStorage,
            'rootHtmlFileStorage' => null,
            'configFactory' => $this->configFactory,
            'databaseFactory' => $this->databaseFactory,
            'userFactory' => $this->userFactory,
            'logFactory' => $this->logFactory,
            'transferFactory' => $this->transferFactory,
            'errorFactory' => $this->errorFactory,
            'dateFactory' => $this->dateFactory,
            'viewParserExceptionFactory' => $this->viewParserExceptionFactory,
            'viewRouterFactory' => $this->viewRouterFactory,
            'viewLoaderState' => $this->viewLoaderState,
            'metaMakerState' => $this->metaMakerState,
            'metaMakerFactory' => $this->metaMakerFactory,
            'phpArrayFileLoader' => $this->phpArrayFileLoader,
            'metaRowFactory' => $this->metaRowFactory,
            'blockFactory' => $this->blockFactory,
            'blockExceptionFactory' => $this->blockExceptionFactory,
            'uploadSizeLimitProvider' => $this->uploadSizeLimitProviderDependency,
        ]];
    }

    private function tabService(): object
    {
        return $this->tab ?? throw new \RuntimeException('Tab service is not configured for block.');
    }

    protected function requestService(): object
    {
        return $this->request ?? throw new \RuntimeException('Request service is not configured for block.');
    }

    protected function roleService(): object
    {
        return $this->roleFactory !== null ? ($this->roleFactory)() : throw new \RuntimeException('Role service is not configured for block.');
    }

    protected function sessionService(string $nameSpace, string $group = 'block'): object
    {
        return $this->sessionFactory !== null ? ($this->sessionFactory)($nameSpace, $group) : throw new \RuntimeException('Session service is not configured for block.');
    }

    private function reflectorService(): object
    {
        return $this->reflectorFactory !== null ? ($this->reflectorFactory)() : throw new \RuntimeException('Reflector service is not configured for block.');
    }

    protected function runtimeService(): object
    {
        return $this->runtime ?? throw new \RuntimeException('Bootstrap runtime service is not configured for block.');
    }

    protected function localeService(): object
    {
        return $this->localeFactory !== null ? ($this->localeFactory)() : throw new \RuntimeException('Locale service is not configured for block.');
    }

    protected function currentSessionService(): object
    {
        return $this->sessionService(get_class($this), 'block');
    }

    private function factoryService(string $factoryKey, string $serviceName, mixed ...$arguments): object
    {
        return $this->$factoryKey !== null ?
            ($this->$factoryKey)(...$arguments) :
            throw new \RuntimeException($serviceName . ' service is not configured for block.');
    }

    protected function entityService(mixed ...$arguments): object
    {
        return $this->factoryService('entityFactory', 'Entity', ...$arguments);
    }

    protected function matcherService(): object
    {
        return $this->factoryService('matcherFactory', 'Matcher');
    }

    protected function formService(mixed ...$arguments): object
    {
        return $this->factoryService('formFactory', 'Form', ...$arguments);
    }

    protected function requestInputService(): object
    {
        return $this->factoryService('requestInputFactory', 'Request input');
    }

    protected function jsonService(mixed ...$arguments): object
    {
        return $this->factoryService('jsonFactory', 'Json', ...$arguments);
    }

    protected function dataLoaderService(): object
    {
        return $this->factoryService('dataLoaderFactory', 'Data loader');
    }

    protected function arrayAdducer(): callable
    {
        if (!is_callable($this->arrayAdducer)) {
            throw new \RuntimeException('Array adducer dependency is not configured for block.');
        }

        return $this->arrayAdducer;
    }

    protected function recursiveMerger(): callable
    {
        if (!is_callable($this->recursiveMerger)) {
            throw new \RuntimeException('Recursive merger dependency is not configured for block.');
        }

        return $this->recursiveMerger;
    }

    protected function arrayValueReader(): callable
    {
        if (!is_callable($this->arrayValueReader)) {
            throw new \RuntimeException('Array value reader dependency is not configured for block.');
        }

        return $this->arrayValueReader;
    }

    protected function pagerService(mixed ...$arguments): object
    {
        return $this->factoryService('pagerFactory', 'Pager', ...$arguments);
    }

    protected function templateService(mixed ...$arguments): object
    {
        return $this->factoryService('templateFactory', 'Template', ...$arguments);
    }

    protected function applicationService(): object
    {
        return $this->factoryService('applicationFactory', 'Application');
    }

    protected function obfuscatorService(string $type): object
    {
        return $this->factoryService('obfuscatorFactory', 'Obfuscator', $type);
    }

    protected function imageModifyService(mixed ...$arguments): object
    {
        return $this->factoryService('imageModifyFactory', 'Image modify', ...$arguments);
    }

    protected function imageMetadataReader(): object
    {
        return $this->imageMetadataReader ?? throw new \RuntimeException('Image metadata reader is not configured for block.');
    }

    protected function errorLogWriter(): object
    {
        return $this->errorLogWriter ?? throw new \RuntimeException('Error log writer is not configured for block.');
    }

    protected function fileStorage(): object
    {
        return $this->fileStorage ?? throw new \RuntimeException('Block file storage is not configured for block.');
    }

    private function metaFileStorage(): object
    {
        return $this->metaFileStorage ?? throw new \RuntimeException('Meta file storage is not configured for block.');
    }

    protected function configService(mixed ...$arguments): object
    {
        return $this->factoryService('configFactory', 'Config', ...$arguments);
    }

    protected function databaseService(mixed ...$arguments): object
    {
        return $this->factoryService('databaseFactory', 'Database', ...$arguments);
    }

    protected function userService(mixed ...$arguments): object
    {
        return $this->factoryService('userFactory', 'User', ...$arguments);
    }

    protected function logService(mixed ...$arguments): object
    {
        return $this->factoryService('logFactory', 'Log', ...$arguments);
    }

    protected function transferService(mixed ...$arguments): object
    {
        return $this->factoryService('transferFactory', 'Transfer', ...$arguments);
    }

    protected function errorService(): object
    {
        return $this->factoryService('errorFactory', 'Error');
    }

    protected function dateService(mixed ...$arguments): object
    {
        return $this->factoryService('dateFactory', 'Date', ...$arguments);
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
        return $convToArray && is_object($meta) && $meta instanceof row ? $meta->toArray() : $meta;
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
    protected function addMeta(array|row $value): static
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

    public function makeDelayedMeta(row $meta): void
    {
        foreach ($meta as $k => $v) {
            if (is_object($v)) {
                if ($v instanceof delayed) {
                    $meta[$k] = $v->getValue();
                } elseif ($v instanceof row) {
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
            if (isset($v['condition']) && !$this->roleService()->check($v['condition'])) {
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
        $viewClass = $this->tab->getViewClass();

        return $viewClass::getFormat($this->viewParserExceptionFactory);
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
        if ($this->fileStorage()->isFile($templatePath)) {
            $this->template = $templatePath;
            return true;
        } elseif ($allowException) {
            throw $this->createBlockFatalException('Incorrect template path "' . $templatePath . '"');
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
            $this->_makeBlockException('Call to unknown Embedded Block "' . $key . '"', 'local', null, E_USER_WARNING);
        }
        return $this->embeddedBlocks[$key];
    }

    public function getExceptionDbOper(): ?string
    {
        return $this->exceptionDbOper;
    }

    public function getSession(): object
    {
        return $this->sessionService(get_class($this), 'block');
    }

    public function checkRunInit(): bool
    {
        return true;
    }

    public function getDebugInfo(): array
    {
        $metaSourse = $this->metaMaker->getSource();
        $parentPaths  = $this->reflectorService()->getParentPaths($this);
        $currentPath = substr((string)reset($parentPaths), 0, -3);

        return [
            'blockName'      => $this->blockName,
            'className'      => get_class($this),
            'templateFile'   => $this->template,
            'metaFile'       => $this->fileStorage()->exists($currentPath . 'meta.php') ? $currentPath . 'meta.php' : null,
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
        $factory = $this->metaMakerFactory();
        $metaMaker = $factory(
            $this,
            $this->reflectorService(),
            $this->metaMakerState(),
            $this->phpArrayFileLoader(),
            $this->metaRowFactory(),
            $this->metaFileStorage(),
            $this->blockExceptionFactory
        );

        if (!$metaMaker instanceof maker) {
            throw new \UnexpectedValueException('Meta maker factory must return a meta maker.');
        }

        return $metaMaker;
    }

    private function metaMakerState(): maker_state
    {
        if (!$this->metaMakerState instanceof maker_state) {
            throw new \RuntimeException('Meta maker state is not configured for block.');
        }

        return $this->metaMakerState;
    }

    private function metaMakerFactory(): callable
    {
        if (!is_callable($this->metaMakerFactory)) {
            throw new \RuntimeException('Meta maker factory is not configured for block.');
        }

        return $this->metaMakerFactory;
    }

    private function phpArrayFileLoader(): callable
    {
        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP array file loader is not configured for block.');
        }

        return $this->phpArrayFileLoader;
    }

    private function metaRowFactory(): callable
    {
        if (!is_callable($this->metaRowFactory)) {
            throw new \RuntimeException('Meta row factory is not configured for block.');
        }

        return $this->metaRowFactory;
    }

    protected function _createViewRouter(): object
    {
        $viewClass = $this->tab->getViewClass();
        $factory = $this->viewRouterFactory();
        $loaderState = is_a($viewClass, loader::class, true) ? $this->viewLoaderState() : null;
        $router = $factory($viewClass, $this, $loaderState, $this->blockExceptionFactory);
        if (!is_object($router)) {
            throw new \UnexpectedValueException('View router factory must return an object.');
        }

        return $router;
    }

    private function viewRouterFactory(): callable
    {
        if (!is_callable($this->viewRouterFactory)) {
            throw new \RuntimeException('View router factory is not configured for block.');
        }

        return $this->viewRouterFactory;
    }

    private function viewLoaderState(): loader_state
    {
        if (!$this->viewLoaderState instanceof loader_state) {
            throw new \RuntimeException('Loader state is not configured for block.');
        }

        return $this->viewLoaderState;
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

    protected function _setRootBlockParameters(?block_base $root = null, array $rootKeys = []): static
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
                if (is_array($v1) || is_object($v1) && $v1 instanceof row) {
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

    protected function _setTplVarsByMeta(array|row $tplVars): static
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
        $paths    = $this->reflectorService()->getParentPaths($this);
        $suffixes = $this->_getTplSuffixes();

        // If template-name isn't defined - try to get it from the Meta
        if (!$templateName) {
            $templateName = $this->getMeta('template');
        }
        // If template-name is defined - check and set it
        if ($templateName) {
            // If Template Name is set as full path
            if ($this->_checkTemplate('', $this->runtimeService()->parsePath($templateName), $suffixes)) {
                return $this;
            }

            // If Template Name is set as base name (concat with block path)
            reset($paths);
            if ($this->_checkTemplate(current($paths), $templateName, $suffixes)) {
                return $this;
            }

            // Throw exception if defined template-name incorrect
            throw $this->createBlockFatalException('Incorrect template name "' . $templateName . '"');
        }

        // Try to find template by block-name
        foreach ($paths as $class => $path) {
            if ($this->_checkTemplate($path, $this->shortClassName($class), $suffixes)) {
                return $this;
            }
        }
        return $this;
    }

    private function shortClassName(object|string $object): string
    {
        if (is_callable($this->shortClassNameResolver)) {
            $className = ($this->shortClassNameResolver)($object);
            if (!is_string($className) || $className === '') {
                throw new \UnexpectedValueException('Short class-name resolver must return a non-empty string.');
            }

            return $className;
        }

        $className = is_object($object) ? get_class($object) : $object;
        $position = strrpos($className, '\\');

        return $position === false ? $className : substr($className, $position + 1);
    }

    protected function _getTplSuffixes(string $separator = '_'): array
    {
        $suffixes = [''];
        if ($this->getMeta('useMultiLanguage')) {
            $lng = $this->localeService()->getLanguage();
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
                            throw $this->createBlockFatalException('Main Block isn\'t set.');
                        } else {
                            $this->embeddedBlocks[$k] = $main;
                            $main->finishConstruct($this, $containerMeta);
                        }
                    } else{
                        $class = $this->_parseClassName($v);
                        if ($class) {
                            $this->embeddedBlocks[$k] = $this->createEmbeddedBlock($class, (string)$k, $containerMeta);
                            $this->tab->setCurrentBlock($this);
                        }
                    }
                }
            }
        }
        return $this;
    }

    private function createEmbeddedBlock(string $class, string $blockName, array $containerMeta): base
    {
        if (!is_callable($this->blockFactory)) {
            throw new \RuntimeException('Block factory is not configured for embedded block creation.');
        }

        $block = ($this->blockFactory)(
            $class,
            $blockName,
            $this->tab,
            $this,
            $containerMeta,
            true,
            null,
            $this->getBlockDependencyArguments()[0]
        );
        if (!$block instanceof base) {
            throw new \UnexpectedValueException('Block factory must return a block object.');
        }

        return $block;
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
        throw $this->createBlockException($class, $logErrMsg, $code, $previous);
    }

    private function createBlockException(string $class, string $logErrMsg, int $code, ?\Exception $previous): \Throwable
    {
        if (!is_callable($this->blockExceptionFactory)) {
            throw new \RuntimeException('Block exception factory is not configured for block.');
        }

        $exception = ($this->blockExceptionFactory)($class, $this, $logErrMsg, $code, $previous);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('Block exception factory must return a throwable object.');
        }

        return $exception;
    }

    private function createBlockFatalException(string $logErrMsg, int $code = E_USER_ERROR, ?\Exception $previous = null): \Throwable
    {
        return $this->createBlockException('\fan\project\exception\block\fatal', $logErrMsg, $code, $previous);
    }

    /**
     * @throws \fan\core\exception\block\fatal
     */
    protected function _parseClassName(string $blockPath, bool $allowException = true): ?string
    {
        $class = $this->tab->loadBlock($blockPath);
        if (empty($class) && $allowException) {
            throw $this->createBlockFatalException('Unknown block path "' . $blockPath . '" for Embedded Block');
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
        return $object->{$method}(...(empty($args) ? [] : $args));
    }

    protected function _callIdentifiedDelegate(object $object, string $method, array $args): mixed
    {
        if (empty($args)) {
            $args = [];
        }
        array_unshift($args, $this);
        return $object->{$method}(...$args);
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
                    $object = $this->delegateObject($objectName);
                    foreach ($param as $v) {
                        $object = $object->{$v}();
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

    private function delegateObject(string $objectName): object
    {
        return match ($objectName) {
            'this' => $this,
            'tab' => $this->getTab(),
            default => throw new \RuntimeException('Delegate object "' . $objectName . '" is not configured for block.'),
        };
    }

    // ======== Required Interface methods ======== \\
}
