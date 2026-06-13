<?php
declare(strict_types=1);

namespace fan\core\base\model\spec_file\image;
use fan\core\base\model\entity as model_entity;
use fan\core\base\model\spec_file\row as spec_file_row;

/**
 * Row of special file
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
 * @version of file: 05.02.006 (20.04.2015)
 * @abstract
 */
abstract class row extends spec_file_row
{

    private ?object $runtime = null;

    private mixed $imageModifyFactory = null;

    private mixed $errorFactory = null;

    private ?row_state $state = null;

    private ?object $imageMetadataReader = null;
    private ?object $imageSourceFileStorage = null;

    public function setSpecFileImageRowDependencies(
        ?object $runtime = null,
        ?callable $templateFactory = null,
        ?callable $imageModifyFactory = null,
        ?callable $errorFactory = null,
        ?row_state $state = null,
        ?object $imageMetadataReader = null,
        ?object $imageSourceFileStorage = null
    ): static {
        if ($runtime !== null) {
            $this->runtime = $runtime;
        }
        if ($imageModifyFactory !== null) {
            $this->imageModifyFactory = $imageModifyFactory;
        }
        if ($errorFactory !== null) {
            $this->errorFactory = $errorFactory;
        }
        if ($state !== null) {
            $this->state = $state;
        }
        if ($imageMetadataReader !== null) {
            $this->imageMetadataReader = $imageMetadataReader;
        }
        if ($imageSourceFileStorage !== null) {
            $this->imageSourceFileStorage = $imageSourceFileStorage;
        }

        return $this;
    }

    protected function setDependenciesFromEntityService(model_entity $entity): void
    {
        parent::setDependenciesFromEntityService($entity);
        $dependencies = $entity->specFileImageRowDependencies();
        if ($dependencies !== []) {
            $this->setSpecFileImageRowDependencies(...$dependencies);
        }
    }

    private function runtimeService(): object
    {
        return $this->runtime ?? throw new \RuntimeException('Bootstrap runtime service is not configured for spec image row.');
    }

    private function imageModifyService(string $sourcePath): object
    {
        return $this->imageModifyFactory !== null ? ($this->imageModifyFactory)($sourcePath) : throw new \RuntimeException('Image modify service is not configured for spec image row.');
    }

    private function errorService(): object
    {
        return $this->errorFactory !== null ? ($this->errorFactory)() : throw new \RuntimeException('Error service is not configured for spec image row.');
    }

    protected function getTemplate(): object
    {
        throw new \RuntimeException('Template service has been removed for spec image row.');
    }

    private function state(): row_state
    {
        return $this->state ?? throw new \RuntimeException('Spec-file image row state is not configured for spec-file image row.');
    }

    private function imageMetadataReader(): object
    {
        return $this->imageMetadataReader ?? throw new \RuntimeException('Image metadata reader is not configured for spec-file image row.');
    }

    private function imageSourceFileStorage(): object
    {
        return $this->imageSourceFileStorage ?? throw new \RuntimeException('Image source file storage is not configured for spec-file image row.');
    }

    public function setFormFile(string $formKey, array $addKeys = [], string $decription = '', string $alt = ''): bool
    {
        if ($this->getEntityFile()->setFormFile($formKey, $addKeys, 'image', $decription)) {
            return $this->saveImage($alt);
        }
        return false;
    }

    /**
     * @param mixed $url URL used as the external request target.
     */
    public function setUrlFile(string $url, string $decription = '', string $alt = ''): bool
    {
        if ($this->getEntityFile()->setUrlFile($url, 'image', $decription)) {
            return $this->saveImage($alt);
        }
        return false;
    }

    public function setLocalFile(string $srcPath, string $decription = '', string $alt = '', ?string $name = null, bool $deleteOrigin = false): bool
    {
        $imgInf = $this->getImageSize($srcPath);
        if ($imgInf) {
            if ($this->getEntityFile()->setLocalFile($srcPath, 'image', $imgInf['mime'], $decription, $name, $deleteOrigin)) {
                return $this->saveImage($alt);
            }
        }
        return false;
    }

    public function getImageData(): array
    {
        $ret = $this->getFields();
        unset($ret[$this->getEntity()->getDescription()->getPrimeryKey()]);
        $ret['id']          = $this->getId();
        $ret['description'] = $this->getEntityFile()->get_description();
        $ret['src_name']    = $this->getEntityFile()->get_src_name();
        return $ret;
    }

    public function rotateImage(int|float $angle, int $bgrColor = 0xFFFFFF, int|float $fix = 0): void
    {
        $si = $this->imageModifyService($this->getEntityFile()->getFilePath());
        $si->rotate($angle, $bgrColor, $fix);
        $si->saveAndReplace(null);
        $this->saveImage();
    }

