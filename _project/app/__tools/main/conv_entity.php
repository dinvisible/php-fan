<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * Covert entity from old to new format
 * @version 05.02.001 (10.03.2014)
 */
class conv_entity extends \fan\project\block\form\injector
{

    /**
     * Destination Directory
     * @var string
     */
    protected string $dstDir = '';

    /**
     * Role name form
     * @var string
     */
    protected string $roleName = '';

    /**
     * Extendet for class
     * @var array
     */
    protected array $ext = [
        'entity'  => '\fan\project\base\model\entity',
        'rowset'  => '\fan\project\base\model\rowset',
        'row'     => '\fan\project\base\model\row',
        'request' => '\fan\project\base\model\request',
    ];
    public function init(): void
    {
        $this->_parseForm();
    }

    protected function onSubmit(): void
    {
        $form = $this->getForm();
        $this->dstDir = rtrim($form->getFieldValue('dest_dir'), '/\\') . '/';
        if (is_dir($this->dstDir)) {
            $this->baseNs = $this->_getNameSpace($this->dstDir);
            if (empty($this->baseNs)) {
                throw new \RuntimeException('NameSpace is not defined.');
            } else {
                $srcDir = rtrim($form->getFieldValue('source_dir'), '/\\') . '/';
                $files  = $this->_makeFileList($srcDir, $form->getFieldValue('source_mask'));
                foreach ($files as $v) {
                    $newDir = $this->dstDir . $v['table_name'] . '/';
                    if (is_file($newDir)) {
                        throw new \RuntimeException('Such file: "' . $newDir . '" already exists.');
                    } elseif (is_dir($newDir)) {
                        throw new \RuntimeException('Such directory: "' . $newDir . '" already exists.');
                    } else {
                        mkdir($newDir, 0777, true);
                        $srcContent = file_get_contents($v['src_file']);

                        $rowContent = $this->_makeRowContent($srcContent, $v['src_file']);
                        $this->_createFile('row', $v['table_name'], $rowContent);

                        $entityContent = $this->_makeEntityContent($srcContent);
                        $this->_createFile('entity', $v['table_name'], $entityContent);


                        $aggrFile = $srcDir . '../aggr_entities/aggr_' . $v['src_class'];
                        if (is_file($aggrFile . '.php')) {
                            $requestContent = $this->_makeRequestContent(file_get_contents($aggrFile . '.php'));
                            $this->_createFile('request', $v['table_name'], $requestContent);
                            if (is_dir($aggrFile)) {
                                $sqlDir = $this->dstDir . $v['table_name'] . '/sql/';
                                mkdir($sqlDir, 0766);
                                foreach (scandir($aggrFile) as $v) {
                                    if ($v === '.' || $v === '..') {
                                        continue;
                                    }
                                    if (!copy($aggrFile . '/' . $v, $sqlDir . $v)) {
                                        throw new \RuntimeException('Can\'t copy SQL-file: "' . $aggrFile . '/' . $v . '".');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    protected function _makeFileList($path, $mask): array
    {
        $result = [];
        if (is_dir($path)) {
            foreach (scandir($path) as $v) {
                if ($v === '.' || $v === '..') {
                    continue;
                }

                $fullPath = $path . $v;
                $matches  = null;
                if (is_file($fullPath) && preg_match('/^' . $mask . '\.php$/', $v, $matches)) {
                    if (empty($matches[1])) {
                        throw new \InvalidArgumentException('Incorrect mask. Destination class is not set.');
                    }
                    $result[] = [
                        'src_class'  => substr($matches[0], 0, -4),
                        'table_name' => $matches[1],
                        'src_file'   => $fullPath
                    ];
                }
            }
        } else {
            throw new \RuntimeException('Incorrect sourse path: "' . $path . '".');
        }
        return $result;
    }

    protected function _getNameSpace($path): ?string
    {
        if (preg_match('/[\/\\\\]model[\/\\\\].*$/', $path, $matches)) {
            return 'project' . str_replace('/', '\\', $matches[0]);
        }
        return null;
    }

    protected function _makeRowContent(&$srcContent, $srcName): array
    {
        $rowContent = [
            'set/get' => ' ',
        ];
        $mainMatches = $matches = null;
        $srcContent = str_replace("\r", '', $srcContent);
        preg_match('/^\<\?php\n?\s*(?:\/\*\*(.*?)\s+\*\/)?.*?class\s+entity_.+?\{(.+?)\n\}.*\n\?\>\n?$/si', $srcContent, $mainMatches);

        // ----- comments of dynamic set/get methods ----- \\
        if (!empty($mainMatches[1])) {
            preg_match_all('/\s+\*\s*\@(?:version|method)\s.+?\n/i', $mainMatches[1], $matches); // PREG_SET_ORDER
            if (!empty($matches[0])) {
                $rowContent['comments'] = implode('', $matches[0]);
            }
        }

        if (!empty($mainMatches[2])) {
            $srcContent = trim($mainMatches[2]);
            $srcContent = preg_replace('/\s*\/\*\n\s+\*\s*[-=]{5,}\s*\[.+?\]\s*[-=]{5,}.*?\n\s+\*\//', '', $srcContent);
            $srcContent = preg_replace('/(?:\s*\/\*\*\n.+?\*\/\n)?\s+public\s+function\s+init\(\).+?\n\s+\}[\s\w\/]*(?:\n|$)/s', '', $srcContent);
            $srcContent = trim($srcContent);
        } else {
            throw new \UnexpectedValueException('Incorrect file structure "' . $srcName . '".');
        }

        if (empty($srcContent)) {
            return $rowContent;
        }

        // ----- set/get methods ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*public\sfunction\s(?:s|g)et_.+?\n\s{4}\{.+?\n\s{4}\}.*?(?:\n|$)/s', $srcContent, $matches);
        if (!empty($matches[0])) {
            $rowContent['set/get'] = implode('', $matches[0]);
            $this->_addSpace($rowContent['set/get'])->_removeUsed($srcContent, $matches[0]);
        }

        return $rowContent;
    }

    protected function _makeEntityContent(&$srcContent): array
    {
        $entityContent = [
            'public' => '',
        ];
        $matches = null;

        // ----- property ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:public|protected|private)\s+\$\w+.+?\;\n/s', $srcContent, $matches);
        if (!empty($matches[0])) {
            $entityContent['property'] = implode('', $matches[0]);

            $this->_addSpace($entityContent['property'])->_removeUsed($srcContent, $matches[0]);
        }

        if (empty($srcContent)) {
            return $entityContent;
        }

        // ----- static methods ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:public\s+static|static\s+public)\s+function\s.+?\n\s{4}\{.+?\n\s{4}\}.*?(?:\n|$)/s', $srcContent, $matches);
        if (!empty($matches[0])) {
            $entityContent['static'] = implode('', $matches[0]);
            $this->_addSpace($entityContent['static'])->_removeUsed($srcContent, $matches[0]);
        }

        if (empty($srcContent)) {
            return $entityContent;
        }

        // ----- protected methods ----- \\
        preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:protected|private)\s+function\s+(\w+).+?\n\s{4}\{.+?\n\s{4}\}.*?(?:\n|$)/s', $srcContent, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $k => &$v) {
                $srcContent = str_replace($v, '', $srcContent);
                $this->_addSpace($v, empty($k));

                if (substr($matches[1][$k], 0, 1) !== '_') {
                    $v = preg_replace('/\s+function\s+' . $matches[1][$k] . '/', ' function _' . $matches[1][$k], $srcContent);
                }
            }
            $entityContent['protected'] = implode('', $matches[0]);
            $srcContent = trim($srcContent);
        }

        // ----- public methods ----- \\
        $entityContent['public'] = $srcContent;

        return $entityContent;
    }

    protected function _makeRequestContent(&$srcContent): array
    {
        $requestContent = [];
        $srcContent = str_replace("\r", '', $srcContent);
        $mainMatches = $matches = null;
        if (preg_match('/^\<\?php\n?\s*(?:\/\*\*.*?\s+\*\/)?.*?class\s+aggr_entity_.+?\{(.+?)\n\}.*\n\?\>\n?$/si', $srcContent, $mainMatches)) {
            $srcContent = trim($mainMatches[1]);

            // ----- property ----- \\
            preg_match_all('/(?:\s*\/\*\*.+?\*\/\n)?\s*(?:public|protected|private)\s+\$\w+.+?\;\n/s', $srcContent, $matches);
            if (!empty($matches[0])) {
                $requestContent['property'] = implode('', $matches[0]);

                $this->_addSpace($requestContent['property'])->_removeUsed($srcContent, $matches[0]);
            }

            // ----- public methods ----- \\
            $requestContent['public'] = $srcContent;
        }
        return $requestContent;
    }

    protected function _createFile(string $class, string $tableName, $content): static
    {
        if (empty($content)) {
            return $this;
        }
        $path = $this->dstDir . $tableName . '/' . $class . '.php';
        $ns   = $this->baseNs . $tableName;

        file_put_contents($path, '<?php namespace \fan' . $ns . ';
/**
 * Description of ' . $class . '
' . (empty($content['comments']) ? '' : ' ' . trim($content['comments']) . "\n") . ' *
 * @author Name
 */
class ' . $class . ' extends ' . $this->ext[$class] . '
{
' . (empty($content['property']) ? '' :  "\n    " . trim($content['property']) . "\n") . '
' . (empty($content['set/get']) ? '' :  '
    /*
     * ================ [ Redefined methods AND set/get methods of row-data ] ================ *
     */
    ' . trim($content['set/get'])) . '
    /*
     * ============================== [ Static methods ] ============================== *
     */
' . (empty($content['static']) ? '' :  "\n    " . trim($content['static'])) . '
    /*
     * ========================== [ Special public methods ] ========================== *
     */
' . (empty($content['public']) ? '' :  "\n    " . trim($content['public'])) . '
    /*
     * ============================= [ Private/protected methods ] ============================ *
     */
' . (empty($content['protected']) ? '' :  "\n    " . trim($content['protected'])) . '
}
?>');
        return $this;
    }

    protected function _addSpace(&$content, bool $addCond = true): static
    {
        if ($addCond && substr($content, 0, 4) !== '    ') {
            $content = '    ' . $content;
        }
        return $this;
    }

    protected function _removeUsed(&$content, $texts): static
    {
        foreach ($texts as $v) {
            $content = str_replace($v, '', $content);
        }
        $content = trim($content);
        return $this;
    }

}
