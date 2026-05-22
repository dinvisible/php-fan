<?php

declare(strict_types=1);

namespace fan\core\service\entity\encapsulant;
/**
 * Description of encapsulant
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
class simple
{
    /**
     * Class of DB-table
     * @var fan\core\entity\table
     */
    protected ?object $service = null;

    public function __construct(\fan\core\service\entity $service)
    {
        $this->service = $service;
    }

    // ======== Static methods ======== \\

    // ======== Main Interface methods ======== \\
    public function encryptId(int|float $id): string
    {
        $code1 = '';
        if ($id) {
            $shift = rand(0, 30);
            $shift = $shift % 31;
            $criptKey = $this->_getCriptKey($this->_code2Symbol($shift));

            $code0 = str_pad((string)($id < 1000000000 ? $id * ($shift + 1) : ($id < 100000000000 ? $id + $shift : $id)), 20, '0', STR_PAD_LEFT);
            $code0 = $this->_getCheckSumId($code0, 11) . $code0;
            if ($shift) {
                $code0 = substr($code0, $shift) . substr($code0, 0, $shift);
            }
            for ($i = 0; $i < 31; $i++) {
                $n = $this->_symbol2Code($criptKey[$i]);
                $code1 .= $this->_code2Symbol(($n + (int)$code0[$i]) % 36);
            }
            $shiftPos = $this->_getShiftPos();
            $code1 = $shiftPos ? substr($code1, 0, $shiftPos) . $this->_code2Symbol($shift) . substr($code1, $shiftPos) : $this->_code2Symbol($shift) . $code1;
        }
        return $code1 ? $code1 : '0';
    }

    public function decryptId(string $code): int|float|string
    {
        if (strlen($code) !== 32) {
            return 0;
        }
        $shiftPos = $this->_getShiftPos();
        $shift = $this->_symbol2Code($code[$shiftPos]) % 31;
        $code1 = substr($code, 0, $shiftPos) . substr($code, $shiftPos + 1);
        $criptKey = $this->_getCriptKey($code[$shiftPos]);
        $code0 = '';
        for ($i = 0; $i < 31; $i++) {
            $n1 = $this->_symbol2Code($code1[$i]);
            $n2 = $this->_symbol2Code($criptKey[$i]);
            $code0 .= ($n1 >= $n2 ? $n1 - $n2 : $n1 + 36 - $n2);
        }
        if ($shift) {
            $code0 = substr($code0, -$shift) . substr($code0, 0, -$shift);
        }

        $code2 = substr($code0, 11);
        if ($this->_getCheckSumId($code2, 11) === substr($code0, 0, 11)) {
            while (strlen($code2) > 0 && $code2[0] === '0') {
                $code2 = substr($code2, 1);
            }
            return strlen($code2) <= 9 ? $code2 / ($shift + 1) : (strlen($code2) < 12 ? $code2 - $shift : $code2);
        } else {
            return 0;
        }
    }

    // ======== Private/Protected methods ======== \\
    protected function _code2Symbol(int $code): string
    {
        return $code > 25 ? chr($code + 22) : chr($code + 97);
    }
    protected function _symbol2Code(string $sym): int
    {
        $code = ord($sym);
        return $code > 96 ? $code - 97 : $code - 22;
    }

    protected function _getCriptKey(string $srt, int $len = 31): string
    {
        return substr(md5((string)$this->_getEncryptKey() . $srt), -$len);
    }
    protected function _getCheckSumId(int|string $id, int $len = 11): string
    {
        $cs = substr(sprintf('%u', crc32((string)$id . (string)$this->_getEncryptKey())), -$len);
        return str_pad($cs, $len, '0', STR_PAD_LEFT);
    }
    protected function _getShiftPos(int $len = 31): int
    {
        return sprintf('%u', crc32($this->_getEncryptKey())) % $len;
    }

    protected function _getEncryptKey(): string
    {
        return (string)$this->service->getConfig()->get('encrypt_id_key', '');
    }

}
