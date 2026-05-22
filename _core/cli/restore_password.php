<?php

declare(strict_types=1);

namespace fan\core\cli;
/**
 * Restore password CLI-tool
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
 */
class restore_password
{
    use \fan\core\di\container_aware_trait;

    /**
     * Service config
     * @var \fan\core\service\config
     */
    protected ?object $conf = null;

    public function init(): void
    {
        $this->conf = $this->containerService('config');
        $errDir = \bootstrap::parsePath((string)$this->conf->get('log', ['LOG_DIR', 'error']));
        $tmp    = filter_var(scandir($errDir) ?: [], FILTER_VALIDATE_REGEXP, [
            'flags'   => FILTER_FORCE_ARRAY,
            'options' => ['regexp' => '/.+\.log$/i']
        ]);
        $files = is_array($tmp) ? array_diff($tmp, [false]) : [];
        rsort($files);
        foreach ($files as $v) {
            $content = file($errDir . '/' . $v);
            if ($content === false) {
                continue;
            }
            for ($i = count($content) - 1; $i >= 0; $i--) {
                $data = explode("\t", $content[$i]);
                if (($data[1] ?? null) === 'custom' && $this->parceData((string)($data[2] ?? ''))) {
                    break 2;
                }
            }
        }
    }

    public function parceData(string $src): bool
    {
        $src = trim(str_replace('\n', "\n", $src));
        $inf = $this->unserializeLogRow($src);
        if (is_array($inf) && isset($inf['header']) && trim((string)$inf['header']) === 'Error authentication') {
            $hashe = null;
            $identifier = null;
            $ns = null;
            $matches = [];
            if (preg_match('/^[^\"]+\"([^\"]+)\"/', (string)($inf['main_msg'] ?? ''), $matches)) {
                $identifier = $matches[1];
            }
            if (preg_match('/^.+?\:\s*(\S+).+?\:\s*(\S+)/s', (string)($inf['note'] ?? ''), $matches)) {
                $hashe = $matches[1];
                $ns    = $matches[2];
            }
            if ($hashe === null || $identifier === null || $ns === null) {
                return false;
            }

            $userSpace = $this->conf->get('user', ['space', $ns]);
            if ((string)$userSpace['ENGINE'] === 'entity') {
                echo 'In DB-table of entity "' . $userSpace['ENGINE_KEY'] .
                        '" for "' .$identifier . '" set password=' . $hashe . "\n\n";
            } elseif ((string)$userSpace['ENGINE'] === 'config') {
                echo 'In the file "' . $userSpace['ENGINE_SOURCE'] . '.ini", section "' .
                        $userSpace['ENGINE_KEY'] . '" for "' .$identifier .
                        '" set password=' . $hashe . "\n\n";
            } else {
                return false;
            }
            return true;
        }
        return false;
    }

    private function unserializeLogRow(string $src): mixed
    {
        return \fan\core\adapter\safe_serializer::decodeExternalPayload(
            $src,
            false,
            static function (string $message): void {
                error_log('Cannot unserialize restore_password log row: ' . $message);
            }
        );
    }
}
