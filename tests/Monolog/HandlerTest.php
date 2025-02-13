<?php

declare(strict_types=1);

namespace Sentry\Tests\Monolog;

use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sentry\ClientInterface;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Monolog\Handler;
use Sentry\Severity;
use Sentry\State\Hub;
use Sentry\State\Scope;

final class HandlerTest extends TestCase
{
    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle(bool $fillExtraContext, array $record, Event $expectedEvent, EventHint $expectedHint, array $expectedExtra): void
    {
        /** @var ClientInterface&MockObject $client */
        $client = $this->createMock(ClientInterface::class);
        $client->expects($this->once())
            ->method('captureEvent')
            ->with(
                $this->callback(function (Event $event) use ($expectedEvent): bool {
                    $this->assertEquals($expectedEvent->getLevel(), $event->getLevel());
                    $this->assertSame($expectedEvent->getMessage(), $event->getMessage());
                    $this->assertSame($expectedEvent->getLogger(), $event->getLogger());

                    return true;
                }),
                $expectedHint,
                $this->callback(function (Scope $scopeArg) use ($expectedExtra): bool {
                    $event = $scopeArg->applyToEvent(Event::createEvent());

                    $this->assertNotNull($event);
                    $this->assertSame($expectedExtra, $event->getExtra());

                    return true;
                })
            );

        $handler = new Handler(new Hub($client, new Scope()), Logger::DEBUG, true, $fillExtraContext);
        $handler->handle($record);
    }

    public function handleDataProvider(): iterable
    {
        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::debug());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::DEBUG,
                'level_name' => Logger::getLevelName(Logger::DEBUG),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::DEBUG),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::info());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::INFO,
                'level_name' => Logger::getLevelName(Logger::INFO),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::INFO),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::info());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::NOTICE,
                'level_name' => Logger::getLevelName(Logger::NOTICE),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::NOTICE),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::warning());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::WARNING),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::error());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::ERROR,
                'level_name' => Logger::getLevelName(Logger::ERROR),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::ERROR),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::fatal());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::CRITICAL,
                'level_name' => Logger::getLevelName(Logger::CRITICAL),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::CRITICAL),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::fatal());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::ALERT,
                'level_name' => Logger::getLevelName(Logger::ALERT),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::ALERT),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::fatal());

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::EMERGENCY,
                'level_name' => Logger::getLevelName(Logger::EMERGENCY),
                'channel' => 'channel.foo',
                'context' => [],
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::EMERGENCY),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::warning());

        $exampleException = new \Exception('exception message');

        yield [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'context' => [
                    'exception' => $exampleException,
                ],
                'channel' => 'channel.foo',
                'extra' => [],
            ],
            $event,
            EventHint::fromArray([
                'exception' => $exampleException,
            ]),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::WARNING),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::warning());

        yield 'Monolog\'s context is filled and the handler should fill the "extra" context' => [
            true,
            [
                'message' => 'foo bar',
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'context' => [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
                'channel' => 'channel.foo',
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::WARNING),
                'monolog.context' => [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::warning());

        yield 'Monolog\'s context is filled with "exception" field and the handler should fill the "extra" context' => [
            true,
            [
                'message' => 'foo bar',
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'context' => [
                    'exception' => new \Exception('exception message'),
                ],
                'channel' => 'channel.foo',
                'extra' => [],
            ],
            $event,
            EventHint::fromArray([
                'exception' => $exampleException,
            ]),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::WARNING),
            ],
        ];

        $event = Event::createEvent();
        $event->setMessage('foo bar');
        $event->setLogger('monolog.channel.foo');
        $event->setLevel(Severity::warning());

        yield 'Monolog\'s context is filled but handler should not fill the "extra" context' => [
            false,
            [
                'message' => 'foo bar',
                'level' => Logger::WARNING,
                'level_name' => Logger::getLevelName(Logger::WARNING),
                'context' => [
                    'foo' => 'bar',
                    'bar' => 'baz',
                ],
                'channel' => 'channel.foo',
                'extra' => [],
            ],
            $event,
            new EventHint(),
            [
                'monolog.channel' => 'channel.foo',
                'monolog.level' => Logger::getLevelName(Logger::WARNING),
            ],
        ];
    }
}
