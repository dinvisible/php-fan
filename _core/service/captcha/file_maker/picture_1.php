<?php
declare(strict_types=1);

namespace fan\core\service\captcha\file_maker;
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
class picture_1 extends \fan\core\service\captcha\base
{
    protected ?array $imgInfo = null;

    public function getHeaders(): array
    {
        $this->getData();
        $headers = $this->imgInfo['headers'];
        $headers['filename'] = 'captcha.' . $this->imgInfo['type'];
        return $headers;
    }

    public function getData(): string
    {
        if (empty($this->imgInfo)) {
            $this->_makeBinaryData();
        }
        return $this->imgInfo['content'];
    }

    // ======== Private/Protected methods ======== \\

    protected function _makeBinaryData(): static
    {
        $conf = $this->config['image'];
        /* @var $conf \fan\core\service\config\row */
        $width   = (int)$conf->get('width',  180);
        $height  = (int)$conf->get('height',  60);
        $quality = (int)$conf->get('quality', 80);

        // Make image service
        $img = service('image_draw');
        /* @var $img \fan\core\service\image_draw */
        $src = $this->_randomChoice($conf['src_files']);
        if (empty($src)) {
            $img->setSource(null, [
                'width'   => $width,
                'height'  => $height,
                'quality' => $quality,
            ]);
        } else {
            $img->setSource($this->config['SRC_DIR'] . $src, [
                'quality' => $quality,
            ]);
            $img->crop(
                    rand(0, (int)$img->getWidth() - $width),
                    rand(0, (int)$img->getHeigth() - $height),
                    $width,
                    $height
            );
        }

        // Draw lines in background
        $this->_drawLines($img, $conf, $conf->get('line_qtt', 10), $height);

        // Draw captcha text
        $text = (string)$this->facade->getText();
        $left = rand(5, 10);
        $top  = rand(5, (int)floor($height / 2));
        $font = $this->_randomChoice($conf['fonts']);
        for ($i = 0; $i < strlen($text); $i++) {
            $fontHeight = $this->_randomValue($conf['font_height'], 20, 24);
            $img->drawTextTtf($text[$i], [
                'left'   => $left,
                'top'    => rand($top, $top + (int)floor($height / 10)),
                'height' => $fontHeight,
                'angle'  => rand(-5, 5),
            ], $font, $this->_randomColor($conf, 'font'));
            $left += (int)floor($fontHeight * 0.8) + $this->_randomValue($conf['interval'], 2, 5);
        }

        // Draw lines in front
        $this->_drawLines($img, $conf, $conf->get('line_qtt', 10), $height);

        $this->imgInfo = $img->getImageInfo();
        return $this;
    }

    protected function _randomChoice(mixed $arr): mixed
    {
        if (is_object($arr)) {
            if (!method_exists($arr, 'toArray')) {
                return null;
            }
            $arr = $arr->toArray();
        }
        return empty($arr) ? null : $arr[array_rand($arr)];
    }

    protected function _randomValue(mixed $conf, int|float $defMin, int|float $defMax): int
    {
        if (is_object($conf)) {
            $min = $conf->get(0, $defMin);
            $max = $conf->get(1, $defMax);
        } else {
            $min = $defMin;
            $max = $defMax;
        }
        return rand((int)$min, (int)$max);
    }

    protected function _randomColor(\fan\core\service\config\row $conf, string $key): array
    {
        $src = [
            'r' => [0, 255],
            'g' => [0, 255],
            'b' => [0, 255],
        ];

        $res = [];
        foreach ($src as $k => $v) {
            $min = $conf->get(['color', $key, $k, 0], $v[0]);
            $max = $conf->get(['color', $key, $k, 1], $v[1]);
            $res[$k] = rand((int)$min, (int)$max);
        }
        return $res;
    }

    protected function _drawLines(\fan\core\service\image_draw $img, \fan\core\service\config\row $conf, int|float $qtt, int|float $height): static
    {
        for ($i = 0; $i < $qtt / 2; $i++)
        {
            $img->line([
                'left'   => rand(0, 20),
                'right'  => rand(0, 20),
                'top'    => rand(0, (int)$height),
                'bottom' => rand(0, (int)$height),
            ], $this->_randomColor($conf, 'line'));
        }
        return $this;
    }

}
