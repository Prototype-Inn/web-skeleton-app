<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Logging;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use PrototypeIn\App\Logging\RedactSensitiveDataProcessor;
use PrototypeIn\App\Logging\RequestIdProcessor;

class MonologProcessorsTest extends TestCase
{
    public function testRequestIdProcessorInjectsRequestIdWhenMissing(): void
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $logger->pushProcessor(new RequestIdProcessor('req-fixed-123'));

        $logger->info('hello world');

        $record = $handler->getRecords()[0];
        $context = $record instanceof LogRecord ? $record->context : $record['context'];

        self::assertSame('req-fixed-123', $context['request_id']);
    }

    public function testRedactSensitiveDataProcessorRedactsNestedSensitiveKeys(): void
    {
        $logger = new Logger('test');
        $handler = new TestHandler();
        $logger->pushHandler($handler);
        $logger->pushProcessor(new RedactSensitiveDataProcessor(['password', 'token']));

        $logger->info('created', [
            'email' => 'john@example.com',
            'password' => 'super-secret',
            'nested' => [
                'token' => 'abc123',
                'safe' => 'ok',
            ],
        ]);

        $record = $handler->getRecords()[0];
        $context = $record instanceof LogRecord ? $record->context : $record['context'];

        self::assertSame('john@example.com', $context['email']);
        self::assertSame('[REDACTED]', $context['password']);
        self::assertSame('[REDACTED]', $context['nested']['token']);
        self::assertSame('ok', $context['nested']['safe']);
    }
}
