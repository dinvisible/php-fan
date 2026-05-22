<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
/**
 * REST-client service
 *
 * This file is part PHP-FAN (php-framework of Alexandr Nosov)
 * Copyright (C) 2005-2007 Alexandr Nosov, http://www.alex.4n.com.ua/
 *
 * Licensed under the terms of the GNU Lesser General Public License:
 *     http://www.opensource.org/licenses/lgpl-license.php
 *
 * Do not remove this comment if you want to use script!
 * Не удаляйте данный комментарий, если вы хотите использовать скрипт!
 *
 * @author: Alexandr Nosov (alex@4n.com.ua)
 * @version of file: 05.02.005 (12.02.2015)
 */
class rest extends \fan\core\base\service\multi
{
    /**
     * @var \fan\core\service\rest[] Service's Instances
     */
    private static ?array $instances = null;

    private static ?string $defaultName = null;

    private ?string $connectionName = null;

    protected function __construct(?string $connectionName)
    {
        parent::__construct(false);

        if (empty(self::$defaultName)) {
            self::$defaultName = (string)$this->config['DEFAULT_CONNECTION'];
        }
        if (empty($connectionName)) {
            $connectionName = self::$defaultName;
        }
        if (!isset($this->config['CONNECTION'][$connectionName])) {
            $this->errorMessage = 'Undefind connection name: ' . $connectionName;
            throw new fatalException($this, 'Undefined connection name <b>' . $connectionName . '</b>');
        }

        $this->connectionName = (string)$connectionName;

        self::$instances[$this->connectionName] = $this;
    }

    public function __destruct() {
    }

    public static function instance(?string $connectionName = NULL): self
    {
        if (empty($connectionName)) {
            $connectionName = self::$defaultName;
        }
        if (!isset(self::$instances[$connectionName])) {
            $className = __CLASS__;
            new $className($connectionName);
        }
        if (empty($connectionName)) {
            $connectionName = self::$defaultName;
        }

        return self::$instances[$connectionName];
    }

    public function get(string $urlSuffix, mixed $data = null): mixed
    {
        if (!empty($data)) {
            if (is_array($data)) {
                $urlSuffix .= '?' . http_build_query($data, '', '&');
            } else {
                $urlSuffix .= '/' . urlencode((string)$data);
            }
        }
        $curl = $this->_getCurl($urlSuffix);

        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);

        return $this->_getResponse($curl);
    }

    public function post(string $urlSuffix, mixed $data, mixed $format = 'json'): mixed
    {
        $curl = $this->_getCurl($urlSuffix);

        if ($format === 'json'){
            $curl->setHeaders(['Content-Type: application/json', 'charset=utf-8']);
            $post = $this->containerService('json')->encode($data);
        } else {
            $post = $data;
        }
        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        return $this->_getResponse($curl, $post);
    }

    public function delete(string $urlSuffix, string $data): mixed
    {
        $curl = $this->_getCurl($urlSuffix . '/' . $data );

        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOption(CURLOPT_CUSTOMREQUEST, 'DELETE');

        return $this->_getResponse($curl);
    }

    public function _callPutRequest(string $urlSuffix, string $data): mixed
    {
        $curl = $this->_getCurl($urlSuffix . '/' . $data );

        $curl->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOption(CURLOPT_CUSTOMREQUEST, 'PUT');
        $payCode = ['pay_code' => $data];
        $curl->setOption(CURLOPT_POSTFIELDS, http_build_query($payCode));

        return $this->_getResponse($curl);
    }


    public function getConnectionName(): ?string {
        return $this->connectionName;
    }

    protected function _getCurl(string $urlSuffix): \fan\core\service\curl
    {
        $conf = $this->getConfig(['CONNECTION', $this->connectionName, 'url']);
        $url  = $conf['server'] . '/' . $conf['request'];
        if (!empty($conf['user'])) {
            $url = $conf['user'] . ':' . $conf['pass'] . '@' . $url;
        }
        $url = (empty($conf['proyocol']) ? 'http' : $conf['proyocol']) . '://' . $url;
        if (!empty($urlSuffix)) {
            $url .= '/' . $urlSuffix;
        }
        return service('curl', $url);
    }

    /**
     * @throws fatalException
     */
    protected function _getResponse(\fan\core\service\curl $curl, mixed $post = null): mixed
    {
        $url      = $curl->getInfo(CURLINFO_EFFECTIVE_URL);
        $response = $curl->exec($post);
        $curl->close();

        $json = $this->containerService('json');
        /* @var $json \fan\core\service\json */
        $decoded  = $json->decode((string)$response, true);
        if ($json->getError() > 0) {
            if (is_string($post)) {
                $post = preg_replace('/\"Password\"\:\"[^"]+?\"/', '"Password":"*******"', $post);
            } elseif (isset($post['Password'])) {
                $post['Password'] = '*******';
            }
            $errMsg  = $json->getErrorText();
            $errMsg .= '<br /><br />URL: ' . $url .'<br />Request:<br /><pre>' . (is_string($post) ? $post : var_export($post, true)) . '</pre>';
            $this->containerService('error')->logErrorMessage($errMsg, 'REST response error', htmlentities((string)$response));
            throw new fatalException($this, 'Illegal response for REST "' . $url . '".');
        }
        return $decoded;
    }

}
