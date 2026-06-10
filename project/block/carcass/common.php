<?php

declare(strict_types=1);

namespace fan\project\block\carcass;
use fan\project\block\base;

/**
 * Block of carcass
 * @version of file: 05.02.001 (10.03.2014)
 */
class common extends base
{

    /**
     * Special message before main content
     * @var string
     */
    protected string $messBefore = '';

    /**
     * Special message after main content
     * @var string
     */
    protected string $messAfter = '';

    public function setMessageBefore(string $mess, int|float $position = 1, string $type = 'error'): void
    {
        if ($mess) {
            $mess = '<div class="' . $type . 'Msg">' . $mess . '</div>';
            $this->messBefore = !$position ? $mess : ($position > 0 ? $this->messBefore . $mess : $mess . $this->messBefore);
            if ($this->messBefore) {
                $this->_setViewVar('messBefore', '<div id="messBefore">' . $this->messBefore . '</div>');
            }
        }
    }

    public function setMessageAfter(string $mess, int|float $position = 1, string $type = 'error'): void
    {
        if ($mess) {
            $mess = '<div class="' . $type . 'Msg">' . $mess . '</div>';
            $this->messAfter = !$position ? $mess : ($position > 0 ? $this->messAfter . $mess : $mess . $this->messAfter);
            if ($this->messAfter) {
                $this->_setViewVar('messAfter', '<div id="messAfter">' . $this->messAfter . '</div>');
            }
        }
    }
}
