<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Action;

use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\Action\LandingPageAction;
use PrototypeIn\App\Responder\HtmlResponder;
use Psr\Http\Message\ResponseInterface;
use PrototypeIn\Abac\Services\AbacService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;
use PrototypeIn\App\Tests\Support\MockeryTestCase;

class LandingPageActionTest extends MockeryTestCase
{
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->request = new ServerRequest();
    }

    private function createMockHtmlResponder(): HtmlResponder
    {
        $mockResponse = \Mockery::mock(ResponseInterface::class);

        $responder = \Mockery::mock(HtmlResponder::class);
        $responder->shouldReceive('setPayload')->andReturnSelf();
        $responder->shouldReceive('__invoke')->andReturn($mockResponse);

        return $responder;
    }

    private function createMockAbacService(bool $accessGranted = true): AbacService
    {
        $abac = \Mockery::mock(AbacService::class);
        $abac->shouldReceive('evaluateWithMatrix')->andReturn($accessGranted);
        return $abac;
    }

    private function createMockRequest(): ServerRequestInterface
    {
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getAttribute')->with('user_id', null)->andReturn(null)->byDefault();
        return $request;
    }

    public function testHandleReturnsLandingPageViewModel(): void
    {
        $request = $this->createMockRequest();
        $args = [];

        $responder = $this->createMockHtmlResponder();
        $abac = $this->createMockAbacService();

        $action = new LandingPageAction($responder, $abac);
        $response = $action->handle($request, $args);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLandingPageActionRendersSuccessfullyWhenAccessGranted(): void
    {
        $responder = $this->createMockHtmlResponder();
        $abac = $this->createMockAbacService(true);

        $action = new LandingPageAction($responder, $abac);
        $response = $action->handle($this->request, []);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLandingPageActionReturnsForbiddenWhenAccessDenied(): void
    {
        $responder = $this->createMockHtmlResponder();
        $abac = $this->createMockAbacService(false);

        $action = new LandingPageAction($responder, $abac);
        $response = $action->handle($this->request, []);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(403, $response->getStatusCode());
        self::assertStringContainsString('Access Denied', (string)$response->getBody());
    }
}
