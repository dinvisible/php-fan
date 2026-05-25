<?php

declare(strict_types=1);

namespace fan\core\service;

/**
 * Service Image Processor
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class image_draw extends image_modify
{

// =========================== Main Convert functions ============================ \\

    public function setBackground(int|string|array $bgrColor = 0xFFFFFF): static
    {
        $this->imageCanvasOperations()->fillRectangle($this->image, 0, 0, (int)$this->sourceWidth, (int)$this->sourceHeight, $this->adaptColor($bgrColor));
        return $this;
    }

    public function drawText(string $string, array $coord, int|float $fontNumber = 1, int|string|array $fntColor = 0x000000, string $txtAlign = 'left', string $vertAlign = 'top'): static
    {
        $fontNumber = (int)$fontNumber;

        //Calculating of top y coordinate for text
        if ($vertAlign === 'top') {
            $top = $this->_getCoord($coord, 'top');
        } elseif ($vertAlign === 'middle') {
            $top = ceil(($this->sourceHeight - $this->imageCanvasOperations()->fontHeight($fontNumber) - $this->_getCoordDiff($coord, 'bottom', 'top')) / 2);
        } elseif ($vertAlign === 'bottom') {
            $top = $this->sourceHeight - ($this->_getCoord($coord, 'bottom') + $this->imageCanvasOperations()->fontHeight($fontNumber));
        }
        //Calculating of left x coordinate for text
        if ($txtAlign === 'left') {
            $left = $this->_getCoord($coord, 'left');
        } elseif ($txtAlign === 'center') {
            $left = ceil(($this->sourceWidth - $this->imageCanvasOperations()->fontWidth($fontNumber) * strlen($string) - $this->_getCoordDiff($coord, 'right', 'left')) / 2);
        } elseif ($txtAlign === 'right') {
            $left = $this->sourceWidth - ($this->_getCoord($coord, 'right') + $this->imageCanvasOperations()->fontWidth($fontNumber) * strlen($string));
        }

        $this->imageCanvasOperations()->string($this->image, $fontNumber, (int)$left, (int)$top, $string, $this->adaptColor($fntColor));
        return $this;
    }

    public function drawTextTtf(string $string, array $coord, string $fontFile = '', int|string|array $fntColor = 0X000000, string $txtAlign = 'left', string $vertAlign = 'top',  array $info = ['linespacing' => 1]): static
    {
        if (!$fontFile) {
            $fontFile = (string)$this->get_config('FONT_FILE', 'arial.ttf');
        }
        $fontFile = (string)$this->runtime()->parsePath((string)$this->getConfig('FONT_PATH', '{PROJECT}/data/font/')) . $fontFile;

        $fontHeight = (int)(isset($coord['height']) ? $coord['height'] : $this->sourceHeight - $this->_getCoordDiff($coord, 'bottom', 'top') / 2);
        $stringSize = $this->imageCanvasOperations()->trueTypeBoundingBox($fontHeight, 0, $fontFile, $string, $info);
        $strWidth  = $stringSize[4];
        $strHeight = -$stringSize[5];

        //calculating of top y coordinate for text
        if ($vertAlign === 'top') {
            $top = $this->_getCoord($coord, 'top');
        } elseif ($vertAlign === 'middle') {
            $top = ceil(($this->sourceHeight - $strHeight - $this->_getCoordDiff($coord, 'bottom', 'top')) / 2);
        } elseif ($vertAlign === 'bottom') {
            $top = $this->sourceHeight - ($this->_getCoord($coord, 'bottom') + $strHeight);
        }
        //calculating of left x coordinate for text
        if ($txtAlign === 'left') {
            $left = $this->_getCoord($coord, 'left');
        } elseif ($txtAlign === 'center') {
            $left = ceil(($this->sourceWidth - $strWidth - $this->_getCoordDiff($coord, 'right', 'left')) / 2);
        } elseif ($txtAlign === 'right') {
            $left = $this->sourceWidth - ($this->_getCoord($coord, 'right') + $strWidth);
        }

        $this->imageCanvasOperations()->trueTypeText($this->image, $fontHeight, (float)$this->arrayValueReader()($coord, 'angle', 0), (int)$left, (int)($top + $fontHeight), $this->adaptColor($fntColor), $fontFile, $string);
        return $this;
    }

    public function rectangle(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0xFFFFFF): static
    {
        if (empty($coord)) {
            $coord = [
                'left'   => 0,
                'right'  => 1,
                'top'    => 0,
                'bottom' => 1,
                'angle'  => 0,
                'height' => 0,
            ];
        }
        if (!is_null($brdColor)) {
            $this->imageCanvasOperations()->rectangle($this->image, (int)$coord['left'], (int)$coord['top'], (int)($this->width - $coord['right']), (int)($this->height - $coord['bottom']), $this->adaptColor($brdColor));
        }
        if (!is_null($bgrColor)) {
            $this->imageCanvasOperations()->fillRectangle($this->image, (int)$coord['left'], (int)$coord['top'], (int)($this->width - $coord['right']), (int)($this->height - $coord['bottom']), $this->adaptColor($bgrColor));
        }
        return $this;
    }

    public function polygon(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0XFFFFFF): static
    {
        $points = array_map(static fn($value): int => (int)round((float)$value), $coord);
        $pointCount = (int)(count($points) / 2);

        if (!is_null($brdColor)) {
            $this->imageCanvasOperations()->polygon($this->image, $points, $pointCount, $this->adaptColor($brdColor));
        }
        if (!is_null($bgrColor)) {
            $this->imageCanvasOperations()->filledPolygon($this->image, $points, $pointCount, $this->adaptColor($bgrColor));
        }
        return $this;
    }

    public function ellipse(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0XFFFFFF): static
    {
        if (!is_null($brdColor)) {
            $this->imageCanvasOperations()->ellipse(
                    $this->image,
                    (int)$coord['centerX'],
                    (int)$coord['centerY'],
                    (int)$coord['width'],
                    (int)$coord['height'],
                    $this->adaptColor($brdColor)
            );
        }
        if (!is_null($bgrColor)) {
            $this->imageCanvasOperations()->filledEllipse(
                    $this->image,
                    (int)$coord['centerX'],
                    (int)$coord['centerY'],
                    (int)$coord['width'],
                    (int)$coord['height'],
                    $this->adaptColor($bgrColor)
            );
        }
        return $this;
    }

    public function ellipseSector(array $coord, int|string|array|null $brdColor = 0x000000, int|string|array|null $bgrColor = 0XFFFFFF): static
    {
        if (!is_null($brdColor)) {
            $this->imageCanvasOperations()->arc(
                    $this->image,
                    (int)$coord['centerX'],
                    (int)$coord['centerY'],
                    (int)$coord['width'],
                    (int)$coord['height'],
                    (int)$coord['startAngle'],
                    (int)$coord['endAngle'],
                    $this->adaptColor($brdColor)
            );
        }
        if (!is_null($bgrColor)) {
            $this->imageCanvasOperations()->filledArc(
                    $this->image,
                    (int)$coord['centerX'],
                    (int)$coord['centerY'],
                    (int)$coord['width'],
                    (int)$coord['height'],
                    (int)$coord['startAngle'],
                    (int)$coord['endAngle'],
                    $this->adaptColor($bgrColor),
                    IMG_ARC_PIE
            );
        }
        return $this;
    }



    public function line(array $coord, int|string|array $brdColor = 0X000000): static
    {
        $arrayValueReader = $this->arrayValueReader();

        $this->imageCanvasOperations()->line(
                $this->image,
                (int)$arrayValueReader($coord, 'left', 0),
                (int)$arrayValueReader($coord, 'top', 0),
                (int)($this->width - $arrayValueReader($coord, 'right', 0)),
                (int)($this->height - $arrayValueReader($coord, 'bottom', 0)),
                $this->adaptColor($brdColor)
        );
        return $this;
    }

    public function lineVertical(array $coord, int|string|array $brdColor = 0X000000): static
    {
        if (!isset($coord['left'])) {
            $coord['left'] = isset($coord['right']) ? $this->width - $coord['right'] : 0;
        }
        $coord['right'] = $this->width - $coord['left'];
        return $this->line($coord, $brdColor);
    }

    public function lineHorizontal(array $coord, int|string|array $brdColor = 0X000000): static
    {
        if (!isset($coord['top'])) {
            $coord['top'] = isset($coord['bottom']) ? $this->height - $coord['bottom'] : 0;
        }
        $coord['bottom'] = $this->height - $coord['top'];
        return $this->line($coord, $brdColor);
    }

    public function getFontWidth(int|float $fontNumber): int
    {
        return $this->imageCanvasOperations()->fontWidth((int)$fontNumber);
    }

    public function getFontHeigth(int|float $fontNumber): int
    {
        return $this->imageCanvasOperations()->fontHeight((int)$fontNumber);
    }

// ========================= Private methods ============================ \\

}
