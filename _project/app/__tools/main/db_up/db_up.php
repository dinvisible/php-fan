<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * db_up block
 * @version 05.02.001 (10.03.2014)
 */
class db_up extends \fan\project\block\form\injector
{
    public function init(): void
    {
        \fan\project\adapter\project_tool_loader::load(scenario::class, __DIR__ . '/scenario.php');

        $this->fieldMeta["scenario"]["data"] = [];

        $scList = [];
        $dirPath = \bootstrap::parsePath($this->getMeta("scenario_dir"));
        if (is_dir($dirPath)) {
            $dir = dir($dirPath);
            while (($entry = $dir->read()) !== false) {
                if (substr($entry, -4) === ".txt") {
                    $key = substr($entry, 0, -4);
                    $sc = new scenario($dirPath . $entry);
                    list($k, $v) = $sc->get_next();
                    $scList[$key] = (string)$k === "description" ? $v : $key;
                }
            }
            $dir->close();
        }
        if ($scList) {
            asort($scList);
            foreach ($scList as $k => $v) {
                $this->fieldMeta["scenario"]["data"][] = ["value" => $k, "text" => $v];
            }

            $this->_parseForm();
            $this->view->isCorrect = 1;
        } else {
            $realDirPath = realpath($dirPath);
            $this->view->dirPath = $realDirPath ? $realDirPath : $this->getMeta("scenario_dir");
        }
    }
}
