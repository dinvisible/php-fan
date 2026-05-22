<?php

declare(strict_types=1);

namespace fan\core\service\email;
/**
 * PHPMailer engine
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
 * @version of file: 05.02.006 (20.04.2015)
 */
class phpmailer
{
    protected ?object $facade = null;

    /**
     * Main PHPMailer adapter.
     *
     * @var \fan\core\adapter\php_mailer
     */
    protected ?object $mail = null;

    public function __construct()
    {
        $this->mail = new \fan\project\adapter\php_mailer(false); //ToDo: Use Mailer Exception there

        $this->mail->Debugoutput = 'error_log';
        $this->mail->SetLanguage('ru'); //ToDo: Get Language from config
    }

    public function setFacade(\fan\core\service\email $facade): static
    {
        if (empty($this->facade)) {
            $this->facade = $facade;

            $config = $facade->getConfig();
            switch ($config->MAILER) {
                case 'SMTP':
                    $this->mail->IsSMTP();
                    break;
                case 'MAIL':
                    $this->mail->IsMail();
                    break;
                default:
                    $this->mail->IsSendmail();
            }

            $user = $config->SMTP_USER;
            if (!empty($user)) {
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $user;
                $this->mail->Password = $config->SMTP_PASSWORD;
            } // check SMTP_USER

            foreach ([
                'Host'     => 'SMTP_HOST',
                'Port'     => 'PORT',
                'CharSet'  => 'CHARSET',
                'AuthType' => 'AUTH_TYPE',
            ] as $k => $v) {
                if ((string)$config->$v !== '') {
                    $this->mail->$k = $config->$v;
                }
            }

            $this->mail->SMTPDebug = $config->get('DEBUG', false);
        }

        return $this;
    }

    public function setFrom(string $emailFrom, string $nameFrom = ''): void
    {
        $this->mail->From = $emailFrom;
        if ($nameFrom) {
            $this->mail->FromName = $nameFrom;
        }
    }

    public function send(string $subj, string $body, string $emailTo, string $nameTo = '', bool $isHtml = false): bool
    {
        $this->mail->AddAddress($emailTo, $nameTo);
        $this->mail->Subject  = trim($subj);
        $this->mail->Body     = $body;
        $this->mail->IsHTML($isHtml);
        $ret = $this->mail->send();
        if (!empty($this->mail->ErrorInfo)) {
            $ret = false;
        }
        if (!$ret) {
            $this->facade->getContainerService('error')->logErrorMessage(
                    empty($this->mail->ErrorInfo) ? 'Unknown error' : $this->mail->ErrorInfo,
                    'EMAIL error',
                    $nameTo . ' &lt;' . $emailTo . '&gt;',
                    true,
                    false
            );
        }
        $this->mail->ClearAddresses();
        $this->mail->ClearAttachments();
        return $ret;
    }

    public function __call(string $method, array $args): mixed
    {
        $method = (string)$method;
        if (method_exists($this->mail, $method)) {
            return call_user_func_array([$this->mail, $method], empty($args) ? [] : (array)$args);
        }
        return null;
    }
}
