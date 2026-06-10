<?php
declare(strict_types=1);

namespace fan\core\service\matcher\item;
/**
 * Description of parsed
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
 * @property string $app_name
 * @property string $app_prefix
 * @property string $language
 * @property string $src_path
 * @property string $query
 * @property array $main_request
 * @property array $add_request
 * @property array $both_request
 * @property string $class
 * @property string $file
 * @property string $urn
 */
class parsed extends base
{
    /**
     * Allowed property
     * @var array
     */
    protected array $data = [
        'app_name'     => null,
        'app_prefix'   => null,
        'language'     => null,
        'src_path'     => null,
        'query'        => null,
        'main_request' => null,
        'add_request'  => null,
        'both_request' => null,
        'class'        => null,
        'file'         => null,
        'urn'          => null,
    ];

    // ======== Static methods ======== \\
    // ======== The magic methods ======== \\

    public function __toString(): string {
        return (string)$this->data['urn'];
    }
    // ======== Required Interface methods ======== \\
    // ======== Main Interface methods ======== \\

    public function getMainRequest(): array
    {
        if (is_null($this->data['main_request'])) {
            $this->item->parseRequest();
        }
        return $this->data['main_request'];
    }

    public function getAddRequest(): array
    {
        if (is_null($this->data['add_request'])) {
            $this->item->parseRequest();
        }
        return $this->data['add_request'];
    }

    public function getBothRequest(): array
    {
        if (is_null($this->data['both_request'])) {
            $this->data['both_request'] = array_merge($this['main_request'], $this['add_request']);
        }
        return $this->data['both_request'];
    }

    public function getClass(): string
    {
        if (is_null($this->data['class'])) {
            $mainRequest = $this['main_request'];
            if (empty($mainRequest)) {
                $this->data['class'] = '';
            } else {
                $this->data['class']  = '\\fan\\app\\' . $this->data['app_name'];
                $this->data['class'] .= '\\' . $this->_getConfig('main_block_dir', 'main') . '\\';
                $this->data['class'] .= implode('\\', $mainRequest);
            }
        }
        return $this->data['class'];
    }

    public function getFile(): string
    {
        if (is_null($this->data['file'])) {
            $mainRequest = $this['main_request'];
            if (empty($mainRequest)) {
                $this->data['file'] = '';
            } else {
                $this->data['file'] = $this->item->getMainBlockBasePath();
                $this->data['file'] .= '/' . implode('/', $mainRequest) . '.php';
            }
        }
        return $this->data['file'];
    }

    public function getUrn(): string
    {
        $urn =& $this->data['urn'];
        if (is_null($urn)) {

            $urn  = '/';
            // ToDo: Possibility to switch positions "language" and "app_prefix"
            if (!empty($this->data['language'])) {
                $urn  .= $this->data['language'] . '/';
            }
            if (!empty($this->data['app_prefix'])) {
                $urn  .= trim($this->data['app_prefix'], '/') . '/';
            }

            $urn  .= implode('/', (array)$this->data['both_request']);
        }
        return $urn;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    // ======== Private/Protected methods ======== \\

}
