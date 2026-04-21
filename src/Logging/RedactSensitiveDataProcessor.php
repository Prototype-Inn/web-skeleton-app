<?php

declare(strict_types=1);

namespace PrototypeIn\App\Logging;

use Monolog\LogRecord;

final class RedactSensitiveDataProcessor
{
    /**
     * @var array<string, bool>
     */
    private array $sensitiveKeys = [];

    /**
     * @param list<string> $sensitiveKeys
     */
    public function __construct(array $sensitiveKeys = ['password', 'token', 'authorization', 'cookie', 'set-cookie'])
    {
        foreach ($sensitiveKeys as $key) {
            $this->sensitiveKeys[mb_strtolower($key)] = true;
        }
    }

    public function __invoke(mixed $record): mixed
    {
        if ($record instanceof LogRecord) {
            return $record->with(
                context: $this->redactArray($record->context),
                extra: $this->redactArray($record->extra),
            );
        }

        if (is_array($record)) {
            if (isset($record['context']) && is_array($record['context'])) {
                $record['context'] = $this->redactArray($record['context']);
            }
            if (isset($record['extra']) && is_array($record['extra'])) {
                $record['extra'] = $this->redactArray($record['extra']);
            }
        }

        return $record;
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private function redactArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $normalizedKey = is_string($key) ? mb_strtolower($key) : null;

            if ($normalizedKey !== null && isset($this->sensitiveKeys[$normalizedKey])) {
                $data[$key] = '[REDACTED]';
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->redactArray($value);
            }
        }

        return $data;
    }
}
