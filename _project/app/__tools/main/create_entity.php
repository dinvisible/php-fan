<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * create_entity block
 * @version 05.02.001 (10.03.2014)
 */
class create_entity extends \fan\project\block\form\injector
{

    /**
     * Object of database-service
     * @var \fan\core\service\database
     */
    protected ?object $db = null;

    /**
     * Entity dir
     * @var string
     */
    protected string $ettDir = '';

    public function init(): void
    {
        $tableList = [];

        $req = $this->containerService('request');
        /* @var $req \fan\core\service\request */
        $con  = $req->get('connection',   'G');
        $nsPr = $req->get('ns_pref',      'G');
        $re   = $req->get('table_regexp', 'G');

        if (!empty($con) && !empty($nsPr)) {
            $this->ettDir = \bootstrap::getLoader()->getPathByNS($nsPr);
            if (!empty($this->ettDir)) {
                $this->db = $this->containerService('database', $con);
                $this->_parseForm();

                $this->db->setResultTypes(MYSQL_NUM);
                $tmp = $this->db->getCol('SHOW TABLES', 0);
                if (!empty($re)) {
                    foreach ($tmp as $v) {
                        if (preg_match($re, $v)) {
                            $tableList[] = $v;
                        }
                    }
                } else {
                    $tableList = $tmp;
                }
                $tableList = array_flip($tableList);
                ksort($tableList);
                $sep  = \fan\core\bootstrap\loader::DEFAULT_DIR_SEPARATOR;

                foreach ($tableList as $tableName => &$v) {
                    $dir = $this->ettDir . $sep . $tableName;
                    if (is_dir($dir)) {
                        if (!is_file($dir . $sep . 'entity.php')) {
                            $v = ['red', 'File of entity-class is not set.'];
                        } elseif (!is_file($dir . $sep . 'row.php')) {
                            $v = ['yellow', 'File of row-class is not set.'];
                        } else {
                            $v = [];
                        }
                    } else {
                        $v = null;
                    }
                }
            }
        }
        /*
        */
        $this->view['CurrentDb']  =  $con;
        $this->view['aTableList'] = $tableList;
    }

    protected function onSubmit(): void
    {
        $nsPr = trim($this->containerService('request')->get('ns_pref', 'G'), '\\');
        $sep  = \fan\core\bootstrap\loader::DEFAULT_DIR_SEPARATOR;
        $tbl = $this->getForm()->getFieldValue('tbl');
        if (!empty($tbl)) {
            foreach ($tbl as $tableName => $v) {
                $dir = $this->ettDir . $sep . $tableName;
                if (!is_dir($dir)) {
                    //$param = $this->getParamByDb($tableName);

                    mkdir($dir);
                    file_put_contents ($dir . $sep . 'entity.php' , '<?php namespace ' . $nsPr . '\\' . $tableName . ';
/**
 * Entity of `' . $tableName . '` table
 * @version 1.0
 */
class entity extends \fan\project\base\model\entity
{

    /*
     * ============================== [ Static methods ] ============================== *
     */

    /*
     * ========================== [ Special public methods ] ========================== *
     */

    /*
     * ============================= [ Private/protected methods ] ============================ *
     */

}
?>');

                    file_put_contents ($dir . $sep . 'row.php' , '<?php namespace ' . $nsPr . '\\' . $tableName . ';
/**
 * Row of `' . $tableName . '` table' . $this->getMethodList($tableName) . '
 * @version 1.0
 */
class row extends \fan\project\base\model\row
{

    /*
     * ================ [ Redefined methods AND set/get methods of row-data ] ================ *
     */

    /*
     * ============================== [ Static methods ] ============================== *
     */

    /*
     * ========================== [ Special public methods ] ========================== *
     */

    /*
     * ============================= [ Private/protected methods ] ============================ *
     */

}
?>');
                }
            }
        }
    }

    protected function getMethodList($tableName): string
    {
        $ret = '';
        foreach ($this->getFields($tableName) as $v) {
            if (strstr($v['Type'], 'char') || strstr($v['Type'], 'date') || strstr($v['Type'], 'enum')) {
                $type = 'string';
            } elseif (strstr($v['Type'], 'int')) {
                $type = 'integer';
            } elseif (strstr($v['Type'], 'float')) {
                $type = 'float';
            } else {
                $type = 'mixed';
            }
            $ret .= "\n" . ' * @method void set_' . $v['Field'] . '()';
            $ret .= "\n" . ' * @method ' . $type . ' get_' . $v['Field'] . '()';
        }

        return $ret;
    }
    protected function getParamByDb($tableName): array
    {
        $index = [];
        foreach ($this->getFields($tableName) as $v) {
            if ((string)$v['Key'] === 'PRI') {
                $index[] = $v['Field'];
            }
        }

        $tmp = $this->db->getRow('SHOW CREATE TABLE `' . $tableName . '`');
        $crt = $tmp['Create Table'];
        $topKeys = [];
        if (preg_match_all('/FOREIGN\s+KEY\s*\(\`?(\w+)\`?\)\s*REFERENCES\s+\`?(\w+)\`?\s+\(\`?(\w+)\`?\)/im', $crt, $matches) && !empty($matches[0])) {
            foreach ($matches[1] as $k => $field) {
                $topKeys[$field] = $matches[2][$k];
            }
        }

        return [
            'primary'  => count($index) < 2 ? ($index[0] ?? null) : $index,
            'top_keys' => $topKeys,
        ];
    }

    protected function getFields(string $tableName): array
    {
        return $this->db->getAll('DESCRIBE `' . $tableName . '`');
    }
}
