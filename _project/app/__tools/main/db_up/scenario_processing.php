<?php

declare(strict_types=1);

namespace fan\app\__tools\main;
/**
 * Scenario processing block
 * @version 05.02.001 (10.03.2014)
 */
class scenario_processing extends \fan\project\block\common\simple
{
    protected ?string $scenarioFile = null;

    public function init(): void
    {
        if (!role('tools_access')) {
            return;
        }

        \fan\project\adapter\project_tool_loader::load(scenario::class, __DIR__ . '/scenario.php');

        $this->scenarioFile = $this->containerService('request')->get('scenario', 'GP', null);
        if ($this->scenarioFile) {
            $this->scenarioFile = \bootstrap::parsePath($this->getMeta('scenario_dir') . $this->scenarioFile . '.txt');
            if (!file_exists($this->scenarioFile)) {
                $this->scenarioFile = null;
            }
        }
        if ($this->scenarioFile) {
            $this->tab->setOutputtingMethod($this, 'perform_db');
            $this->view->isCorrect = 1;
        } else {
            $this->view->isCorrect = 0;
        }
    }

    public function perform_db(): void
    {
        $code = explode('<!-- Repl -->', $this->tab->getTabCode());
        echo $code[0];

        $sc = new scenario($this->scenarioFile, \bootstrap::parsePath($this->getMeta('dump_dir')));
        $sc->parse_scenario();
        echo '<div id="finish"><h4 class="' . ($sc->isSuccess() ? 'success' : 'error') . '">Process is finished ' . ($sc->isSuccess() ? 'successfully' : 'with error') . '. Click <a href="about:blank">here</a> for clear result.</h4></div>';

        echo $code[1];
    }
}
