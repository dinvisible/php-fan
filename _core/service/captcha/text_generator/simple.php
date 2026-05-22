<?php
declare(strict_types=1);

namespace fan\core\service\captcha\text_generator;
/**
 * Siple text geterator for captcha
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
class simple extends \fan\core\service\captcha\base
{
    public function makeNewText(int $length, string $type): string
    {
        $result = '';
        if ($type === 'char') {
            $consonant = [
                'B', 'C', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'V', 'W', 'X', 'Z', 'TR', 'CR', 'FR', 'DR', 'WR', 'PR', 'TH', 'CH', 'PH', 'ST', 'SL', 'CL'
            ];
            $vowel     = [
                'A', 'E', 'I', 'O', 'U', 'Y', 'AE', 'OU', 'IO', 'EA', 'OU', 'IA', 'AI'
            ];

            $ccnt = count($consonant) - 1;
            $vcnt = count($vowel) - 1;
            for ($i = 0; $i < $length / 2; $i++) {
                $result .= $consonant[mt_rand(0, $ccnt)] . $vowel[mt_rand(0, $vcnt)];
            }
        } else {
            for ($i = 0; $i < $length; $i++) {
                $result .= mt_rand($i === 0 ? 1 : 0, 9);
            }
        }

        return substr($result, 0, $length);
    }

}
