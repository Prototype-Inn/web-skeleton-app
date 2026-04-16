<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Action;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\Action\LandingPageAction;
use PrototypeIn\App\Responder\HtmlResponder;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use PrototypeIn\Abac\Services\AbacService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class LandingPageActionTest extends TestCase
{
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->request = new ServerRequest();
    }

    private function createStubHtmlResponder(): HtmlResponder
    {
        $mockTwig = $this->createStub(Environment::class);
        $mockResponse = $this->createStub(ResponseInterface::class);

        $stub = $this->createStub(HtmlResponder::class);
        $stub->method('setPayload')->willReturn($stub);
        $stub->method('__invoke')->willReturn($mockResponse);

        return $stub;
    }

    private function createStubAbacService(bool $accessGranted = true): AbacService
    {
        $stub = $this->createStub(AbacService::class);
        $stub->method('evaluateWithMatrix')->willReturn($accessGranted);
        return $stub;
    }

    public function testHandleReturnsLandingPageViewModel(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $args = [];

        $responder = $this->createStubHtmlResponder();
        $abac = $this->createStubAbacService();

        $action = new LandingPageAction($responder, $abac);
        $response = $action->handle($request, $args);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLandingPageActionRendersSuccessfullyWhenAccessGranted(): void
    {
        $responder = $this->createStubHtmlResponder();
        $abac = $this->createStubAbacService(true);

        $action = new LandingPageAction($responder, $abac);
        $response = $action->handle($this->request, []);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLandingPageActionReturnsForbiddenWhenAccessDenied(): void
    {
        $responder = $this->createStubHtmlResponder();
        $abac = $this->createStubAbacService(false);

        $action = new LandingPageAction($responder, $abac);
        $response = $action->handle($this->request, []);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(403, $response->getStatusCode());
        self::assertStringContainsString('Access Denied', (string)$response->getBody());
    }
}
