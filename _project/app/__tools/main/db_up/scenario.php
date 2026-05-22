<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * Parse scenario
 * @version 05.02.001 (10.03.2014)
 */
class scenario
{
    use \fan\core\di\container_aware_trait;

    protected ?array $scData0 = null;
    protected ?array $scData1 = null;

    /**
     * @var number
     */
    protected int|float $index = 0;

    protected ?object $db = null;

    /**
     * Current sql query
     * @var unknown_type
     */
    private ?string $currentQuery = null;

    /**
     * Dump directory path
     * @var string
     */
    private ?string $dumpdir = null;

    /**
     * Successful operation
     * @var boolean
     */
    private bool $success = true;

    /**
     * @param mixed $file File path or file descriptor handled by the operation.
     */
    public function __construct($file, $dumpdir = null) {
        $this->scData0 = file($file);
        $this->dumpdir = $dumpdir;
        foreach ($this->scData0 as $row) {
            $row = trim($row);
            if ($row !== '' && substr($row, 0, 1) !== '#') {
                $this->scData[] = $row;
            }
        }
    }

    /**
     * @return mixed Returns null after reporting the unknown method.
     */
    public function __call($m, $a): mixed {
        echo '<div>Unknown method <b>' . $m . '</b></div>\n';
        return null;
    }

    public function isSuccess(): bool {
        return $this->success;
    }

    public function get_next(): array {
        if ($this->index >= count($this->scData)) {
            $ret = [null,null];
        } else {
            $ret = explode(':', $this->scData[$this->index], 2);
            $ret[0] = trim(strtolower($ret[0]));
            $ret[1] = trim($ret[1]);
            $this->index++;
        }
        return $ret;
    }

    /**
     * Transforms scenario between supported representations.
     */
    public function parse_scenario(): void {
        do {
            list ($k, $v) = $this->get_next();
            if ($k) {
                $command = 'command_' . $k;
                if (!method_exists($this, $command)) {
                    echo '<div class="sc_error"><h2>Error! Unknown scenario\'s command ' . $k . '</h2></div>';
                    $this->success = false;
                    break;
                }
                try {
                    $this->$command($v);
                } catch (Exception $e) {
                    echo '<div class="sc_error">' . $e->getMessage(), "</div>\n";
                    $this->success = false;
                    break;
                }
                flush();
            }
        } while ($k);
    }



//----------------------------------------------
    public function command_description($name): void {
        echo '<h2>' . $name . "</h2>\n";
    }

    public function command_connect($name): void {
        echo '<h3>Connect to DB <i>' . $name . "</i></h3>\n";
        $this->db = null;
        $this->db = $this->containerService('database', $name);
        if (!$this->db) {
            throw new Exception('<h2>Error! No DB-connection!</h2>');
        }
        $this->containerService('config')->set('service_database', 'SQL_LNG_CORRECTION', false);
        $this->check_sql_error();
    }

    public function command_commit(): void {
        echo "<h3>Commit data.</h3>\n";
        $this->db->commit();
    }

    public function command_clear_tables(): void {
        echo "<h3>Clear all database tables.</h3>\n";

        $tableList = $this->get_table_list('Deleted tables');

        $this->clear_fk($tableList);
        foreach ($tableList as $table) {
            $this->execute('DROP TABLE `' . $table . '`');
        }
        $this->db->commit();
    }

    public function command_clear_fk(): void {
        echo "<h3>Clear Foreign Keys in all tables.</h3>\n";

        $tableList = $this->get_table_list('Clear FK in tables');

        $this->clear_fk($tableList);
    }

    /**
     * @param mixed $file File path or file descriptor handled by the operation.
     */
    public function command_sql_file($file): void {
        echo '<h3>File <i>' . $file . "</i></h3>\n";
        if (!file_exists($this->dumpdir . $file)) {
            throw new Exception('<h2>Error! No file <i>' . $file . '</i></h2>');
        }
        $file = trim(file_get_contents($this->dumpdir . $file));
        $file = str_replace("\r\n", "\n", $file);
        $eof = false;
        $k=1;
        while ($file !== '' && $file !== '--') {
            if (substr($file, 0, 3) === '-- ') {
                $file = (string)strstr($file, "\n");
            } else {
                $pos = strpos($file, ";\n");
                if ($pos === false) {
                    $pos = substr($file,-1) === ';' ? strlen($file) - 1 : strlen($file);
                    $eof = true;
                } elseif ($pos === 0){
                    $file = substr($file,1);
                    continue;
                }
                echo '<div>SQL-request #', $k++, '</div>';
                if ($k%10 === 0) {
                    flush();
                }

                $this->execute(substr($file,0,$pos));
                $file = $eof ? '' : substr($file, $pos+1);
            }
            $file = trim($file);
        }

    }

    public function command_sql_query($query): void {
        echo "<h3>Scenario's query</h3>\n";
        $this->execute($query);
    }

//----------------------------------------------
    protected function execute($query, $param = []): void {
        $this->currentQuery = $query;
        $this->db->execute($query, $param);
        $this->check_sql_error();
    }

    protected function check_sql_error(): void {
        $err = $this->db->get_error_message();
        if ((string)$err !== '') {
            throw new Exception('<h2>Error! ' . $err . '</h2><pre>' . $this->currentQuery . '</pre>');
        }
    }

    protected function get_table_list($show = ''): array {
        $tableList = $this->db->get_col('SHOW TABLES');
        asort($tableList);
        if ($show) {
            echo '<p>' . $show . ':</p><pre>';
            print_r($tableList);
            echo '</pre>';
            flush();
        }
        return $tableList;
    }

    protected function clear_fk($tableList): void {
        foreach ($tableList as $table) {
            $ct = $this->db->get_row('SHOW CREATE TABLE `' . $table . '`');
            if (preg_match_all('/CONSTRAINT\s+\`(.+?)\`\s+FOREIGN KEY\s+/i', $ct['Create Table'], $keys)) {
                foreach ($keys[1] as $key) {
                    $this->execute('ALTER TABLE `' . $table . '` DROP FOREIGN KEY `' . $key . '`');
                }
            }
        }
        $this->db->commit();
    }

}
