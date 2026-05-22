<?php

declare(strict_types=1);

namespace fan\core\service;
use fan\project\exception\service\fatal as fatalException;
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
class image_modify extends \fan\core\base\service\multi
{
    /**
     * @var \fan\core\service\image_modify[] Service's Instances
     */
    private static ?array $instances = null;

    protected ?string $sourcePath = null;
    /**
     * @var numeric width of Source Image
     */
    protected int|float|null $sourceWidth = null;
    /**
     * @var numeric height of Source Image
     */
    protected int|float|null $sourceHeight = null;
    /**
     * @var numeric Image current width
     */
    protected int|float|null $width = null;
    /**
     * @var numeric Image current height
     */
    protected int|float|null $height = null;
    private ?array $sourceParam = null;
    protected ?object $image = null;

    /**
     * @var numeric Quality save of Create Image
     */
    private int|float $quality = 80;

    private ?string $type = null;

    private array $convType = [
        1  => 'gif',
        2  => 'jpeg',
        3  => 'png',
        15 => 'wbmp',
        16 => 'xbm',
    ];

    protected function __construct(?string $sourcePath, array $createParam)
    {
        parent::__construct(empty(self::$instances));

        if (!empty($sourcePath) || !empty($createParam)) {
            $this->setSource($sourcePath, $createParam);
        }
    }

    // ======== Static methods ======== \\

    public static function instance(?string $sourcePath = null, array $createParam = [], bool $saveInstance = true): static
    {
        $name = self::checkName(get_called_class());
        if (!$saveInstance || !isset(self::$instances[$name])) {
            $instance = new $name($sourcePath, $createParam);
            if (!$saveInstance) {
                return $instance;
            }
            self::$instances[$name] = $instance;
        }
        return self::$instances[$name];
    }

    // ======== Main Interface methods ======== \\

    // ---------- Prepare functions ---------- \\
    public function setSource(mixed $sourcePath, array $createParam = []): static
    {
        // If "Create Parameters" exist - save them
        if (!empty($createParam)) {
            $this->setParam($createParam);
        }

        if (!is_null($sourcePath)) {
            // If $sourcePath is empty, but not NULL - get source image from config
            if (empty($sourcePath)) {
                $sourcePath = $this->getConfig('DEFAULT_IMAGE');
            }
            // If $sourcePath is not empty set basic image by source
            if (!empty($sourcePath)) {
                $sourcePath = (string)\bootstrap::parsePath((string)$sourcePath);
                if (!file_exists($sourcePath)) {
                    $sourcePath = (string)\bootstrap::parsePath((string)$this->getConfig('BASIC_PATH')) . $sourcePath;
                }
                $this->sourcePath = $sourcePath;

                // Check - file exists and readable
                if (is_readable($sourcePath)) {
                    if (!exif_imagetype($sourcePath)) {
                        throw new fatalException($this, 'Incorrect image file format "' . $sourcePath . '".');
                    }
                    $this->sourceParam = getimagesize($sourcePath);
                } else {
                    throw new fatalException($this, 'Image-file "' . $this->sourcePath . '" isn\'t ' . (file_exists($sourcePath) ? 'readable.' : 'exist.'));
                }

                // Set parameters by source
                $type = $this->convType[$this->sourceParam[2]];
                if (is_null($this->type)) {
                    $this->type = $type;
                }

                $func = 'imagecreatefrom' . $type;
                $this->image = $func($sourcePath);

                $this->sourceWidth = (int)$this->sourceParam[0];
                if (empty($this->width)) {
                    $this->width = $this->sourceWidth;
                }
                $this->sourceHeight = (int)$this->sourceParam[1];
                if (empty($this->height)) {
                    $this->height = $this->sourceHeight;
                }
            }
        }
        // If $sourcePath is not set - create blank image
        if (empty($sourcePath)) {
            if ($this->sourceWidth < 1 || $this->sourceHeight < 1) {
                throw new fatalException($this, 'Image size doesn\'t set (' . $this->sourceWidth . 'x' . $this->sourceHeight . ').');
            }
            $this->image = imagecreatetruecolor((int)$this->sourceWidth, (int)$this->sourceHeight);
        }

        return $this;
    }

    public function setParam(array $param): static
    {
        if (isset($param['type'])) {
            $this->type = (string)$param['type'];
        }
        if (isset($param['quality'])) {
            $quality = (float)$param['quality'];
            $this->quality = $quality < 1 || $quality > 100 ? 80 : $quality;
        }
        if (isset($param['width'])) {
            $this->width = (float)$param['width'];
            if (empty($this->sourceWidth)) {
                $this->sourceWidth = $this->width;
            }
        }
        if (isset($param['height'])) {
            $this->height = (float)$param['height'];
            if (empty($this->sourceHeight)) {
                $this->sourceHeight = $this->height;
            }
        }
        return $this;
    }

