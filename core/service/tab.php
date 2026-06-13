<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\core\adapter\twig_template_service;
use fan\core\base\data;
use fan\core\base\service_dependencies;
use fan\core\base\service\single;
use fan\core\base\transfer as base_transfer;
use fan\core\block\base;
use fan\core\view\parser;


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
class tab extends single
{
    /**
     *  Marker of URN application prefix
     */
    public const URN_AP = '~';

    protected array $engine = [];

    protected array $delegateRule = [
        'urlMaker' => ['isUseHttps', 'getCurrentURI', 'getModifiedCurrentURI', 'getURI', 'addQuery', 'getDefaultExtension'],
    ];

    /**
     * @var \fan\core\service\matcher
     */
    protected ?object $matcher = null;

    protected ?object $request = null;

    protected ?object $locale = null;

    protected mixed $sessionFactory = null;

    protected ?object $input = null;

    protected ?object $runtime = null;

    protected mixed $roleFactory = null;

    protected mixed $transferFactory = null;

    protected mixed $configFactory = null;

    protected mixed $headerFactory = null;

    protected mixed $applicationFactory = null;

    protected mixed $debugFactory = null;

    protected mixed $jsonFactory = null;

    protected mixed $dataLoaderFactory = null;

    protected mixed $arrayAdducer = null;

    protected mixed $recursiveMerger = null;

    protected mixed $arrayValueReader = null;

    protected mixed $arrayLikeChecker = null;

    protected mixed $shortClassNameResolver = null;

    protected mixed $viewClassExists = null;

    protected mixed $templateFactory = null;

    protected mixed $errorFactory = null;

    protected mixed $logFactory = null;

    protected mixed $cookieFactory = null;

    protected mixed $reflectorFactory = null;

    protected mixed $entityFactory = null;
    protected mixed $formFactory = null;
    protected mixed $pagerFactory = null;
    protected mixed $obfuscatorFactory = null;
    protected mixed $imageModifyFactory = null;
    protected mixed $databaseFactory = null;
    protected mixed $userFactory = null;
    protected mixed $dateFactory = null;
    protected mixed $phpArrayFileLoader = null;
    protected mixed $delegateFactory = null;
    protected mixed $viewParserFactory = null;
    protected mixed $blockFactory = null;
    protected mixed $blockExceptionFactory = null;
    protected mixed $metaMakerFactory = null;
    protected mixed $metaRowFactory = null;
    protected mixed $uploadSizeLimitProvider = null;
    protected mixed $viewDefinerFactory = null;
    protected mixed $viewRouterFactory = null;
    protected mixed $viewLoaderStateFactory = null;
    protected mixed $metaMakerStateFactory = null;
    protected ?object $aliasFileStorage = null;
    protected ?object $imageMetadataReader = null;
    protected ?object $errorLogWriter = null;
    protected ?object $blockFileStorage = null;
    protected ?object $metaFileStorage = null;
    protected ?object $projectToolFileStorage = null;
    protected ?object $rootHtmlFileStorage = null;

    private ?object $tabState = null;

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


    public function __construct(
        bool $allowIni = true,
        ?object $matcher = null,
        ?object $request = null,
        ?object $locale = null,
        ?callable $sessionFactory = null,
        ?object $input = null,
        ?object $runtime = null,
        ?callable $roleFactory = null,
        ?callable $transferFactory = null,
        ?callable $configFactory = null,
        ?callable $headerFactory = null,
        ?callable $applicationFactory = null,
        ?callable $debugFactory = null,
        ?callable $jsonFactory = null,
        ?callable $dataLoaderFactory = null,
        ?callable $templateFactory = null,
        ?callable $errorFactory = null,
        ?callable $logFactory = null,
        ?callable $cookieFactory = null,
        ?callable $reflectorFactory = null,
        ?callable $entityFactory = null,
        ?callable $formFactory = null,
        ?callable $pagerFactory = null,
        ?callable $obfuscatorFactory = null,
        ?callable $imageModifyFactory = null,
        ?callable $databaseFactory = null,
        ?callable $userFactory = null,
        ?callable $dateFactory = null,
        ?object $tabState = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $delegateFactory = null,
        ?callable $viewParserFactory = null,
        ?callable $blockFactory = null,
        ?callable $blockExceptionFactory = null,
        ?callable $metaRowFactory = null,
        ?object $aliasFileStorage = null,
        ?callable $viewDefinerFactory = null,
        ?callable $metaMakerFactory = null,
        ?callable $viewRouterFactory = null,
        ?callable $viewLoaderStateFactory = null,
        ?callable $metaMakerStateFactory = null,
        ?callable $arrayAdducer = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayValueReader = null,
        ?callable $classNameResolver = null,
        ?callable $arrayLikeChecker = null,
        ?callable $shortClassNameResolver = null,
        ?object $imageMetadataReader = null,
        ?object $errorLogWriter = null,
        ?object $blockFileStorage = null,
        ?object $metaFileStorage = null,
        ?object $projectToolFileStorage = null,
        ?object $rootHtmlFileStorage = null,
        ?tab_dependencies $tabDependencies = null,
        ?service_dependencies $serviceDependencies = null
    )
    {
        $this->matcher = $matcher;
        $this->request = $request;
        $this->locale = $locale;
        $this->sessionFactory = $sessionFactory;
        $this->input = $input;
        $this->applyTabDependencies($tabDependencies ?? tab_dependencies::fromLegacy(
            $runtime,
            $roleFactory,
            $transferFactory,
            $configFactory,
            $headerFactory,
            $applicationFactory,
            $debugFactory,
            $jsonFactory,
            $dataLoaderFactory,
            $templateFactory,
            $errorFactory,
            $logFactory,
            $cookieFactory,
            $reflectorFactory,
            $entityFactory,
            $formFactory,
            $pagerFactory,
            $obfuscatorFactory,
            $imageModifyFactory,
            $databaseFactory,
            $userFactory,
            $dateFactory,
            $phpArrayFileLoader,
            $delegateFactory,
            $viewParserFactory,
            $blockFactory,
            $blockExceptionFactory,
            $metaRowFactory,
            null,
            $aliasFileStorage,
            $viewDefinerFactory,
            $metaMakerFactory,
            $viewRouterFactory,
            $viewLoaderStateFactory,
            $metaMakerStateFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $arrayLikeChecker,
            $shortClassNameResolver,
            $imageMetadataReader,
            $errorLogWriter,
            $blockFileStorage,
            $metaFileStorage,
            $projectToolFileStorage,
            $rootHtmlFileStorage,
            $tabState
        ));
        parent::__construct(
            $allowIni,
            $serviceDependencies ?? service_dependencies::fromLegacy(
                $serviceBootstrapRuntime,
                $serviceConfigurator,
                $serviceCacheFactory,
                null,
                null,
                null,
                null,
                $classNameResolver,
                $arrayValueReader
            )
        );
    }

