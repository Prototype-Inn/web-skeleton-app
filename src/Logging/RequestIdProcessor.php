<?php

declare(strict_types=1);

namespace PrototypeIn\App\Logging;

use Monolog\LogRecord;

final class RequestIdProcessor
{
    private string $requestId;

    public function __construct(?string $requestId = null)
    {
        $this->requestId = $requestId ?? getenv('APP_REQUEST_ID') ?: bin2hex(random_bytes(8));
    }

    public function __invoke(mixed $record): mixed
    {
        if ($record instanceof LogRecord) {
            $context = $record->context;
            if (!isset($context['request_id'])) {
                $context['request_id'] = $this->requestId;
            }

            return $record->with(context: $context);
        }

        if (is_array($record)) {
            if (!isset($record['context']) || !is_array($record['context'])) {
                $record['context'] = [];
            }
            if (!isset($record['context']['request_id'])) {
                $record['context']['request_id'] = $this->requestId;
            }
        }

        return $record;
    }
}