    public function setTransparent(int|string|array $color): static
    {
        imagecolortransparent($this->image, $this->adaptColor($color));
        return $this;
    }

    public function getSourceParam(): ?array
    {
        return $this->sourceParam;
    }

    public function getWidth(): int|float|null
    {
        return $this->width;
    }
    public function getSourceWidth(): int|float|null
    {
        return $this->sourceWidth;
    }
    public function getHeigth(): int|float|null
    {
        return $this->height;
    }
    public function getSourceHeigth(): int|float|null
    {
        return $this->sourceHeight;
    }

    // ---------- Main Convert functions ---------- \\

    public function relocate(int|float|null $width = null, int|float|null $height = null, int|string|array $bgrColor = 0XFFFFFF): static
    {
        if (is_null($width)) {
            $width = $this->width;
        }
        if (is_null($height)) {
            $height = $this->height;
        }
        if ($width < 1 || $height < 1) {
            throw new fatalException($this, 'Image size doesn\'t set (' . $width . 'x' . $height . ').');
        }

        $left = round(($width - $this->width) / 2);
        $top  = round(($height - $this->height) / 2);

        $position = [
            'dstX' => $left,
            'dstY' => $top,
            'srcX' => 0,
            'srcY' => 0,
            'dstW' => $width,
            'dstH' => $height,
            'srcW' => $this->width,
            'srcH' => $this->height,
        ];
        $this->_replaceImage($width, $height, $position, null, $bgrColor);
        return $this;
    }

    public function scal(int|float|null &$width, int|float|null &$height, int|float $fixRatio = 1, int|string|array $bgrColor = 0xFFFFFF): static
    {
        $left = 0;
        $top  = 0;
        if ($fixRatio && $width && $height)  {
            if ($this->width/$width > $this->height/$height) {
                // to fall into a width
                $width_  = $width;
                $height_ = 0;
                $this->correctSize($width_, $height_, $this->width, $this->height);
                if ((int)$fixRatio === 2) {
                    $top = round(($height - $height_) / 2);
                } else {
                    $height = $height_;
                }
            } else {
                // to fall into a height
                $width_  = 0;
                $height_ = $height;
                $this->correctSize($width_, $height_, $this->width, $this->height);
                if ((int)$fixRatio === 2) {
                    $left  = round(($width - $width_) / 2);
                } else {
                    $width = $width_;
                }
            }
        } else {
            $this->correctSize($width, $height, $this->width, $this->height);
            $width_  = $width;
            $height_ = $height;
        }

        if ($width && $height && ((float)$width !== (float)$this->width || (float)$height !== (float)$this->height)) {
            $position = [
                'dstX' => $left,
                'dstY' => $top,
                'srcX' => 0,
                'srcY' => 0,
                'dstW' => $width_,
                'dstH' => $height_,
                'srcW' => $this->width,
                'srcH' => $this->height,
            ];
            $this->_replaceImage($width, $height, $position, null, (int)$fixRatio === 2 ? $bgrColor : null);
        } // if convert image
        return $this;
    }

    public function crop(int|float $left, int|float $top, int|float $width, int|float $height): static
    {
        if ($left < 0 || $left > $this->width) {
            $left = 0;
        }
        if ($top < 0 || $top > $this->height) {
            $top = 0;
        }
        if ($left + $width > $this->width || (float)$width === 0.0) {
            $width  = $this->width - $left;
        }
        if ($top + $height > $this->height || (float)$height === 0.0) {
            $height = $this->height - $top;
        }

        if ((float)$width !== (float)$this->width || (float)$height !== (float)$this->height) {
            $position = [
                'dstX' => 0,
                'dstY' => 0,
                'srcX' => $left,
                'srcY' => $top,
                'dstW' => $width,
                'dstH' => $height,
                'srcW' => $width,
                'srcH' => $height,
            ];
            $this->_replaceImage($width, $height, $position);
        }
        return $this;
    }

