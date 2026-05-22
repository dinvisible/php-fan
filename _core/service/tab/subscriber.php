<?php
declare(strict_types=1);

namespace fan\core\service\tab;
/**
 * Description of subscriber
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
 * @version of file: 05.02.004 (25.12.2014)
 */
class subscriber extends \fan\core\service\tab\delegate
{
    /**
     * List of Subscriber by block name and by class (with namespace)
     * @var array
     */
    protected array $subscriber = ['any' => [], 'name' => [], 'class' => []];

    // ======== Static methods ======== \\
    // ======== Main Interface methods ======== \\

    /** @throws \fan\project\exception\service\fatal */
    public function subscribeForEvent(\fan\core\block\base $listener, string $eventName, string $listenerMethod = 'eventHandler'): static
    {
        return $this->_addSubscriber($listener, $listenerMethod, 'any', 0, $eventName);
    }

    /** @throws \fan\project\exception\service\fatal */
    public function subscribeByName(\fan\core\block\base $listener, string $broadcasterName, string $eventName, string $listenerMethod = 'eventHandler'): static
    {
        return $this->_addSubscriber($listener, $listenerMethod, 'name', $broadcasterName, $eventName);
    }

    /** @throws \fan\project\exception\service\fatal */
    public function subscribeByClass(\fan\core\block\base $listener, string $className, string $eventName, string $listenerMethod = 'eventHandler'): static
    {
        return $this->_addSubscriber($listener, $listenerMethod, 'class', trim($className, '\\'), $eventName);
    }

    public function unSubscribeByName(\fan\core\block\base $listener, string $broadcasterName, string $eventName, string $listenerMethod = 'eventHandler'): static
    {
        return $this->_removeSubscriber($listener, $listenerMethod, 'name', $broadcasterName, $eventName);
    }

    public function unSubscribeByClass(\fan\core\block\base $listener, string $className, string $eventName, string $listenerMethod = 'eventHandler'): static
    {
        return $this->_removeSubscriber($listener, $listenerMethod, 'class', trim($className, '\\'), $eventName);
    }

    public function broadcastEvent(\fan\core\block\base $broadcaster, string $eventName, array $data = []): static
    {
        $keys = [
            'any'   => 0,
            'name'  => $broadcaster->getBlockName(),
            'class' => get_class($broadcaster),
        ];
        foreach ($keys as $type => $key) {
            if (isset($this->subscriber[$type][$key][$eventName])) {
                foreach ($this->subscriber[$type][$key][$eventName] as $v) {
                    call_user_func($v, $broadcaster, $data);
                }
            }
        }
        return $this;
    }

    // ======== Private/Protected methods ======== \\

    /**
     * @throws \fan\project\exception\service\fatal
     */
    protected function _addSubscriber(\fan\core\block\base $listener, string $listenerMethod, string $type, int|string $key, string $eventName): static
    {
        if (!method_exists($listener, $listenerMethod) || !is_callable([$listener, $listenerMethod])) {
            $this->_makeException('Incorrect method name "' . $listenerMethod . '" in block "' . $listener->getBlockName() . '".');
        }
        $this->_removeSubscriber($listener, $listenerMethod, $type, $key, $eventName);

        if (!isset($this->subscriber[$type][$key][$eventName])) {
            $this->subscriber[$type][$key][$eventName] = [];
        }
        $this->subscriber[$type][$key][$eventName][] = [$listener, $listenerMethod];
        return $this;
    }

    protected function _removeSubscriber(\fan\core\block\base $listener, string $listenerMethod, string $type, int|string $key, string $eventName): static
    {
        if (isset($this->subscriber[$type][$key][$eventName])) {
            foreach ($this->subscriber[$type][$key][$eventName] as $k => $v) {
                if ($listener === $v[0] && (string)$listenerMethod === (string)$v[1]) {
                    unset($this->subscriber[$type][$key][$eventName][$k]);
                    return $this;
                }
            }
        }
        return $this;
    }

    // ======== The magic methods ======== \\
    // ======== Required Interface methods ======== \\
}
