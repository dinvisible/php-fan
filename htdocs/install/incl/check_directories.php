<?php

declare(strict_types=1);

/**
 * Check directories of V-host
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
 * @version of file: 05.02.007 (31.08.2015)
 */
class check_directories extends base
{
    /**
     * Directory separator
     * @var string
     */
    protected string $separator = '';
    /**
     * Path to Base V-host directory
     * @var string
     */
    protected ?string $baseDir = null;
    /**
     * Path to CORE directory
     * @var string
     */
    protected ?string $coreDir = null;
    /**
     * Path to PROJECT directory
     * @var string
     */
    protected ?string $projectDir = null;
    /**
     * Path to TEMP-files directory
     * @var string
     */
    protected ?string $tempDir = null;
    /**
     * Path to Bootstrap config
     * @var string
     */
    protected ?string $bootstrapConfig = null;
    /**
     * Path to Service config
     * @var string
     */
    protected ?string $serviceConfig = null;
    /**
     * Flag is PROJECT dir specially defined
     * @var string
     */
    protected bool $isDefinedProjectDir = false;
    /**
     * Content of INI-file
     * @var array
     */
    protected array $iniContent = [
        'bootstrap' => '',
        'service'   => '',
    ];
    /**
     * Keys for parse path
     * @var array
     */
    protected array $placeholderMap = [
        'BASE_DIR'    => 'baseDir',
        'CORE_DIR'    => 'coreDir',
        'PROJECT'     => 'projectDir',
        'PROJECT_DIR' => 'projectDir',
        'TEMP'        => 'tempDir',
    ];

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\
    public function runCheck(): bool
    {
        $this->separator = DIRECTORY_SEPARATOR;
        $res  = $this->_checkBaseDirectories();
        $res &= $this->_checkLogDirectories();
        $res &= $this->_checkCacheDirectories();

        $this->_setConst();
        return $res;
    }
    // ======== Private/Protected methods ======== \\
    protected function _checkBaseDirectories(): bool
    {
        $result = false;
        $this->baseDir = $this->_adaptPath($_SERVER['DOCUMENT_ROOT']);
        $indexDir = $this->_findIndexDir();
        $this->view['baseDir']  = $this->baseDir;
        $this->view['indexDir'] = $indexDir;

        if (!empty($indexDir)) {
            $isCoreDir = is_dir($this->coreDir);
            if ($isCoreDir) {
                $this->coreDir = $this->_adaptPath(realpath($this->coreDir));
            }

            $isProjectDir = is_dir($this->projectDir);
            if ($isProjectDir) {
                $this->projectDir = $this->_adaptPath(realpath($this->projectDir));
            }

            if (empty($this->bootstrapConfig)) {
                $this->bootstrapConfig = $this->projectDir . '/conf/bootstrap.ini';
            }
            $this->bootstrapConfig = file_exists($this->bootstrapConfig) ?  $this->_adaptPath(realpath($this->bootstrapConfig)) : null;

            $this->view['coreDir']     = $this->coreDir;
            $this->view['isCoreDir']   = $isCoreDir;
            $this->view['isCoreUnder'] = $this->_isUnderDir($this->coreDir);

            $this->view['projectDir']     = $this->projectDir;
            $this->view['isProjectDir']   = $isProjectDir;
            $this->view['isProjectUnder'] = $this->_isUnderDir($this->projectDir);
            $this->view['isDefinedProjectDir'] = $this->isDefinedProjectDir;

            $this->view['bootstrapConfig'] = $this->bootstrapConfig;

            $result = !empty($this->coreDir) && !empty($this->projectDir);
        }

        $this->_parseTemplate('base_directories');

        return $result;
    }
    protected function _checkLogDirectories(): bool
    {
        $this->iniContent['bootstrap'] = file_get_contents($this->bootstrapConfig);

        $Matches = null;

        if (preg_match('/^\s*ini\.temp_dir\s*\=\s*\"(.+?)\"\s*$/m', $this->iniContent['bootstrap'], $matches)) {
            $this->tempDir = $this->_replacePlaceholder($matches[1]);
        }

        if (preg_match('/^\s*global_path\.config_source\s*\=\s*\"(.+?)\"\s*$/m', $this->iniContent['bootstrap'], $matches)) {
            $this->serviceConfig = $this->_replacePlaceholder($matches[1]);
        } else {
            $tmp = pathinfo($this->bootstrapConfig);
            $this->serviceConfig = $tmp['dirname'];
        }
        $this->serviceConfig .= '/service.ini';

        $this->iniContent['service'] = file_get_contents($this->serviceConfig);

        $result = true;
        $logDir = [
            'apache'    => ['file' => 'bootstrap', 'key' => 'global_path\\.apache_log'],
            'bootstrap' => ['file' => 'bootstrap', 'key' => 'global_path\\.bootstrap_log'],
            'data'      => ['file' => 'service',   'key' => 'LOG_DIR\\.data'],
            'error'     => ['file' => 'service',   'key' => 'LOG_DIR\\.error'],
            'message'   => ['file' => 'service',   'key' => 'LOG_DIR\\.message'],
        ];

        foreach ($logDir as &$v) {
            if (preg_match('/^\s*' . $v['key'] . '\s*\=\s*\"(.+?)\"\s*$/m', $this->iniContent[$v['file']], $Matches)) {
                $v['dir']      = $this->_replacePlaceholder($Matches[1]);
                $v['writable'] = is_dir($v['dir']) && is_writable($v['dir']);
                if (!$v['writable'] && !file_exists($v['dir'])) {
                    $tmp = pathinfo($v['dir']);
                    if (is_writable($tmp['dirname']) && mkdir($v['dir'])) {
                        $v['writable'] = true;
                    } else {
                        $v['parent'] = $tmp['dirname'];
                    }
                }
            }
            $result &= $v['writable'];
        }

        $this->view['logDir'] = $logDir;
        $this->_parseTemplate('log_directories');
        return $result;
    }
    protected function _checkCacheDirectories(): bool
    {
        $result = false;
        if (empty($this->tempDir)) {
            $isTmp = 0;
        } elseif (!is_dir($this->tempDir)) {
            $isTmp = -1;
        } else {
            $this->tempDir = realpath($this->tempDir);
            if (!is_writable($this->tempDir)) {
                $isTmp = -2;
            } else {
                $isTmp = 1;

                $result = true;
                $cacheDir = [
                    'config'       => ['required' =>  1, 'file' => 'bootstrap', 'key' => '\[config_cache\][^\[]+BASE_DIR'],
                    'template'     => ['required' =>  1, 'file' => 'service',   'key' => '\[template\][^\[]+CACHE_DIR'],
                    'entity'       => ['required' =>  0, 'file' => 'service',   'key' => '\[entity\][^\[]+CACHE_DIR'],
                    'service-data' => ['required' =>  0, 'file' => 'service',   'key' => '\[cache\.TYPE\.service_data\][^\[]+BASE_DIR'],
                    'file-store'   => ['required' =>  0, 'file' => 'service',   'key' => '\[cache\.TYPE\.file_store\][^\[]+BASE_DIR'],
                    'img-nail'     => ['required' =>  0, 'file' => 'service',   'key' => '\[cache\.TYPE\.img_nail\][^\[]+BASE_DIR'],
                    'common'       => ['required' => -1, 'file' => 'service',   'key' => '\[cache\.TYPE\.common_by_file\][^\[]+BASE_DIR'],
                ];

                $unset = [];
                foreach ($cacheDir as $k => &$v) {
                    if (preg_match('/\s*' . $v['key'] . '\s*\=\s*\"(.+?)\"\s*/', $this->iniContent[$v['file']], $Matches)) {
                        $v['dir']      = $this->_replacePlaceholder($Matches[1]);
                        $v['writable'] = is_dir($v['dir']) && is_writable($v['dir']);
                        if (!$v['writable'] && !file_exists($v['dir'])) {
                            if (mkdir($v['dir'], 0777, true)) {
                                $v['writable'] = true;
                            }
                        }
                    } else {
                        $v['writable'] = false;
                    }

                    if (!$v['writable'] && $v['required'] < 0) {
                        $unset[] = $k;
                    } else {
                        $v['img'] = $v['writable'] ? 'correct' : ($v['required'] > 0 ? 'incorrect' : 'need');
                    }
                    $result &= ($v['writable'] || $v['required'] < 1);
                }

                foreach ($unset as $k) {
                    unset($cacheDir[$k]);
                }
            }
        }

        $this->view['isTmp']    = $isTmp;
        $this->view['tempDir']  = $this->tempDir;
        $this->view['cacheDir'] = $cacheDir;

        $this->_parseTemplate('cache_directories');
        return $result;
    }

