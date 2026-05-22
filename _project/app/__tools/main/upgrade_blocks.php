<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * Upgrade blocks
 * @version 05.02.007 (31.08.2015)
 */
class upgrade_blocks extends \fan\project\block\common\simple
{
    /**
     * Base Path to upgraded directory
     * @var string
     */
    protected string $basePath = '';

    protected array $fileStruct = [
        'php'  => [],
        'meta' => [],
        'tpl'  => [],
    ];

    protected array $content = [
        'php'  => [],
        'meta' => [],
        'tpl'  => [],
    ];

    protected array $changed = [];

    /**
     * Quantity of Not writeble files
     * @var array
     */
    protected array $notWr = [
        'php'  => 0,
        'meta' => 0,
        'tpl'  => 0,
    ];

    /**
     * Quantity of Added namespaces
     * @var array
     */
    protected array $nsAdded = [0, 0, 0];

    /**
     * Quantity of Added namespaces
     * @var array
     */
    protected array $extSet = [0, 0, 0];

    /**
     * Service Calls
     * @var array
     */
    protected array $serviceCalls = [0, 0, 0];

    /**
     * Entity Set
     * @var array
     */
    protected array $entitySet = [0, 0, 0];

    /**
     * Direct replacement
     * @var array
     */
    protected array $dirReplace = [
        'php'  => 0,
        'meta' => 0,
        'tpl'  => 0,
    ];

    /**
     * Quantity of Set Final Coment
     * @var array
     */
    protected array $finalComent = [0, 0, 0];



    public function init(): void
    {
        $this->basePath = \bootstrap::parsePath($this->meta['src']['path']);


        // Add namespace
        $this->_addNamespase($this->_getFileList('php'), $this->meta['src']['ns']);
        $this->view->nsAdded = $this->nsAdded;

        // Set Extends
        $this->_setExtends();
        $this->view->extSet = $this->extSet;

        // Set Service calls
        $this->_setServiceCalls();
        $this->view->serviceCalls = $this->serviceCalls;

        // Set Entity
        $this->_setEntityOperations();
        $this->view->entitySet = $this->entitySet;

        // Set Direct Replacement
        $this->_directReplacement();
        $this->view->dirReplace = $this->dirReplace;

        // Set Final Coment
        $this->_setFinalComent($this->_getFileList('php'), '\\' . $this->meta['src']['ns'] . '\\');
        $this->view->finalComent = $this->finalComent;



        // Save changed files
        $changed = $this->_saveFiles();

        // Summary info
        $this->view->changed = $changed;
        $this->view->notWr   = $this->notWr;
    }

