<?php

declare(strict_types=1);

namespace PrototypeIn\App\Pipeline;

use League\Pipeline\Pipeline;
use League\Pipeline\PipelineInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class RequestProcessingPipeline
{
    private PipelineInterface $pipeline;
    private ?LoggerInterface $logger = null;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        
        $this->pipeline = (new Pipeline(
            new \League\Pipeline\FingersCrossedProcessor,
            new ValidationStage($logger),
            new SanitizationStage($logger),
            new AuthorizationStage($logger),
            new ProcessingStage($logger)
        ));
    }

    public function process(ServerRequestInterface $request, callable $final): ResponseInterface
    {
        $payload = new PipelinePayload($request);
        
        $result = $this->pipeline->process($payload);
        
        return $final($result);
    }
}

class PipelinePayload
{
    public ServerRequestInterface $request;
    public array $data = [];
    public array $errors = [];
    public bool $isValid = false;
    public bool $authorized = false;
    public ?ResponseInterface $response = null;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function withData(array $data): self
    {
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    public function withErrors(array $errors): self
    {
        $clone = clone $this;
        $clone->errors = $errors;
        $clone->isValid = empty($errors);
        return $clone;
    }

    public function withAuthorized(bool $authorized): self
    {
        $clone = clone $this;
        $clone->authorized = $authorized;
        return $clone;
    }

    public function withResponse(ResponseInterface $response): self
    {
        $clone = clone $this;
        $clone->response = $response;
        return $clone;
    }
}

class ValidationStage
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function __invoke(PipelinePayload $payload): PipelinePayload
    {
        $requestId = (string) $payload->request->getAttribute('request_id', 'n/a');
        $this->logger?->debug('Pipeline stage started', [
            'event' => 'pipeline.stage.start',
            'stage' => 'validation',
            'request_id' => $requestId,
        ]);

        $body = (string) $payload->request->getBody();
        $data = json_decode($body, true) ?? [];

        $errors = [];
        
        if (empty($data['email'] ?? '')) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'] ?? '')) {
            $errors['password'] = 'Password is required';
        } elseif (mb_strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        $this->logger?->debug('Pipeline stage completed', [
            'event' => 'pipeline.stage.end',
            'stage' => 'validation',
            'request_id' => $requestId,
            'error_fields' => array_keys($errors),
            'is_valid' => empty($errors),
        ]);

        return $payload->withData($data)->withErrors($errors);
    }
}

class SanitizationStage
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function __invoke(PipelinePayload $payload): PipelinePayload
    {
        $requestId = (string) $payload->request->getAttribute('request_id', 'n/a');
        $this->logger?->debug('Pipeline stage started', [
            'event' => 'pipeline.stage.start',
            'stage' => 'sanitization',
            'request_id' => $requestId,
        ]);

        $data = $payload->data;
        
        if (isset($data['email'])) {
            $data['email'] = mb_strtolower(trim($data['email']));
        }
        if (isset($data['firstName'])) {
            $data['firstName'] = trim($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $data['lastName'] = trim($data['lastName']);
        }

        $this->logger?->debug('Pipeline stage completed', [
            'event' => 'pipeline.stage.end',
            'stage' => 'sanitization',
            'request_id' => $requestId,
            'fields' => array_keys($data),
        ]);

        return $payload->withData($data);
    }
}

class AuthorizationStage
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function __invoke(PipelinePayload $payload): PipelinePayload
    {
        $requestId = (string) $payload->request->getAttribute('request_id', 'n/a');
        $this->logger?->debug('Pipeline stage started', [
            'event' => 'pipeline.stage.start',
            'stage' => 'authorization',
            'request_id' => $requestId,
        ]);

        $userId = $payload->request->getAttribute('user_id');

        $authorized = ($userId !== null) || ($payload->request->getMethod() === 'POST');

        $this->logger?->debug('Pipeline stage completed', [
            'event' => 'pipeline.stage.end',
            'stage' => 'authorization',
            'request_id' => $requestId,
            'authorized' => $authorized,
            'method' => $payload->request->getMethod(),
        ]);

        return $payload->withAuthorized($authorized);
    }
}

class ProcessingStage
{
    private ?LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function __invoke(PipelinePayload $payload): PipelinePayload
    {
        $this->logger?->debug('Pipeline stage completed', [
            'event' => 'pipeline.stage.end',
            'stage' => 'processing',
            'request_id' => (string) $payload->request->getAttribute('request_id', 'n/a'),
        ]);

        return $payload;
    }
}
