<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Action;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\Action\HomePageAction;
use PrototypeIn\App\Responder\HtmlResponder;
use PrototypeIn\App\ViewModel\HomePageViewModel;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use PrototypeIn\Abac\Services\AbacService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class HomePageActionTest extends TestCase
{
    private HtmlResponder $htmlResponder;
    private AbacService $abac;
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        $this->htmlResponder = $this->createMock(HtmlResponder::class);
        $this->abac = $this->createMock(AbacService::class);
        $this->request = new ServerRequest();
    }

    private function createMockHtmlResponder(): HtmlResponder
    {
        $mockTwig = $this->createStub(Environment::class);
        $mockResponse = $this->createStub(ResponseInterface::class);

        $mockResponder = $this->getMockBuilder(HtmlResponder::class)
            ->setConstructorArgs([$mockTwig])
            ->onlyMethods(['setPayload', '__invoke'])
            ->getMock();

        $mockResponder->method('setPayload')->willReturnSelf();
        $mockResponder->method('__invoke')->willReturn($mockResponse);

        return $mockResponder;
    }

    public function testHandleReturnsHomePageViewModel(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $args = [];

        $responder = $this->createMockHtmlResponder();
        $abac = $this->createMock(AbacService::class);
        $abac->method('evaluateWithMatrix')->willReturn(true);

        $action = new HomePageAction($responder, $abac);
        $response = $action->handle($request, $args);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHandleReturnsHomePageViewModelWithName(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $args = ['name' => 'TestUser'];

        $responder = $this->createMockHtmlResponder();
        $abac = $this->createMock(AbacService::class);
        $abac->method('evaluateWithMatrix')->willReturn(true);

        $action = new HomePageAction($responder, $abac);
        $response = $action->handle($request, $args);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHomePageActionRendersSuccessfullyWhenAccessGranted(): void
    {
        $this->abac->method('evaluateWithMatrix')
            ->willReturn(true);

        $this->htmlResponder->method('setPayload')->willReturnSelf();
        $this->htmlResponder->method('__invoke')->willReturn($this->createMock(ResponseInterface::class));

        $action = new HomePageAction($this->htmlResponder, $this->abac);
        $response = $action->handle($this->request, []);

        self::assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testHomePageActionReturnsForbiddenWhenAccessDenied(): void
    {
        $this->abac->method('evaluateWithMatrix')
            ->with(
                'guest',
                'homepage',
                'view',
                $this->callback(function (array $subjectAttributes) {
                    return ($subjectAttributes['id'] ?? null) === null && $subjectAttributes['isAuthenticated'] === false && in_array('guest', $subjectAttributes['roles']);
                }),
                $this->callback(function (array $resourceAttributes) {
                    return $resourceAttributes['name'] === 'homepage';
                })
            )
            ->willReturn(false);

        $action = new HomePageAction($this->htmlResponder, $this->abac);
        $response = $action->handle($this->request, []);

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(403, $response->getStatusCode());
        self::assertStringContainsString('Access Denied', (string)$response->getBody());
    }
}