    public function rotate(int|float $angle, int|string|array $bgrColor = 0xFFFFFF, int|float $fix = 0): static
    {
        while (abs($angle) > 360){
            $angle = $angle > 0 ? $angle - 360 : $angle + 360;
        } // while $angle > 360
        if ((float)$angle !== 0.0) {
            $imgTmp = imagerotate($this->image, (float)$angle, $this->adaptColor($bgrColor));
            if ($fix) {
                $fix = (int)$fix;
                $width  = ($fix === 1 || $fix === 3) ? $this->width  : 0;
                $height = ($fix === 2 || $fix === 3) ? $this->height : 0;
                $tempWidth  = imagesx($imgTmp);
                $tempHeight = imagesy($imgTmp);
                $this->correctSize($width, $height, $tempWidth, $tempHeight);
                $position = [
                    'dstX' => 0,
                    'dstY' => 0,
                    'srcX' => 0,
                    'srcY' => 0,
                    'dstW' => $width,
                    'dstH' => $height,
                    'srcW' => $tempWidth,
                    'srcH' => $tempHeight,
                ];
                $this->_replaceImage($width, $height, $position, $imgTmp);
            } else {
                $this->image = $imgTmp;
            } // Fix size
        } // if convert image
        return $this;
    }

    public function border(int|float $depth, int|string|array $brdColor = 0x000000, bool $inline = false): static
    {
        if ($depth > 0) {
            if (!$inline) {
                $srcImg = $this->image;
                $width  = $this->width;
                $height = $this->height;
                $this->width  += $depth * 2;
                $this->height += $depth * 2;
                $this->image = imagecreatetruecolor((int)$this->width, (int)$this->height);
                imagecopyresampled($this->image, $srcImg, (int)$depth, (int)$depth, 0, 0, (int)$width, (int)$height, (int)$width, (int)$height);
            }
            $color = $this->adaptColor($brdColor);
            imagefilledrectangle($this->image, 0, 0, (int)$this->width, (int)($depth - 1), $color);
            imagefilledrectangle($this->image, 0, 0, (int)($depth - 1), (int)$this->height, $color);
            imagefilledrectangle($this->image, 0, (int)($this->height - $depth), (int)$this->width, (int)$this->height, $color);
            imagefilledrectangle($this->image, (int)($this->width - $depth), 0, (int)$this->width, (int)$this->height, $color);
        }
        return $this;
    }

    public function colorize(int|string|array $color): static
    {
        $color = $this->adaptColor($color);
        imagefilter($this->image, IMG_FILTER_COLORIZE, $color >> 16, ($color >> 8) & 0xFF, $color & 0xFF);
        return $this;
    }

    public function blur(): static
    {
        imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
        return $this;
    }

