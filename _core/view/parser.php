<?php
declare(strict_types=1);

namespace fan\core\view;
use fan\core\block\base;
use fan\core\service\header;
use fan\core\view\router;

/**
 * Base abstract html type of block
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
 * @abstract
 */
abstract class parser
{
    /**
     * @var \fan\core\block\base Root block
     */
    protected ?object $rootBlock = null;
    /**
     * @var \fan\core\block\base Main block
     */
    protected ?object $mainBlock = null;

    /**
     * Array - result of parsing process
     * @var array
     */
    protected ?array $result = null;

    protected mixed $jsonFactory = null;

    protected mixed $templateFactory = null;

    protected ?object $headerService = null;

    protected ?object $localeService = null;

    public function __construct(
        base $mainBlock,
        ?callable $jsonFactory = null,
        ?callable $templateFactory = null,
        ?object $header = null,
        ?object $locale = null
    ) {
        $this->mainBlock = $mainBlock;
        $this->jsonFactory = $jsonFactory;
        $this->templateFactory = $templateFactory;
        $this->headerService = $header;
        $this->localeService = $locale;
    }

    // ======== Static methods ======== \\
    static public function getFormat(?callable $exceptionFactory = null): string {
        throw static::createUnsupportedParserException(
            'Class "' . get_called_class() . '" can\'t be use for define View-type',
            $exceptionFactory
        );
    }

    static public function getRouter(
        base $block,
        mixed $loaderStateOrFactory = null,
        ?callable $viewRouterFactory = null
    ): router {
        if (is_callable($loaderStateOrFactory) && $viewRouterFactory === null) {
            $viewRouterFactory = $loaderStateOrFactory;
        }
        $factory = static::viewRouterFactory($viewRouterFactory);
        $router = $factory(static::class, $block);
        if (!$router instanceof router) {
            throw new \UnexpectedValueException('View router factory must return a view router.');
        }

        return $router;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\
    public function startParsing(base $rootBlock): static
    {
        $this->rootBlock = $rootBlock;
        $this->result = $this->getResultData($this->rootBlock);
        return $this;
    }

    public function getFinalContent(): mixed
    {
        $result = end($this->result);
        $this->_setHeaders($result);
        return $result;
    }

    public function getResultData(base $block): array
    {
        return $this->_assembleToArray($block);
    }

    // ======== Protected methods ======== \\
    protected function _assembleToArray(base $block): array
    {
        $viewData = $block->getViewData();

        foreach ($block->getEmbeddedBlocks() as $embeddedBlock) {
            $embData = $this->getResultData($embeddedBlock);
            if (!empty($embData)) {
                $viewData[$embeddedBlock->getBlockName()] = $embData;
            }
        }

        return $viewData;
    }

    protected function _mixEmbededData(array $blockData, array $embededData): array
    {
        $mixedData = [];
        foreach ($embededData as $v) {
            $mixedData = array_merge($mixedData, $v);
        }
        return array_merge($mixedData, $blockData);
    }

    protected function _parseTemplate(base $block, $tplVar): string
    {
        $cond = $block->getRoleCondition();
        if (!empty($cond)) {
            return '';
        }
        $template = $block->getTemplate();
        if ($template) {
            // If template exists - assign variables and parse template
            $tplParentClass = $block->getMeta('tpl_parent_class');

            $template = $this->getTemplate($template, $tplParentClass, $block);
            foreach ($tplVar as $k => $v) {
                $template->assign($k, $v);
            }
            $tplResult = $template->fetch();
        } else {
            // else - concatenate variables
            $tplResult = '';
            foreach ($tplVar as $v) {
                if (is_scalar($v) || is_object($v) && method_exists($v, '__toString')) {
                    $tplResult .= (string)$v;
                }
            }
        }

        return $tplResult;
    }

    protected function _formatResultData(base $block, array $srcData, string $tplResult): array
    {
        return [$block->getBlockName() => $tplResult];
    }

    protected function _setHeaders($result, $contentType = 'text/plain', $encoding = null): header
    {
        $header = $this->getHeader();
        $header->addHeader('length', strlen((string)$result));

        if (!empty($contentType)) {
            if (is_null($encoding)) {
                $encoding = $this->getLocale()->getCharacterSet();
            }
            $header->addHeader('contentType', (string)$contentType);
            if (!empty($encoding)) {
                $header->addHeader('encoding', 'charset=' . $encoding);
            }
        }

         // ToDo: Add another header there. For example - cache headers
        return $header;
    }

    protected function getTemplate(string $template, mixed $tplParentClass, base $block): object
    {
        if (is_callable($this->templateFactory)) {
            return ($this->templateFactory)($template, $tplParentClass, $block);
        }

        throw new \RuntimeException('Template factory is not configured for view parser.');
    }

    protected function getHeader(): header
    {
        if ($this->headerService instanceof header) {
            return $this->headerService;
        }

        throw new \RuntimeException('Header service is not configured for view parser.');
    }

    protected function getLocale(): object
    {
        if (is_object($this->localeService)) {
            return $this->localeService;
        }

        throw new \RuntimeException('Locale service is not configured for view parser.');
    }

    protected function getJsonEncoder(bool $useBase64 = false): object
    {
        if (is_callable($this->jsonFactory)) {
            return ($this->jsonFactory)($useBase64);
        }

        throw new \RuntimeException('JSON encoder factory is not configured for view parser.');
    }

    protected static function viewRouterFactory(?callable $viewRouterFactory): callable
    {
        if (!is_callable($viewRouterFactory)) {
            throw new \RuntimeException('View router factory is not configured for view parser.');
        }

        return $viewRouterFactory;
    }

    protected static function createUnsupportedParserException(string $message, ?callable $exceptionFactory = null): \Throwable
    {
        if ($exceptionFactory === null) {
            throw new \RuntimeException('View parser exception factory is not configured.');
        }

        $exception = $exceptionFactory($message);
        if (!$exception instanceof \Throwable) {
            throw new \UnexpectedValueException('View parser exception factory must return a throwable.');
        }

        return $exception;
    }

}
