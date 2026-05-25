<?php
declare(strict_types=1);

namespace fan\core\view\router;
use fan\core\block\base;
use fan\core\view\keeper;
use fan\core\view\keeper\loader\json as loader_json;
use fan\core\view\keeper\loader\text;
use fan\core\view\router;

/**
 * View router of Block for Loader-type
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
 *
 *
 * IMPORTANT: this view has common area for text and json data, but independed for html
 */
class loader extends router
{
    private loader_state $loaderState;

    /**
     * Routers array
     * @var array
     */
    protected array $keepers = [
        'json' => null,
        'html' => null,
        'text' => null,
    ];
    /**
     * Default Routers Key
     * @var string
     */
    protected ?string $defaultKey = 'html';

    public function __construct(
        base $block,
        ?loader_state $loaderState = null,
        ?callable $keeperFactory = null,
        ?callable $blockExceptionFactory = null,
        ?callable $arrayAdducer = null
    )
    {
        $this->loaderState = $loaderState ?? throw new \RuntimeException('Loader state is not configured for loader view router.');

        parent::__construct($block, $keeperFactory, $blockExceptionFactory, $arrayAdducer);
    }

    // ======== Main Interface methods ======== \\
    public function set(mixed $key, mixed $value): static
    {
        if ((string)$key === 'text') {
            $this->_getTextKeeper()->set($key, $value);
        } else {
            parent::set($key, $value);
        }
        return $this;
    }

    public function getJson(string|array|null $key = null, mixed $default = null, bool $logError = true): mixed
    {
        return $this->_getJsonKeeper()->get($key, $default, $logError);
    }

    public function setJson(string|int|float $key, mixed $value, bool $rewriteExisting = true): static
    {
        $this->_getJsonKeeper()->set($key, $value, $rewriteExisting, false);
        return $this;
    }
    public function getText(): string
    {
        return $this->_getTextKeeper()->__toString();
    }

    public function setText(mixed $value, int $position = 1): static
    {
        $this->_getTextKeeper()->set($position, $value);
        return $this;
    }

    public function isFullRewrite(keeper $keeper): bool
    {
        foreach ($this->keepers as $k => $v) {
            if ($v === $keeper) {
                return (string)$k !== 'html';
            }
        }
        return false;
    }

    // ======== Private/Protected methods ======== \\
    protected function _getJsonKeeper(): loader_json
    {
        return $this->loaderState->jsonKeeper($this);
    }

    protected function _getTextKeeper(): text
    {
        return $this->loaderState->textKeeper($this);
    }
}
