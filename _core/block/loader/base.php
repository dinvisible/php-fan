<?php

declare(strict_types=1);

namespace fan\core\block\loader;
/**
 * Base abstract loader block
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
 * @abstract
 */
abstract class base extends \fan\core\block\base
{
    private array $getData = [];
    private bool $isGetData = false;

    /**
     * @var \fan\core\adapter\data_loader
     */
    private ?object $loader = null;

    public function finishConstruct(?\fan\core\block\base $container = null, array $containerMeta = [], bool $allowSetEmbedded = true): void
    {
        parent::finishConstruct($container, $containerMeta, $allowSetEmbedded);

        $json = $this->getMeta('json');
        if (!empty($json)) {
            $this->setJson($json);
        }

        $text = $this->getMeta('text');
        if (!empty($text)) {
            $this->setText($text);
        }

    }

    public function getData(): array
    {
        if (!$this->isGetData) {
            $engine = $this->getDataLoader();
            if ($engine) {
                $this->getData   = (array)$engine->getData();
                $this->isGetData = true;
            }
        }
        return $this->getData;
    }

    public function getDataLoader(): object
    {
        if (!$this->loader) {
            $this->loader = new \fan\project\adapter\data_loader();
        }
        return $this->loader;
    }

    public function setJson(mixed $json, bool $merge = true): static
    {
        $json = adduceToArray($json);
        if ($merge) {
            $json = array_merge_recursive_alt(adduceToArray($this->view->json), $json);
        }
        $this->view->json = $json;
        return $this;
    }

    public function setHtml(mixed $html, bool $merge = true): static
    {
        $this->view->html = $merge ? (string)$this->view->html . (string)$html : (string)$html;
        return $this;
    }

    public function setText(string $text, bool $merge = true): static
    {
        $this->view->text = $merge ? (string)$this->view->text . $text : $text;
        return $this;
    }

    public function checkRunInit(): bool
    {
        return true;
    }

    public function getOutcome(): array
    {
        return $this->view->toArray();
    }
}