    // ======= Main convert methods ======= \\
    protected function _addNamespase(array $data, string $nsPref): void
    {
        $matches = null;
        if (preg_match('/^(fan\\\\app(?:\\\\[^\\\\]+){2})\\\\/', $nsPref, $matches)) {
            $nsPref = $matches[1];
        }
        $ns = '<?php namespace ' . $nsPref;
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->_addNamespase($v, $nsPref . '\\' . $k);
            } else {
                $content = $this->content['php'][$v];
                if (strstr($content, 'class ')) {
                    if (preg_match('/^\<\?php\s+namespace\s+\w+/', $content)) {
                        $this->nsAdded[1]++;
                    } else {
                        $count = 0;
                        $content = preg_replace('/^\<\?php\s*\r?\n/', $ns . ";\n", $content, 1, $count);
                        if ($count > 0) {
                            $this->content['php'][$v] = $content;
                            $this->changed[$v] = 'php';
                            $this->nsAdded[0]++;
                        } else {
                            $this->nsAdded[2]++;
                            throw new \RuntimeException('Can\'t find first tag in file "' . $v . '".');
                        }
                    }
                }
            }
        }
    }

    protected function _setExtends(): void
    {
        $corr = $this->meta['src']['extends'];
        foreach ($this->_getContent('php') as $k => $v) {
            $matches = null;
            if (preg_match('/class\s+(\w+)(\s+extends\s+([\w\\\\]+))?\s*\{[\r\n]*/', $v, $matches)) {
                if (!empty($matches[2])) {
                    if (strstr($matches[3], '\\')) {
                        $this->extSet[1]++;
                    } else {
                        if (isset($corr[$matches[3]])) {
                            $this->content['php'][$k] = str_replace($matches[0], 'class ' . $matches[1] . ' extends ' . $corr[$matches[3]] . "\n{\n", $v);
                            $this->changed[$k] = 'php';
                            $this->extSet[0]++;
                        } else {
                            $this->extSet[2]++;
                            throw new \UnexpectedValueException('Don\'t know extends "' . $matches[3] . '" in file "' . $k . '".');
                        }
                    }
                }
            } elseif (!empty($matches[2])) {
                $this->extSet[2]++;
                throw new \UnexpectedValueException('Can\'t recognize "extends" in file "' . $k . '".');
            }
        }
    }

    protected function _setServiceCalls(): void
    {
        foreach (['php', 'meta'] as $type) {
            foreach ($this->_getContent($type) as $k => $v) {
                $matches = null;
                if (preg_match_all('/(?<=[\s\n=])service_(\w+)\:\:(instance\(([^\)]*)\))?/', $v, $matches, PREG_SET_ORDER)) {
                    $changed = false;
                    $error   = false;
                    foreach ($matches as $p) {
                        if (empty($p[2])) {
                            throw new \UnexpectedValueException('Can\'t convert Service-call "' . $p[0] . '" in file "' . $k . '".');
                        } else {
                            $replacement = 'service(\'' . $p[1] . '\'' . (empty($p[3]) ? '' : ', ' . $p[3]) . ')';
                            $this->content[$type][$k] = $v = str_replace($p[0], $replacement, $v);
                            $this->changed[$k] = $type;
                            l(htmlspecialchars($p[0]) . '<br /><br />' . htmlspecialchars($replacement), 'Replace service ' . $p[1], $k);
                            $changed = true;
                        }
                    }

                    $this->serviceCalls[$changed ? ($error ? 2 : 0) : 1]++;
                }
            }
        }
    }

    protected function _setEntityOperations(): void
    {
        foreach (['php', 'meta'] as $type) {
            foreach ($this->_getContent($type) as $k => $v) {
                $matches = null;
                if (preg_match_all('/(?<=[\s\n=])(se|le)\s*\(\s*(?:(\\\'|\")(\w+)\2|[^,\)])\s*(?:\,\s*([^\)]+))?\)([^;]+)?\;/', $v, $matches, PREG_SET_ORDER)) {
                    $changed = false;
                    $error   = false;
                    foreach ($matches as $p) {
                        $replacement = '';
                        if (!empty($p[3]) && substr($p[3], 0, 7) === 'entity_') {
                            $name = substr($p[3], 7);
                            if ((string)$p[1] === 'le') {
                                $replacement = 'gr(\'' . $name . '\'' . (empty($p[4]) ? '' : ', ' . $p[4]) . ')' . (empty($p[5]) ? '' : $p[5]) . ';';
                            } else {
                                $methods = null;
                                if (preg_match('/^\-\>getAggr\(\)\-\>(getEntitiesSimple|getOneEntityByKey|getArrayHash|getArrayHashByKey|getArrayColumn|getCountByParam)\s*\(\s*(.*)\s*\)[\r\n\s]*$/s', $p[5], $methods)) {
                                    $arg = empty($methods[2]) ? '' : $methods[2];
                                    switch ($methods[1]) {
                                    case 'getEntitiesSimple':
                                        $replacement = 'ge(\'' . $name . '\')->getRowsetByParam(' . $arg . ');';
                                        break;
                                    case 'getOneEntityByKey':
                                        $tmp = explode_alt(',', $arg, 3);
                                        $replacement = 'ge(\'' . $name . '\')->getRowByKey(' . trim($tmp[0]) . ', ' . $tmp[1] . ', ' . ltrim($tmp[2]) . ');';
                                        break;
                                    case 'getArrayHash':
                                        $tmp = explode(',', $arg, 3);
                                        $replacement = 'ge(\'' . $name . '\')->getRowsetByParam(' . ltrim($tmp[2]) . ')->getArrayHash(' . trim($tmp[0]) . ', ' . trim($tmp[1]) . ');';
                                        break;
                                    case 'getArrayHashByKey':
                                        $tmp = explode(',', $arg, 4);
                                        $replacement = 'ge(\'' . $name . '\')->getRowsetByKey(' . trim($tmp[0]) . ', ' . ltrim($tmp[3]) . ')->getArrayHash(' . trim($tmp[1]) . ', ' . trim($tmp[2]) . ');';
                                        break;
                                    case 'getArrayColumn':
                                        $tmp = explode(',', $arg, 2);
                                        $replacement = 'ge(\'' . $name . '\')->getRowsetByParam(' . (empty($tmp[1]) ? '' : $tmp[1]) . ')->getColumn(' . trim($tmp[0]) . ');';
                                        break;
                                    case 'getCountByParam':
                                        $replacement = 'ge(\'' . $name . '\')->getRowsetByParam(' . $arg . ')->count();';
                                        break;
                                    }
                                } else if (preg_match('/^\-\>(loadById|loadByParam|loadOrCreate)\s*\(\s*(.*)\s*\)[\r\n\s]*$/s', $p[5], $methods)) {
                                    $arg = empty($methods[2]) ? '' : $methods[2];
                                    switch ($methods[1]) {
                                    case 'loadById':
                                        $replacement = 'ge(\'' . $name . '\')->getRowById(' . $arg . ');';
                                        break;
                                    case 'loadByParam':
                                        $replacement = 'ge(\'' . $name . '\')->getRowByParam(' . $arg . ');';
                                        break;
                                    case 'loadOrCreate':
                                        $replacement = 'ge(\'' . $name . '\')->getRowOrCreate(' . $arg . ');';
                                        break;
                                    }
                                    //$t = ge($name)->getRowByParam();
                                } else {
                                    throw new \UnexpectedValueException('Unrecognized value: ' . $p[5]);
                                }
                            }
                        }

                        if (empty($replacement)) {
                            throw new \UnexpectedValueException('Can\'t convert entity-call "' . $p[0] . '" in file "' . $k . '".');
                        } else {
                            $this->content[$type][$k] = $v = str_replace($p[0], $replacement, $v);
                            $this->changed[$k] = $type;
                            l(htmlspecialchars($p[0]) . '<br /><br />' . htmlspecialchars($replacement), 'Replace ' . $p[1], $k);
                            $changed = true;
                        }
                    }

                    $this->entitySet[$changed ? ($error ? 2 : 0) : 1]++;
                }
            }
        }
    }

    protected function _directReplacement(): void
    {
        foreach ($this->meta['src']['direct_replace'] as $k => $v) {
            if (count($v) > 0) {
                foreach ($this->_getContent($k) as $path => $content) {
                    $isChange = false;
                    foreach ($v as $pattern => $replacement) {
                        $count   = 0;
                        $content = preg_replace($pattern, $replacement, $content, -1, $count);
                        if ($count > 0) {
                            l(htmlspecialchars($pattern) . '<br /><br />' . htmlspecialchars($replacement), 'Direct replace', $k);
                            $this->content[$k][$path] = $content;
                            $isChange = true;
                        }
                    }
                    if ($isChange) {
                        $this->changed[$path] = $k;
                        $this->dirReplace[$k]++;
                    }
                }
            }
        }
    }

    protected function _setFinalComent($data, $nsPref): void
    {
        $regexp = '/\}\s*(\/\/[\w\s\\\\]+)?\s*\r?\n\s*\?\>[\r\n\s]*$/';
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $this->_setFinalComent($v, $nsPref . $k . '\\');
            } else {
                $content = $this->content['php'][$v];
                if (strstr($content, 'class ')) {
                    $matches = null;
                    if (preg_match($regexp, $content, $matches)) {
                        if (!empty($matches[1]) && strstr($matches[1], '\\')) {
                            $this->finalComent[1]++;
                        } else {
                            $matches2 = null;
                            if (preg_match('/^(\\\\fan\\\\app\\\\(?:[^\\\\]+\\\\){2}).+/', $nsPref, $matches2)) {
                                $nsPref = $matches2[1];
                            }
                            $content = str_replace($matches[0], "}\n?>", $content);
                            $this->content['php'][$v] = $content;
                            $this->changed[$v] = 'php';
                            $this->finalComent[0]++;
                        }
                    } else {
                        $this->finalComent[2]++;
                        throw new \RuntimeException('Can\'t find final tag in file "' . $v . '".');
                    }
                }
            }
        }
    }


    // --------- Auxiliary methods --------- \\
    protected function _getFileList($type): array
    {
        if (empty($this->fileStruct[$type])) {
            $this->_makeFileList($this->fileStruct[$type], $type, $this->basePath);
        }
        return $this->fileStruct[$type];
    }

    protected function _getContent($type): array
    {
        if (empty($this->content[$type])) {
            $this->_makeFileList($this->fileStruct[$type], $type, $this->basePath);
        }
        return $this->content[$type];
    }

    protected function _makeFileList(&$dest, string $type, string $basePath): static
    {
        if (is_dir($basePath)) {
            foreach (scandir($basePath) as $v) {
                if ($v === '.' || $v === '..') {
                    continue;
                }

                $fullPath = $basePath . '/' . $v;
                if (is_dir($fullPath)) {
                    $dest[$v] = [];
                    $this->_makeFileList($dest[$v], $type, $fullPath);
                } elseif ($this->_checkType($v, $type)) {
                    if (!is_writable($fullPath)) {
                        $this->notWr[$type]++;
                        throw new \RuntimeException('File "' . $fullPath . '" is not writable.');
                    } else {
                        $dest[$v] = $fullPath;
                        $this->content[$type][$fullPath] = file_get_contents($fullPath);
                    }
                }
            }
        } else {
            throw new \RuntimeException('Incorrect Base Path: "' . $basePath . '".');
        }
        return $this;
    }

    protected function _saveFiles(): array
    {
        $result = [
            'php'  => 0,
            'meta' => 0,
            'tpl'  => 0,
        ];
        foreach ($this->changed as $path => $type) {
            file_put_contents($path, $this->content[$type][$path]);
            $result[$type]++;
        }
        return $result;
    }

    protected function _checkType(string $name, string $type): bool
    {
        if ($type === 'meta') {
            return substr($name, -9) === '.meta.php';
        } elseif (substr($name, -9) !== '.meta.php') {
            return substr($name, -strlen($type) - 1) === '.' . $type;
        }
        return false;
    }

}