    protected function _findIndexDir(): ?string
    {
        $lenRoot = strlen($this->baseDir);
        $script  = pathinfo($_SERVER['SCRIPT_FILENAME']);
        $tmp = $this->_adaptPath($script['dirname']);

        if ($lenRoot >= strlen($tmp)) {
            return null;
        }
        $pos = strrpos($tmp, '/', $lenRoot);
        if (!$pos) {
            return null;
        }

        $indexDir = substr($tmp, 0, $pos);

        if ($this->_checkIndexFile($indexDir)) {
            return $indexDir;
        }
        return $this->_checkIndexFile($this->baseDir) ? $this->baseDir : null;
    }

    protected function _checkIndexFile(string $dir): bool
    {
        $path = $dir . '/index.php';
        if (is_file($path)) {
            $index   = file_get_contents($path);

            $matches1 = $matches2 = $matches3 = null;

            $res1 = preg_match('/require_once\s+.+?\'(.+\/_core)\/bootstrap\.php\'\;/', $index, $matches1);
            $res2 = preg_match('/\\\\bootstrap\:\:run\((?:.+\'(.+\/bootstrap\.ini)\')?\)\;/', $index, $matches2);

            if ($res1 && $res2) {
                $this->coreDir = $dir . $matches1[1];
                if (preg_match('/define\s*\(\s*\'PROJECT_DIR\'\s*\,.+?\'(.+)\'\)\;/', $index, $matches3)) {
                    $this->projectDir = $dir . $matches3[1];
                    $this->isDefinedProjectDir = true;
                } else {
                    $this->projectDir = $this->coreDir . '/../_project';
                }
                if (isset($matches2[1])) {
                    $tmp = $dir . $matches2[1];
                    $this->bootstrapConfig = file_exists($tmp) ?  realpath($tmp) : null;
                }
                return true;
            }
        }
        return false;
    }

    protected function _isUnderDir(string $dir): bool
    {
        $pos1 = strrpos($this->baseDir, '/');
        $pos2 = strrpos($dir, '/');
        return substr($this->baseDir, 0, $pos1) === substr($dir, 0, $pos2);
    }

    protected function _adaptPath(string $path): string
    {
        return $this->separator === '/' ? $path : str_replace($this->separator, '/', $path);
    }

    protected function _replacePlaceholder(string $path): string
    {
        foreach ($this->placeholderMap as $k => $v) {
            $path = str_replace('{' . $k . '}', $this->$v, $path);
        }
        return $path;
    }

    protected function _setConst(): static
    {
        foreach ($this->placeholderMap as $k => $v) {
            $name = 'FAN_' . $k;
            if (!empty($this->$v) && !defined($name)) {
                define($name, $this->$v);
            }
        }
        return $this;
    }
    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
