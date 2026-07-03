<?php
declare(strict_types=1);

namespace fan\core\service;
use fan\core\base\service\multi;
use fan\core\service\curl;


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
class rest extends multi
{
    private ?string $connectionName = null;

    private ?string $errorMessage = null;

    private ?\Closure $jsonFactory = null;

    private ?\Closure $curlFactory = null;

    private ?\Closure $errorFactory = null;

    public function __construct(
        ?string $connectionName,
        ?callable $jsonFactory = null,
        ?callable $curlFactory = null,
        ?callable $errorFactory = null,
        ?object $serviceBootstrapRuntime = null,
        ?object $serviceConfigurator = null,
        ?callable $serviceCacheFactory = null
    )
    {
        $this->jsonFactory = \Closure::fromCallable(
            $jsonFactory ?? static function (): object {
                throw new \RuntimeException('JSON dependency is not configured for REST service.');
            }
        );
        $this->curlFactory = \Closure::fromCallable(
            $curlFactory ?? static function (string $url): curl {
                throw new \RuntimeException('CURL dependency is not configured for REST service.');
            }
        );
        $this->errorFactory = \Closure::fromCallable(
            $errorFactory ?? static function (): object {
                throw new \RuntimeException('Error dependency is not configured for REST service.');
            }
        );
        parent::__construct(false, $serviceBootstrapRuntime, $serviceConfigurator, $serviceCacheFactory);

        if (empty($connectionName)) {
            $connectionName = (string)$this->config['DEFAULT_CONNECTION'];
        }
        if (!isset($this->config['CONNECTION'][$connectionName])) {
            $this->errorMessage = 'Undefind connection name: ' . $connectionName;
            throw $this->createServiceFatalException('Undefined connection name <b>' . $connectionName . '</b>');
        }

        $this->connectionName = (string)$connectionName;

    }

    public function __destruct() {
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
            $post = $this->json()->encode($data);
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

    protected function _getCurl(string $urlSuffix): curl
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
        return $this->curl($url);
    }

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _getResponse(curl $curl, mixed $post = null): mixed
    {
        $url      = $curl->getInfo(CURLINFO_EFFECTIVE_URL);
        $response = $curl->exec($post);
        $curl->close();

        $json = $this->json();
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
            $this->error()->logErrorMessage($errMsg, 'REST response error', htmlentities((string)$response));
            throw $this->createServiceFatalException('Illegal response for REST "' . $url . '".');
        }
        return $decoded;
    }

    private function json(): object
    {
        if (!isset($this->jsonFactory)) {
            $this->jsonFactory = \Closure::fromCallable(
                static function (): object {
                    throw new \RuntimeException('JSON dependency is not configured for REST service.');
                }
            );
        }

        $json = ($this->jsonFactory)();
        if (!is_object($json)) {
            throw new \UnexpectedValueException('JSON dependency must be an object.');
        }

        return $json;
    }

    private function curl(string $url): curl
    {
        if (!isset($this->curlFactory)) {
            $this->curlFactory = \Closure::fromCallable(
                static function (string $url): curl {
                    throw new \RuntimeException('CURL dependency is not configured for REST service.');
                }
            );
        }

        $curl = ($this->curlFactory)($url);
        if (!$curl instanceof curl) {
            throw new \UnexpectedValueException('CURL dependency must be an instance of ' . curl::class . '.');
        }

        return $curl;
    }

    private function error(): object
    {
        if (!isset($this->errorFactory)) {
            $this->errorFactory = \Closure::fromCallable(
                static function (): object {
                    throw new \RuntimeException('Error dependency is not configured for REST service.');
                }
            );
        }

        $error = ($this->errorFactory)();
        if (!is_object($error)) {
            throw new \UnexpectedValueException('Error dependency must be an object.');
        }

        return $error;
    }

}