    public function grayscale(): static
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        return $this;
    }

    public function sepia(): static
    {
        imagefilter($this->image, IMG_FILTER_GRAYSCALE);
        imagefilter($this->image, IMG_FILTER_COLORIZE, 50, 25, 5);
        return $this;
    }

    public function markering(string $markerMode = 'left_bottom', int|float $opacity = 10): static
    {
        $pathToPic = (string)\bootstrap::parsePath((string)$this->config['WATERMARK_PATH']);

        $param = getimagesize($pathToPic);
        if ($param) {
            $widthMark  = $param[0];
            $heightMark = $param[1];

            switch ($markerMode) {
                case 'left_bottom': {
                    $posX = 10;
                    $posY = $this->height - $heightMark - 10;
                    break;
                }
                case 'right_bottom': {
                    $posX = $this->width  - $widthMark  - 10;
                    $posY = $this->height - $heightMark - 10;
                    break;
                }
                case 'left_top': {
                    $posX = 10;
                    $posY = 10;
                    break;
                }
                case 'right_top': {
                    $posX = $this->width - $widthMark - 10;
                    $posY = 10;
                    break;
                }
                case 'center': {
                    $posX = intval($this->width  / 2 - $widthMark  / 2);
                    $posY = intval($this->height / 2 - $heightMark / 2);
                    break;
                }
                default: { // 'left_bottom'
                    $posX = 10;
                    $posY = $this->height - $heightMark - 10;
                    break;
                }
            }

            $type = null;
            switch ($param[2]) {
                case 1: {
                    $type = 'gif';
                    break;
                }
                case 2: {
                    $type = 'jpeg';
                    break;
                }
                case 3: {
                    $type = 'png';
                    break;
                }
            }
            $func      = 'imagecreatefrom' . $type;
            $imgMarker = $func($pathToPic);
            imagecopymerge($this->image, $imgMarker, (int)$posX, (int)$posY, 0, 0, (int)$widthMark, (int)$heightMark, (int)$opacity);
            imagedestroy($imgMarker);
        }
        return $this;
    }

    public function adaptColor(int|string|array $color): int
    {
        if (is_array($color)) {
            $sourceColor = $color;
            $color = ['r' => 0, 'g' => 0, 'b' => 0];
            foreach ($color as $k1 => &$v) {
                $k2 = strtoupper($k1);
                if (isset($sourceColor[$k1])) {
                    $v = $sourceColor[$k1];
                } elseif (isset($sourceColor[$k2])) {
                    $v = $sourceColor[$k2];
                } else {
                    throw new \InvalidArgumentException('Color key ' . $k2 . ' isn\'t defined.');
                }
                if (is_string($v)) {
                    $v = hexdec($v);
                }
                $v = (int)abs(round((float)$v)) % 0xFF;
            }
        } else {
            if (is_string($color)) {
                $color = hexdec($color);
            }
            $color = (int)abs(round((float)$color)) % 0xFFFFFF;
            $color = [
                'r' => $color >> 16,
                'g' => ($color >> 8) & 0xFF,
                'b' => $color & 0xFF
            ];
        }
        $result = imagecolorallocate($this->image, (int)$color['r'], (int)$color['g'], (int)$color['b']);
        if ($result === false) {
            throw new \UnexpectedValueException('Incorrect value of color ' . var_export($color, true));
        }
        return $result;
    }

    // ---------- Finish methods ---------- \\
    public function getType(): ?string
    {
        return $this->type;
    }

    public function getImageInfo(int $timeExpires = 0, bool $withContent = true): array
    {
        $content = (string)$this->getImage();
        $imgPath  = pathinfo((string)$this->sourcePath);

        return [
            'sourcePath' => $this->sourcePath,
            'content'    => $withContent ? $content : null,
            'type'       => $this->type,
            'headers' => [
                'contentType' => 'image/' . $this->type,
                'filename'    => $imgPath['basename'],
                'length'      => strlen($content),
                'legthRange'  => 'bytes',
                'modified'    => time(),
                'cacheLimit'  => (int)$timeExpires,
            ],
        ];
    }

    public function getImage(): string|false
    {
        ob_start();
        $this->saveAsNew(null);
        $output = ob_get_contents();
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        return $output;
    }

    public function saveAsNew(?string $newFile = null): static
    {
        $func = 'image' . $this->type;
        if (in_array($func, ['imagejpeg', 'imagepng'])) {
            if ($func === 'imagepng' && $this->quality > 10) {
                $this->quality = round($this->quality/10);
            }
            $func($this->image, $newFile, (int)$this->quality);
        } elseif ($this->type && function_exists($func)) {
            $func($this->image, $newFile);
        } else {
            throw new fatalException($this, 'Incorrect image type (' . $this->type . ').');
        }
        return $this;
    }

    public function saveAndReplace(mixed $ext = 'bak'): static
    {
        if (!is_null($ext)) {
            $imgPath = pathinfo((string)$this->sourcePath);
            rename((string)$this->sourcePath, $imgPath['dirname'] . '/' . $imgPath['filename'] . '.' . (string)$ext . '.' . $imgPath['extension']);
        }
        $this->saveAsNew($this->sourcePath);
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @param int|float $default Fallback value returned when no explicit value is available.
     */
    protected function _getCoord(array $coord, string $key, int|float $default = 0): mixed
    {
        if (!isset($coord[$key])) {
            $trace = debug_backtrace();
            throw new \OutOfBoundsException(
                    'Coordinate isn\'t set for ' . $key . '<br />' .
                    (isset($trace[1]['file']) ? 'file "<nobr><b>'  . $trace[1]['file'] . '</b></nobr>", ' : 'No file') .
                    (isset($trace[1]['line']) ? 'line <b>'         . $trace[1]['line'] . '</b>.' : '')
            );
        }
        return array_val($coord, $key, $default);
    }

    protected function _getCoordDiff(array $coord, string $key1, string $key2): int|float
    {
        return abs($this->_getCoord($coord, $key1) - $this->_getCoord($coord, $key2));
    }

    protected function _replaceImage(int|float $width, int|float $height, array $position, mixed $srcImg = null, int|string|array|null $bgrColor = null): static
    {
        if (is_null($srcImg)) {
            $srcImg = $this->image;
        }
        $this->image = imagecreatetruecolor((int)$width, (int)$height);
        if (!is_null($bgrColor)) {
            imagefilledrectangle($this->image, 0, 0, (int)$width, (int)$height, $this->adaptColor($bgrColor));
        }
        imagecopyresampled($this->image, $srcImg, (int)$position['dstX'], (int)$position['dstY'], (int)$position['srcX'], (int)$position['srcY'], (int)$position['dstW'], (int)$position['dstH'], (int)$position['srcW'], (int)$position['srcH']);
        $this->width  = $width;
        $this->height = $height;
        return $this;
    }

    private function correctSize(&$width, &$height, $oldWidth, $oldHeight): static
    {
        if (!$width && $height && $oldHeight) {
            $width  = round($height * $oldWidth / $oldHeight);
        } elseif (!$height && $width && $oldWidth) {
            $height = round($width * $oldHeight / $oldWidth);
        }
        return $this;
    }

}
