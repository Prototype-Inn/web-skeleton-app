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

class HomePageActionTest extends TestCase
{
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
        $action = new HomePageAction($responder);
        $response = $action->handle($request, $args);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        // Additional assertions can be made on the $response object if needed
        // For example, checking headers, body content, etc.
        // However, verifying the ViewModel content is now the responsibility of the Responder test.
        // This test only verifies that the Action correctly uses the Responder.
    }

    public function testHandleReturnsHomePageViewModelWithName(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $args = ['name' => 'TestUser'];

        $responder = $this->createMockHtmlResponder();
        $action = new HomePageAction($responder);
        $response = $action->handle($request, $args);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}

