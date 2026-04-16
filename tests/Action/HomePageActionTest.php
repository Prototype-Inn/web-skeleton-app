<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Action;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\Action\HomePageAction;
use PrototypeIn\App\ViewModel\HomePageViewModel;

class HomePageActionTest extends TestCase
{
    public function testHandleReturnsHomePageViewModel(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $args = [];

        $action = new HomePageAction();
        $viewModel = $action->handle($request, $args);

        $this->assertInstanceOf(HomePageViewModel::class, $viewModel);
        $this->assertEquals('Welcome to the Web Skeleton App!', $viewModel->getTitle());
        $this->assertEquals('This is a basic home page. The application is up and running.', $viewModel->getMessage());
        $this->assertEquals('Guest', $viewModel->getName());
    }

    public function testHandleReturnsHomePageViewModelWithName(): void
    {
        $request = $this->createStub(ServerRequestInterface::class);
        $args = ['name' => 'TestUser'];

        $action = new HomePageAction();
        $viewModel = $action->handle($request, $args);

        $this->assertInstanceOf(HomePageViewModel::class, $viewModel);
        $this->assertEquals('Welcome to the Web Skeleton App!', $viewModel->getTitle());
        $this->assertEquals('This is a basic home page. The application is up and running.', $viewModel->getMessage());
        $this->assertEquals('TestUser', $viewModel->getName());
    }
}
