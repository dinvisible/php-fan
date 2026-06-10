<?php
declare(strict_types=1);

namespace fan\core\view\parser;
use fan\core\block\base;
use fan\core\view\parser;
use fan\core\view\router\loader as router_loader;
use fan\core\view\router\loader_state;

/**
 * View parser Loader-type
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class loader extends parser
{
    protected mixed $dataLoaderFactory = null;

    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory = null,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null,
        ?callable $dataLoaderFactory = null
    ) {
        parent::__construct($mainBlock, $jsonFactory, $templateFactory, $header, $locale);
        $this->dataLoaderFactory = $dataLoaderFactory;
    }

    // ======== Static methods ======== \\
    final static public function getFormat(?callable $exceptionFactory = null): string {
        return 'loader';
    }

    static public function getRouter(
        base $block,
        mixed $loaderStateOrFactory = null,
        ?callable $viewRouterFactory = null
    ): router_loader {
        $loaderState = $loaderStateOrFactory;
        if ($loaderState === null) {
            throw new \RuntimeException('Loader state is not configured for loader view parser.');
        }
        if (!$loaderState instanceof loader_state) {
            throw new \InvalidArgumentException('Loader view parser router requires a loader state.');
        }

        $factory = static::viewRouterFactory($viewRouterFactory);
        $router = $factory(static::class, $block, $loaderState);
        if (!$router instanceof router_loader) {
            throw new \UnexpectedValueException('View router factory must return a loader view router.');
        }

        return $router;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function getFinalContent(): mixed
    {
        if (method_exists($this->mainBlock, 'getDataLoader')) {
            $loader = $this->mainBlock->getDataLoader();
        } else {
            $loader = $this->createDataLoader();
        }
        $loader->setJson($this->result['json'], true);
        $loader->setText($this->result['text'], true);
        $loader->setHtml($this->result['html'], true);
        $result = $loader->send(false);

        $this->_setHeaders($result, $loader->getContentType([], false, false), false);
        return $result;
    }

    protected function createDataLoader(): object
    {
        if (is_callable($this->dataLoaderFactory)) {
            return ($this->dataLoaderFactory)();
        }

        throw new \RuntimeException('Data loader factory is not configured for loader view parser.');
    }

    public function getResultData(base $block): array
    {
        $viewRouter = $block->getView();
        $tplResult  = $this->_getTplResult($block);
        return [
            'json' => $viewRouter->getJson(),
            'html' => end($tplResult),
            'text' => $viewRouter->getText(),
        ];
    }

    // ======== Protected methods ======== \\
    public function _getTplResult(base $block): array
    {
        $tplVar = $block->getView()->html->toArray();

        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $tmp = $this->_getTplResult($embeddedBlock);
            $tplVar[key($tmp)] = reset($tmp);
        }

        return [$block->getBlockName() => $this->_parseTemplate($block, $tplVar)];
    }
}
