<?php
declare(strict_types=1);

namespace fan\core\service\template\parser;
/**
 * Template parser engine form
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
class form extends base
{
    protected array $tagList = ['form_key_field', 'form_sid'];

    public function parse_form_key_field(): string
    {
        return '$returnHtmlVal.=$this->getKeyField();' . "\n";
    }

    public function parse_form_sid(): string
    {
        return '$returnHtmlVal.=$this->getSidField();' . "\n";
    }

}
