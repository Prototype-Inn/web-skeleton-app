<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\ViewModel\HomePageViewModel;
use PrototypeIn\App\Responder\HtmlResponder;
use Psr\Http\Message\ResponseInterface;

class HomePageAction
{
    private HtmlResponder $responder;

    public function __construct(HtmlResponder $responder)
    {
        $this->responder = $responder;
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $viewModel = new HomePageViewModel(
            'Welcome to the Web Skeleton App!',
            'This is a basic home page. The application is up and running.',
            $args['name'] ?? 'Guest'
        );

        return $this->responder->setPayload($viewModel)();
    }
}


