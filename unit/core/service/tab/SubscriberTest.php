<?php

declare(strict_types=1);

use fan\core\service\tab\subscriber;
use FanTest\core\SourceFileContractTestCase;
use fan\core\block\base;


class ServiceTabSubscriberTest extends SourceFileContractTestCase
{
    protected const SOURCE_FILE = 'core/service/tab/subscriber.php';

    public function testBroadcastEventCallsAnyNameAndClassSubscribers(): void
    {
        $subscriber = new subscriber();
        $broadcaster = new ServiceTabSubscriberBlockDouble('main');
        $anyListener = new ServiceTabSubscriberBlockDouble('any-listener');
        $nameListener = new ServiceTabSubscriberBlockDouble('name-listener');
        $classListener = new ServiceTabSubscriberBlockDouble('class-listener');

        $subscriber
            ->subscribeForEvent($anyListener, 'ready', 'recordEvent')
            ->subscribeByName($nameListener, 'main', 'ready', 'recordEvent')
            ->subscribeByClass($classListener, ServiceTabSubscriberBlockDouble::class, 'ready', 'recordEvent');

        $this->assertSame($subscriber, $subscriber->broadcastEvent($broadcaster, 'ready', ['id' => 7]));

        foreach ([$anyListener, $nameListener, $classListener] as $listener) {
            $this->assertSame([
                [
                    'broadcaster' => $broadcaster,
                    'data' => ['id' => 7],
                ],
            ], $listener->events);
        }
    }

    public function testSubscribeReplacesDuplicateAndUnsubscribeStopsNameSubscriber(): void
    {
        $subscriber = new subscriber();
        $broadcaster = new ServiceTabSubscriberBlockDouble('main');
        $listener = new ServiceTabSubscriberBlockDouble('listener');

        $subscriber
            ->subscribeByName($listener, 'main', 'changed', 'recordEvent')
            ->subscribeByName($listener, 'main', 'changed', 'recordEvent')
            ->broadcastEvent($broadcaster, 'changed', ['first' => true]);

        $subscriber
            ->unSubscribeByName($listener, 'main', 'changed', 'recordEvent')
            ->broadcastEvent($broadcaster, 'changed', ['second' => true]);

        $this->assertSame([
            [
                'broadcaster' => $broadcaster,
                'data' => ['first' => true],
            ],
        ], $listener->events);
    }
}

final class ServiceTabSubscriberBlockDouble extends base
{
    public array $events = [];

    public function __construct(string $blockName)
    {
        $property = new ReflectionProperty(base::class, 'blockName');
        $property->setValue($this, $blockName);
    }

    public function recordEvent(base $broadcaster, array $data): void
    {
        $this->events[] = [
            'broadcaster' => $broadcaster,
            'data' => $data,
        ];
    }
}