    // ======== Main Interface methods ======== \\

    public function handleContent(): mixed
    {
        return $this->_controlTabTransfer()->content;
    }

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
            $viewDefinerFactory = $this->viewDefinerFactory();
            $this->viewDefiner = $viewDefinerFactory(
                $this->config->get('VIEW_DEFINER', [])->toArray(),
                $this->requestService(),
                $this
            );
            if (!is_object($this->viewDefiner)) {
                throw new \UnexpectedValueException('View definer factory must return an object.');
            }
        }
        return $this->viewDefiner;
    }
    public function getViewClass(): ?string
    {
        return $this->viewClass;
    }

    public function setTabDependencies(
        ?object $runtime = null,
        ?callable $roleFactory = null,
        ?callable $transferFactory = null,
        ?callable $configFactory = null,
        ?callable $headerFactory = null,
        ?callable $applicationFactory = null,
        ?callable $debugFactory = null,
        ?callable $jsonFactory = null,
        ?callable $dataLoaderFactory = null,
        ?callable $templateFactory = null,
        ?callable $errorFactory = null,
        ?callable $logFactory = null,
        ?callable $cookieFactory = null,
        ?callable $reflectorFactory = null,
        ?callable $entityFactory = null,
        ?callable $formFactory = null,
        ?callable $pagerFactory = null,
        ?callable $obfuscatorFactory = null,
        ?callable $imageModifyFactory = null,
        ?callable $databaseFactory = null,
        ?callable $userFactory = null,
        ?callable $dateFactory = null,
        ?callable $phpArrayFileLoader = null,
        ?callable $delegateFactory = null,
        ?callable $viewParserFactory = null,
        ?callable $blockFactory = null,
        ?callable $blockExceptionFactory = null,
        ?callable $metaRowFactory = null,
        ?callable $uploadSizeLimitProvider = null,
        ?object $aliasFileStorage = null,
        ?callable $viewDefinerFactory = null,
        ?callable $metaMakerFactory = null,
        ?callable $viewRouterFactory = null,
        ?callable $viewLoaderStateFactory = null,
        ?callable $metaMakerStateFactory = null,
        ?callable $arrayAdducer = null,
        ?callable $recursiveMerger = null,
        ?callable $arrayValueReader = null,
        ?callable $arrayLikeChecker = null,
        ?callable $shortClassNameResolver = null,
        ?object $imageMetadataReader = null,
        ?object $errorLogWriter = null,
        ?object $blockFileStorage = null,
        ?object $metaFileStorage = null,
        ?object $projectToolFileStorage = null,
        ?object $rootHtmlFileStorage = null,
        ?callable $viewClassExists = null
    ): static
    {
        $this->applyTabDependencies(tab_dependencies::fromLegacy(
            $runtime,
            $roleFactory,
            $transferFactory,
            $configFactory,
            $headerFactory,
            $applicationFactory,
            $debugFactory,
            $jsonFactory,
            $dataLoaderFactory,
            $templateFactory,
            $errorFactory,
            $logFactory,
            $cookieFactory,
            $reflectorFactory,
            $entityFactory,
            $formFactory,
            $pagerFactory,
            $obfuscatorFactory,
            $imageModifyFactory,
            $databaseFactory,
            $userFactory,
            $dateFactory,
            $phpArrayFileLoader,
            $delegateFactory,
            $viewParserFactory,
            $blockFactory,
            $blockExceptionFactory,
            $metaRowFactory,
            $uploadSizeLimitProvider,
            $aliasFileStorage,
            $viewDefinerFactory,
            $metaMakerFactory,
            $viewRouterFactory,
            $viewLoaderStateFactory,
            $metaMakerStateFactory,
            $arrayAdducer,
            $recursiveMerger,
            $arrayValueReader,
            $arrayLikeChecker,
            $shortClassNameResolver,
            $imageMetadataReader,
            $errorLogWriter,
            $blockFileStorage,
            $metaFileStorage,
            $projectToolFileStorage,
            $rootHtmlFileStorage
        ));
        if ($viewClassExists !== null) {
            $this->viewClassExists = \Closure::fromCallable($viewClassExists);
        }

        return $this;
    }

    public function applyTabDependencies(tab_dependencies $dependencies): static
    {
        if ($dependencies->runtime !== null) {
            $this->runtime = $dependencies->runtime;
        }
        if ($dependencies->tabState !== null) {
            $this->tabState = $dependencies->tabState;
        }

        foreach ([
            'roleFactory' => $dependencies->serviceFactories->roleFactory,
            'transferFactory' => $dependencies->serviceFactories->transferFactory,
            'configFactory' => $dependencies->serviceFactories->configFactory,
            'headerFactory' => $dependencies->serviceFactories->headerFactory,
            'applicationFactory' => $dependencies->serviceFactories->applicationFactory,
            'debugFactory' => $dependencies->serviceFactories->debugFactory,
            'jsonFactory' => $dependencies->serviceFactories->jsonFactory,
            'dataLoaderFactory' => $dependencies->serviceFactories->dataLoaderFactory,
            'templateFactory' => $dependencies->serviceFactories->templateFactory,
            'errorFactory' => $dependencies->serviceFactories->errorFactory,
            'logFactory' => $dependencies->serviceFactories->logFactory,
            'cookieFactory' => $dependencies->serviceFactories->cookieFactory,
            'reflectorFactory' => $dependencies->serviceFactories->reflectorFactory,
            'entityFactory' => $dependencies->serviceFactories->entityFactory,
            'formFactory' => $dependencies->serviceFactories->formFactory,
            'pagerFactory' => $dependencies->serviceFactories->pagerFactory,
            'obfuscatorFactory' => $dependencies->serviceFactories->obfuscatorFactory,
            'imageModifyFactory' => $dependencies->serviceFactories->imageModifyFactory,
            'databaseFactory' => $dependencies->serviceFactories->databaseFactory,
            'userFactory' => $dependencies->serviceFactories->userFactory,
            'dateFactory' => $dependencies->serviceFactories->dateFactory,
            'phpArrayFileLoader' => $dependencies->serviceFactories->phpArrayFileLoader,
            'delegateFactory' => $dependencies->viewFactories->delegateFactory,
            'viewParserFactory' => $dependencies->viewFactories->viewParserFactory,
            'blockFactory' => $dependencies->viewFactories->blockFactory,
            'blockExceptionFactory' => $dependencies->viewFactories->blockExceptionFactory,
            'metaMakerFactory' => $dependencies->viewFactories->metaMakerFactory,
            'metaRowFactory' => $dependencies->viewFactories->metaRowFactory,
            'uploadSizeLimitProvider' => $dependencies->viewFactories->uploadSizeLimitProvider,
            'viewDefinerFactory' => $dependencies->viewFactories->viewDefinerFactory,
            'viewRouterFactory' => $dependencies->viewFactories->viewRouterFactory,
            'viewLoaderStateFactory' => $dependencies->viewFactories->viewLoaderStateFactory,
            'metaMakerStateFactory' => $dependencies->viewFactories->metaMakerStateFactory,
            'arrayAdducer' => $dependencies->utilityDependencies->arrayAdducer,
            'recursiveMerger' => $dependencies->utilityDependencies->recursiveMerger,
            'arrayValueReader' => $dependencies->utilityDependencies->arrayValueReader,
            'arrayLikeChecker' => $dependencies->utilityDependencies->arrayLikeChecker,
            'shortClassNameResolver' => $dependencies->utilityDependencies->shortClassNameResolver,
            'aliasFileStorage' => $dependencies->storageDependencies->aliasFileStorage,
            'imageMetadataReader' => $dependencies->storageDependencies->imageMetadataReader,
            'errorLogWriter' => $dependencies->storageDependencies->errorLogWriter,
            'blockFileStorage' => $dependencies->storageDependencies->blockFileStorage,
            'metaFileStorage' => $dependencies->storageDependencies->metaFileStorage,
            'projectToolFileStorage' => $dependencies->storageDependencies->projectToolFileStorage,
            'rootHtmlFileStorage' => $dependencies->storageDependencies->rootHtmlFileStorage,
        ] as $property => $dependency) {
            if ($dependency !== null) {
                $this->$property = $dependency;
            }
        }

        return $this;
    }

    public function setUploadSizeLimitProvider(callable $uploadSizeLimitProvider): static
    {
        $this->uploadSizeLimitProvider = $uploadSizeLimitProvider;

        return $this;
    }

    public function getBlockDependencies(): array
    {
        $dependencies = [
            'tab' => $this,
            'requestFactory' => fn(): object => $this->requestService(),
            'roleFactory' => fn(): object => $this->roleService(),
            'sessionFactory' => fn(string $nameSpace, string $group = 'block'): object => $this->sessionService($nameSpace, $group),
            'reflectorFactory' => fn(): object => $this->reflectorService(),
            'runtime' => $this->runtimeService(),
            'localeFactory' => fn(): object => $this->localeService(),
            'entityFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('entityFactory', 'Entity', ...$arguments),
            'matcherFactory' => fn(): object => $this->matcherService(),
            'requestInputFactory' => fn(): object => $this->inputService(),
            'jsonFactory' => fn(bool $useBase64 = false): object => $this->jsonService($useBase64),
            'dataLoaderFactory' => fn(): object => $this->dataLoaderService(),
            'arrayAdducer' => $this->arrayAdducer(),
            'recursiveMerger' => $this->recursiveMerger(),
            'arrayValueReader' => $this->arrayValueReader(),
            'arrayLikeChecker' => $this->arrayLikeChecker(),
            'shortClassNameResolver' => $this->shortClassNameResolver(),
            'pagerFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('pagerFactory', 'Pager', ...$arguments),
            'templateFactory' => fn(): object => $this->templateService(),
            'applicationFactory' => fn(): object => $this->applicationService(),
            'obfuscatorFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('obfuscatorFactory', 'Obfuscator', ...$arguments),
            'imageModifyFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('imageModifyFactory', 'Image modify', ...$arguments),
            'configFactory' => fn(string $type = 'service', string $sourceType = 'arr'): object => $this->configService($type, $sourceType),
            'databaseFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('databaseFactory', 'Database', ...$arguments),
            'userFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('userFactory', 'User', ...$arguments),
            'logFactory' => null,
            'transferFactory' => fn(): object => $this->transferService(),
            'errorFactory' => fn(): object => $this->errorService(),
            'dateFactory' => fn(mixed ...$arguments): object => $this->tabFactoryService('dateFactory', 'Date', ...$arguments),
            'viewRouterFactory' => $this->viewRouterFactory(),
            'viewLoaderState' => method_exists($this->runtimeService(), 'viewLoaderState')
                ? $this->runtimeService()->viewLoaderState()
                : ($this->viewLoaderStateFactory())(),
            'metaMakerState' => method_exists($this->runtimeService(), 'metaMakerState')
                ? $this->runtimeService()->metaMakerState()
                : ($this->metaMakerStateFactory())(),
            'metaMakerFactory' => $this->metaMakerFactory(),
            'phpArrayFileLoader' => $this->phpArrayFileLoader(),
            'metaRowFactory' => $this->metaRowFactory(),
            'blockFactory' => $this->blockFactory,
            'blockExceptionFactory' => $this->blockExceptionFactory,
            'imageMetadataReader' => $this->imageMetadataReader,
            'errorLogWriter' => $this->errorLogWriter,
            'blockFileStorage' => $this->blockFileStorage,
            'metaFileStorage' => $this->metaFileStorage,
            'projectToolFileStorage' => $this->projectToolFileStorage,
            'rootHtmlFileStorage' => $this->rootHtmlFileStorage,
        ];

        if (is_callable($this->uploadSizeLimitProvider)) {
            $dependencies['uploadSizeLimitProvider'] = $this->uploadSizeLimitProvider;
        }

        return $dependencies;
    }

    private function phpArrayFileLoader(): callable
    {
        if (!is_callable($this->phpArrayFileLoader)) {
            throw new \RuntimeException('PHP array file loader is not configured for tab service.');
        }

        return $this->phpArrayFileLoader;
    }

    private function metaRowFactory(): callable
    {
        if (!is_callable($this->metaRowFactory)) {
            throw new \RuntimeException('Meta row factory is not configured for tab service.');
        }

        return $this->metaRowFactory;
    }

    private function metaMakerFactory(): callable
    {
        if (!is_callable($this->metaMakerFactory)) {
            throw new \RuntimeException('Meta maker factory is not configured for tab service.');
        }

        return $this->metaMakerFactory;
    }

    private function viewRouterFactory(): callable
    {
        if (!is_callable($this->viewRouterFactory)) {
            throw new \RuntimeException('View router factory is not configured for tab service.');
        }

        return $this->viewRouterFactory;
    }

    private function viewLoaderStateFactory(): callable
    {
        if (!is_callable($this->viewLoaderStateFactory)) {
            throw new \RuntimeException('View loader state factory is not configured for tab service.');
        }

        return $this->viewLoaderStateFactory;
    }

    private function metaMakerStateFactory(): callable
    {
        if (!is_callable($this->metaMakerStateFactory)) {
            throw new \RuntimeException('Meta maker state factory is not configured for tab service.');
        }

        return $this->metaMakerStateFactory;
    }

    private function aliasFileStorage(): object
    {
        return $this->aliasFileStorage ?? throw new \RuntimeException('Tab alias file storage is not configured for tab service.');
    }

    private function viewDefinerFactory(): callable
    {
        if (!is_callable($this->viewDefinerFactory)) {
            throw new \RuntimeException('View definer factory is not configured for tab service.');
        }

        return $this->viewDefinerFactory;
    }

    protected function _getDelegate(mixed $class): mixed
    {
        if ($class !== 'urlMaker') {
            return parent::_getDelegate($class);
        }
        if (!empty($this->delegate[$class])) {
            return $this->delegate[$class];
        }

        $className = $this->_getEngine('delegate\\' . $class, false);
        if (empty($className)) {
            throw $this->createServiceFatalException('Delegate service class "' . $class . '" isn\'t found!');
        }

        $this->delegate[$class] = $this->createDelegate(
            $className,
            $this->matcherService(),
            $this->requestService(),
            $this->localeService(),
            $this->sessionFactory,
            $this->inputService(),
            $this->arrayValueReader()
        );
        $this->delegate[$class]->setFacade($this);

        return $this->delegate[$class];
    }

    private function createDelegate(
        string $className,
        object $matcher,
        object $request,
        object $locale,
        ?callable $sessionFactory,
        object $input,
        callable $arrayValueReader
    ): object
    {
        if (!is_callable($this->delegateFactory)) {
            throw new \RuntimeException('Tab delegate factory is not configured for tab service.');
        }

        $delegate = ($this->delegateFactory)($className, $matcher, $request, $locale, $sessionFactory, $input, $arrayValueReader);
        if (!is_object($delegate)) {
            throw new \UnexpectedValueException('Tab delegate factory must return an object.');
        }

        return $delegate;
    }

    private function matcherService(): object
    {
        if ($this->matcher !== null) {
            return $this->matcher;
        }

        throw new \RuntimeException('Matcher service is not configured for tab service.');
    }

    private function requestService(): object
    {
        if ($this->request !== null) {
            return $this->request;
        }

        throw new \RuntimeException('Request service is not configured for tab service.');
    }

    private function localeService(): object
    {
        if ($this->locale !== null) {
            return $this->locale;
        }

        throw new \RuntimeException('Locale service is not configured for tab service.');
    }

    private function inputService(): object
    {
        if ($this->input !== null) {
            return $this->input;
        }

        throw new \RuntimeException('Request input service is not configured for tab service.');
    }

    private function runtimeService(): object
    {
        return $this->runtime ?? throw new \RuntimeException('Bootstrap runtime service is not configured for tab service.');
    }

    private function roleService(): object
    {
        return $this->roleFactory !== null ? ($this->roleFactory)() : throw new \RuntimeException('Role service is not configured for tab service.');
    }

    private function sessionService(string $nameSpace = '', string $group = 'custom'): object
    {
        return $this->sessionFactory !== null ? ($this->sessionFactory)($nameSpace, $group) : throw new \RuntimeException('Session service is not configured for tab service.');
    }

    private function transferService(): object
    {
        return $this->transferFactory !== null ? ($this->transferFactory)() : throw new \RuntimeException('Transfer service is not configured for tab service.');
    }

    private function configService(string $type = 'service', string $sourceType = 'arr'): object
    {
        return $this->configFactory !== null ? ($this->configFactory)($type, $sourceType) : throw new \RuntimeException('Config service is not configured for tab service.');
    }

    private function headerService(): object
    {
        return $this->headerFactory !== null ? ($this->headerFactory)() : throw new \RuntimeException('Header service is not configured for tab service.');
    }

    private function applicationService(): object
    {
        return $this->applicationFactory !== null ? ($this->applicationFactory)() : throw new \RuntimeException('Application service is not configured for tab service.');
    }

    private function debugService(): object
    {
        return $this->debugFactory !== null ? ($this->debugFactory)() : throw new \RuntimeException('Debug service is not configured for tab service.');
    }

    private function jsonService(bool $useBase64 = false): object
    {
        return $this->jsonFactory !== null ? ($this->jsonFactory)($useBase64) : throw new \RuntimeException('Json service is not configured for tab service.');
    }

    private function dataLoaderService(): object
    {
        return $this->dataLoaderFactory !== null ? ($this->dataLoaderFactory)() : throw new \RuntimeException('Data loader service is not configured for tab service.');
    }

    private function arrayAdducer(): callable
    {
        return is_callable($this->arrayAdducer) ? $this->arrayAdducer : throw new \RuntimeException('Array adducer is not configured for tab service.');
    }

    private function recursiveMerger(): callable
    {
        return is_callable($this->recursiveMerger) ? $this->recursiveMerger : throw new \RuntimeException('Recursive merger is not configured for tab service.');
    }

    protected function arrayValueReader(): callable
    {
        return is_callable($this->arrayValueReader) ? $this->arrayValueReader : throw new \RuntimeException('Array value reader is not configured for tab service.');
    }

    private function arrayLikeChecker(): callable
    {
        return is_callable($this->arrayLikeChecker) ? $this->arrayLikeChecker : throw new \RuntimeException('Array-like checker is not configured for tab service.');
    }

    private function shortClassNameResolver(): callable
    {
        return is_callable($this->shortClassNameResolver) ? $this->shortClassNameResolver : throw new \RuntimeException('Short class name resolver is not configured for tab service.');
    }

    private function templateService(): object
    {
        if ($this->templateFactory !== null) {
            try {
                $template = ($this->templateFactory)();
                if (is_object($template)) {
                    return $template;
                }
            } catch (\RuntimeException $exception) {
                if ($exception->getMessage() !== 'Template service has been removed.') {
                    throw $exception;
                }
            }
        }

        return new twig_template_service();
    }

    private function errorService(): object
    {
        return $this->errorFactory !== null ? ($this->errorFactory)() : throw new \RuntimeException('Error service is not configured for tab service.');
    }

    private function logService(): object
    {
        return $this->logFactory !== null ? ($this->logFactory)() : throw new \RuntimeException('Log service is not configured for tab service.');
    }

    private function cookieService(): object
    {
        return $this->cookieFactory !== null ? ($this->cookieFactory)() : throw new \RuntimeException('Cookie service is not configured for tab service.');
    }

    private function reflectorService(): object
    {
        return $this->reflectorFactory !== null ? ($this->reflectorFactory)() : throw new \RuntimeException('Reflector service is not configured for tab service.');
    }

    private function tabFactoryService(string $factoryKey, string $serviceName, mixed ...$arguments): object
    {
        return $this->$factoryKey !== null ?
            ($this->$factoryKey)(...$arguments) :
            throw new \RuntimeException($serviceName . ' service is not configured for tab service.');
    }

    public function checkBlockStatus(base $block): array
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
                    if (!$this->roleService()->check($v['condition'])) {
                        $transfer = $v;
                        break 2;
                    }
                }
                return true;
            } while (false);

            if ($allowTransfer) {
                $servSes = $this->sessionService();
                $expire_URL = $servSes->isExpired() && !$this->getTabMeta('notRedirectByExpire', false) ? $this->config['EXPIRE_URL'] : null;

                if ($expire_URL) {
                    $this->transferService()->out($expire_URL, null, $dbOper);
                } elseif (!empty($transfer['transfer_sham'])) {
                    $this->transferService()->sham($this->getURI($transfer['transfer_sham']), null, $dbOper);
                } elseif (!empty($transfer['transfer_int'])) {
                    $this->transferService()->int($this->getURI($transfer['transfer_int']), null, $dbOper);
                } elseif (!empty($transfer['transfer_out'])) {
                    $this->transferService()->out($this->getURI($transfer['transfer_out']), null, $dbOper);
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
        $loader = $this->runtimeService()->getLoader();
        return $loader->loadBlockByPath($path);
    }

    public function setCurrentBlock(base $block): static
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
    public function setTabBlock(base $block, string $name): static
    {
        if (isset($this->blocks[$name])) {
            throw $this->createServiceFatalException('Set dublicate of block with name "' . $name . '"');
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
                throw $this->createServiceFatalException('Call undefined block with name "' . $blockName . '"');
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

    public function createServiceFatalExceptionForSubObject(string $message, int $code = E_USER_ERROR, ?\Throwable $previous = null): \Throwable
    {
        return $this->createServiceFatalException($message, $code, $previous);
    }

    public function isDebugAllowed(): bool
    {
        if (is_null($this->allowDebug)) {
            $debugConfig      = $this->configService()->get('debug');
            $serverAddr = $this->inputService()->serverValue('SERVER_ADDR', '');
            $this->allowDebug = $debugConfig['ENABLED'] && $debugConfig['DEBUG_IP'] && !empty($serverAddr) && preg_match($debugConfig['DEBUG_IP'], (string)$serverAddr);
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
     * @throws \fan\project\exception\service\fatal
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
            } catch (base_transfer $e) {
                // Catch and make transfer
                $transferType = $e->getTransferType();

                $modify = $transferType === 'out' ? null : false;
                $uri = $this->getURI($e->getRequest(), 'link', $modify, $modify);

                // Out transfer
                if ($transferType === 'out') {
                    $this->headerService()->sendLocation($uri);
                }

                $this->matcher->setUri($uri, $e->getHost(), $e->isShiftCurrent());
            }
        } while ($this->matcher->getLastIndex() < $maxQttTransfer);

        // Exception if quantity of Transfer is more Max
        $trList = '';
        foreach ($this->matcher->getStack() as $v) {
            $trList .= "\n" . $v['source'];
        }
        throw $this->createServiceFatalException('To many transfers: ' . $trList);
    }

    protected function _checkAlias(): static
    {
        if ($this->matcher->getCurrentIndex() === 0) {
            $reqData   = $this->matcher->getLastItem()->getParsedSrc();
            $aliasFile = (string)$this->runtimeService()->parsePath((string)$this->getConfig('ALIAS_FILE_PATH', '{PROJECT}/data/url_alias.php'));
            if (!empty($reqData) && $this->aliasFileStorage()->isReadable($aliasFile)) {
                $aliasData = $this->phpArrayFileLoader()($aliasFile, []);
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
                                throw $this->createServiceFatalException('Alias transfer type has incorrect value "' . $type . '" for request "' . $reqPath . '".');
                            }
                            if (empty($destPath)) {
                                throw $this->createServiceFatalException('Alias transfer doesn\'t have path for "' . $reqPath . '".');
                            }
                            if (!is_string($destPath)) {
                                throw $this->createServiceFatalException('Alias transfer has icorrect path for "' . $reqPath . '".');
                            }

                            if (substr($destPath, -1) !== '/') {
                                $destPath = $this->_getPathWithExt($destPath);
                            }
                            $transferFunction = 'transfer_' . $type;
                            $transferFunction($destPath);
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
        $this->appName     = $this->applicationService()->getAppName();
        $this->currentData = $this->matcher->getCurrentParsedData();
        $this->lastData    = $this->matcher->getLastParsedData();
        $this->timesStamp  = [];
        $this->checkPerformance = $this->readBooleanFlag($this->getConfig('CHECK_PERFORMANCE', 0), 'CHECK_PERFORMANCE');
        return $this;
    }

    public function _parseError(): static
    {
        $mainRequest = $this->lastData['main_request'];
        $state = $this->tabState();
        if (empty($mainRequest) && ($state->isErrorTransferEmpty() || $state->lastErrorTransferCode() === 404)) {
            $transferor = $this->getConfig('transferor');
            if (!empty($transferor)) {
                if (!is_array($transferor)) {
                    throw $this->createServiceFatalException('Point transferor isn\'t array.');
                }
                foreach ($transferor as $class) {
                    $class::checkRequest($this->lastData['src_path'], $this->lastData['app_prefix']);
                }
            }
            $this->content = $this->_parseError404(false);
            return $this;
        }

        if (!empty($mainRequest)) {
            $file = $this->lastData['file'];
            if (!empty($file)) {
                $loader = $this->runtimeService()->getLoader();
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
            throw $this->createServiceFatalException('Type of view can\'t be empty.');
        }
        $class = '\fan\project\view\parser\\' . $viewClass;
        if (!$this->viewClassExists($class)) {
            throw $this->createServiceFatalException('Class "' . $class . '" isn\'t found. Please check your "View definer"');
        }
        $this->viewClass = $class;
        return $this;
    }

    protected function viewClassExists(string $className): bool
    {
        $this->viewClassExists ??= static fn(string $viewClassName): bool => class_exists($viewClassName, true);

        return ($this->viewClassExists)($className);
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

    protected function _getFinalContent(base $rootBlock): mixed
    {
        $debugMode = $this->_getDebugMode();
        if ($debugMode > 0) {
            $class = '\fan\project\view\parser\\' . ($debugMode === 1 && $this->mainBlock->getViewFormat() === 'html' ? 'debug1' : 'debug2');
        } else {
            $class = (string)$this->getViewClass();
            if ($this->isDebugAllowed()) {
                $debug = $this->debugService();
                /* @var $debug \fan\core\service\debug */
                $debug->setExtFiles($rootBlock, false);
            }
        }
        /* @var $viewParser \fan\core\view\parser */
        $jsonFactory = fn(bool $useBase64 = false): mixed => $this->jsonService($useBase64);
        $templateFactory = fn(string $template, mixed $tplParentClass, base $block): mixed =>
            $this->templateService()->get($template, $tplParentClass, $block);
        $dataLoaderFactory = fn(): object => $this->dataLoaderService();
        $header = $this->headerService();
        $locale = $this->localeService();
        $viewParser = $this->createViewParser(
            $class,
            $debugMode,
            $this->mainBlock,
            $jsonFactory,
            $debugMode > 0 ? $this->debugService() : null,
            $templateFactory,
            $header,
            $locale,
            $dataLoaderFactory
        );
        $viewParser->startParsing($rootBlock);
        return $viewParser->getFinalContent();
    }

    private function createViewParser(
        string $className,
        int $debugMode,
        base $mainBlock,
        callable $jsonFactory,
        ?object $debug,
        callable $templateFactory,
        object $header,
        object $locale,
        callable $dataLoaderFactory
    ): parser
    {
        if (!is_callable($this->viewParserFactory)) {
            throw new \RuntimeException('Tab view parser factory is not configured for tab service.');
        }

        $viewParser = ($this->viewParserFactory)(
            $className,
            $debugMode,
            $mainBlock,
            $jsonFactory,
            $debug,
            $templateFactory,
            $header,
            $locale,
            $dataLoaderFactory
        );
        if (!$viewParser instanceof parser) {
            throw new \UnexpectedValueException('Tab view parser factory must return a view parser object.');
        }

        return $viewParser;
    }

    protected function _defineRootBlock(array $rootMeta): ?object
    {
        $defaultMeta = $this->getDefaultMeta();
        $arrayValueReader = $this->arrayValueReader();
        $rootPath = $this->getTabMeta(
                'root',
                $arrayValueReader($defaultMeta, ['main', 'root'])
        );
        $carcassPath = $this->getTabMeta(
                'carcass',
                $arrayValueReader($defaultMeta, ['main', 'carcass'])
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

        $rootBlock = $this->createBlock($rootClass, $blockName, null, $rootMeta, true);
        return $rootBlock;
    }

    protected function _getRootMeta(): array
    {
        $defaultMeta = $this->getDefaultMeta();
        if (!empty($defaultMeta) && is_object($defaultMeta)) {
            $defaultMeta = method_exists($defaultMeta, 'toArray') ? $defaultMeta->toArray() : [];
        }

        $locale = $this->localeService();
        if ($locale->isEnabled() && empty($defaultMeta['common']['tplVars']['sLng'])) {
            $defaultMeta['common']['tplVars']['sLng'] = $locale->getLanguage();
        }

        $arrayValueReader = $this->arrayValueReader();
        return [
            'own'    => $arrayValueReader($defaultMeta, 'root',   []),
            'common' => $arrayValueReader($defaultMeta, 'common', []),
        ];
    }

    protected function _getInitBlocks(): array
    {
        ksort($this->initOrder);
        return $this->initOrder;
    }

    protected function _parseError403(): mixed
    {
        $this->headerService()->error403(false);
        $state = $this->tabState();
        if (!$state->hasErrorTransferCode(403)) {
            $state->pushErrorTransferCode(403);
            $urn = $this->getConfig('error_403', self::URN_AP . '/error403');
            $this->transferService()->sham($this->_getPathWithExt($urn));
        }

        $firstItem = $this->matcher->getItem(0);
        $runner    = $this->runtimeService()->getRunner();
        return $runner->showError(['urn', $firstItem['source']['request']], 'error_403', false);
    }

    protected function _parseError404(bool $forse): mixed
    {
        $this->headerService()->error404(false);
        $state = $this->tabState();
        if (empty($forse) && $state->isErrorTransferEmpty()) {
            $state->pushErrorTransferCode(404);
            $urn = $this->getConfig('error_404', self::URN_AP . '/error404');
            $this->transferService()->sham($this->_getPathWithExt($urn));
        }

        $firstItem = $this->matcher->getItem(0);
        $this->runtimeService()->logError(var_export($firstItem->parsed->toArray(), true));

        $runner = $this->runtimeService()->getRunner();
        return $runner->showError(['urn', $firstItem['source']['request']], 'error_404', false);
    }

    protected function _parseError500(?string $errorLog = null): mixed
    {
        if (!empty($errorLog)) {
            $this->errorService()->logErrorMessage($errorLog, 'Tab Error');
        }
        $this->headerService()->error500(false);
        $state = $this->tabState();
        if (!$state->hasErrorTransferCode(500)) {
            $state->pushErrorTransferCode(500);
            $urn = $this->getConfig('error_500', self::URN_AP . '/error500');
            $this->transferService()->sham($this->_getPathWithExt($urn));
        }

        $runner = $this->runtimeService()->getRunner();
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

    private function tabState(): object
    {
        if ($this->tabState === null) {
            throw new \RuntimeException('Tab state service is not configured for tab service.');
        }

        return $this->tabState;
    }

    protected function _setMainBlock(): static
    {
        $mainRequest = $this->lastData['main_request'];
        $class = $this->runtimeService()->getLoader()->loadBlockByMR($this->appName, $mainRequest);
        if (empty($class)) { // ToDo: check it!
            $this->content = $this->_parseError500('Main class for Main Request "' . implode('/', $mainRequest) . '" isn\'t found.');
        } else {
            $this->mainBlock = $this->createBlock($class, 'main', null, [], false);
        }
        return $this;
    }

    private function createBlock(
        string $className,
        string $blockName,
        ?base $container,
        array $meta,
        bool $allowMeta
    ): base
    {
        if (!is_callable($this->blockFactory)) {
            throw new \RuntimeException('Tab block factory is not configured for tab service.');
        }

        $block = ($this->blockFactory)(
            $className,
            $blockName,
            $this,
            $container,
            $meta,
            $allowMeta,
            null,
            $this->getBlockDependencies()
        );
        if (!$block instanceof base) {
            throw new \UnexpectedValueException('Tab block factory must return a block object.');
        }

        return $block;
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
        if ($value instanceof data) {
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
            $sr    = $this->requestService();
            $sc    = $this->cookieService();
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

            if ($this->logFactory !== null) {
                $this->logService()->logMessage(
                    'custom',
                    '<pre style="font-family: Courier, monospace">' . htmlentities(var_export($this->timesStamp, true), ENT_NOQUOTES, 'UTF-8') . '</pre>',
                    'Estimate Performance by elements'
                );
            }
        }
    }

    // ======== The magic methods ======== \\

}