    public function advGetImgTag(string $type, array $param): ?string
    {
        $type = strtolower($type);
        if (!$this->checkIsLoad() || !in_array($type, ['img', 'nail', 'link', 'blowup1', 'blowup2'])) {
            return null;
        }

        // ----- Set img-param ----- \\
        if ($type === 'img') {
            $this->setMainImgParam($param);
        } else {
            $this->setUrl($param['img'], 'nail', '/nail.php?id=', '&amp;w={width}&amp;h={height}');
            $this->resizeImage($param['img']);
            $this->setMainImgParam($param, false);
        }


        // ----- Set link-param ----- \\
        if (substr($type, 0, 6) === 'blowup') {
            $this->setUrl($param['link'], 'blowup', '/blowup/id-', '.html');
            if (!isset($tmp['target'])) {
                $tmp['target'] = '_blank';
            }
        }

        // ----- Make signature-tag ----- \\
        if (!isset($param['signature']['position'])) {
            $param['signature']['position'] = $this->getConfig('signature_pos', 'none');
        }

        return $this->fetchHtml($type, $param);
    }

    public function getImgTag(mixed $param = null): ?string
    {
        if ($this->checkIsLoad()) {
            $this->setMainImgParam($param);
            return $this->fetchHtml('simple', $param);
        }
        return null;
    }

    protected function saveImage(?string $alt = null): bool
    {
        $imgData = $this->getImageSize($this->getEntityFile()->getFilePath());
        if ($imgData) {
            $this->setId($this->getEntityFile()->getId());
            $this->set_width($imgData[0]);
            $this->set_height($imgData[1]);
            $this->set_img_type($imgData[2]);
            if (!is_null($alt)) {
                $this->set_alt($alt);
            }
            $this->save();
            return true;
        }
        return false;
    }

    public function checkAccess(): bool
    {
        return $this->getEntityFile()->checkAccess();
    }

    public function checkIsOwner(): bool
    {
        return $this->getEntityFile()->checkIsOwner();
    }


    protected function fetchHtml(string $type, mixed $param): string
    {
        $template = $this->getTemplate();
        $template->setBaseParam($param);
        $template->assign('img_type', $type);
        return $template->fetch();
    }

    protected function setUrl(mixed &$param, string $key, string $defPrefix, string $defSuffix): void
    {
        if (empty($param['full_url'])) {
            $conf = $this->getConfig($key);
            $this->setParam($param, 'url_prefix', (isset($conf['url_prefix']) ? $conf['url_prefix'] : $defPrefix));
            $this->setParam($param, 'url_suffix', (isset($conf['url_prefix']) ? $conf['url_suffix'] : $defSuffix));
            $param['full_url'] = $this->checkIsLoad() ? $param['url_prefix'] . $this->getId() . $param['url_suffix'] : '';
        }
    }

    protected function setParam(array &$param, string $key, mixed $val): void
    {
        if (!isset($param[$key])) {
            $param[$key] = $val;
        }
    }


    protected function setMainImgParam(mixed &$param, bool $full = true): void
    {
        if ($this->checkIsLoad()) {
            $v = &$param['img'];
            if ($full) {
                $this->setUrl($v, 'img', '/file.php?id=', '');
                $this->setParam($v, 'width', $this->get_width());
                $this->setParam($v, 'height', $this->get_height());
            }
            $this->setParam($v, 'alt', $this->get_alt());
            $this->setParam($v, 'title', $this->get_alt());
        }
    }

    protected function resizeImage(array &$param): void
    {
        $enableIncrease = false; // ToDo: move it to config if need enable it

        $width   = $this->get_width();  // Set width  (default: source width)
        $height  = $this->get_height(); // Set height (default: source height)
        $width_  = intval($param['width'] ?? 0);  // Requested max width
        $height_ = intval($param['height'] ?? 0); // Requested max height
        if ($width_ || $height_) {
             if ($height_ && $width_) { // Select determinator
                if ($width_ / $width <= $height_ / $height) {
                    $height_ = 0;
                } else {
                    $width_  = 0;
                }
            }
            if (!$width_) {
                if ($height_ < $height || $enableIncrease) { // Determine by Height
                    $width  = round($height_ * $width / $height);
                    $height = $height_;
                }
            } elseif ($width_ < $width || $enableIncrease) { // Determine by Width
                $height = round($width_ * $height / $width);
                $width  = $width_;
            }
        }

        foreach (['url_suffix', 'full_url'] as $k) {
            if (isset ($param[$k])) {
                $param[$k] = str_replace(['{width}', '{height}'], [$width, $height], $param[$k]);
            }
        }

        $param['width']  = $width;
        $param['height'] = $height;
    }

    private function getImageSize(string $path): array|false
    {
        if ($path === '') {
            return false;
        }
        if (
            !preg_match('/^[a-z][a-z0-9+.-]*:\/\//i', $path)
            && (!$this->imageSourceFileStorage()->isFile($path) || !$this->imageSourceFileStorage()->isReadable($path))
        ) {
            $this->errorService()->logErrorMessage('Image file "' . $path . '" is not readable.', 'Image metadata error', '', true, false);
            return false;
        }

        $errorMessage = null;
        $result = $this->imageMetadataReader()->size(
            $path,
            static function (string $message) use (&$errorMessage): void {
                $errorMessage = $message;
            }
        );
        if ($result === false && $errorMessage) {
            $this->errorService()->logErrorMessage($errorMessage, 'Image metadata error', '', true, false);
        }
        return $result;
    }

}
