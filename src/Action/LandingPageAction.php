<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\ViewModel\LandingPageViewModel;
use PrototypeIn\App\Responder\HtmlResponder;
use Psr\Http\Message\ResponseInterface;
use PrototypeIn\Abac\Services\AbacService;

class LandingPageAction
{
    private HtmlResponder $responder;
    private AbacService $abac;

    public function __construct(HtmlResponder $responder, AbacService $abac)
    {
        $this->responder = $responder;
        $this->abac = $abac;
    }

    public function handle(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $subjectAttributes = [
            'id' => $request->getAttribute('user_id', null),
            'roles' => ['guest'],
            'isAuthenticated' => false,
        ];

        if (!$this->abac->evaluateWithMatrix(
            'guest',
            'landingpage',
            'view',
            $subjectAttributes,
            ['name' => 'landingpage']
        )) {
            return new \Laminas\Diactoros\Response\JsonResponse(['message' => 'Access Denied'], 403);
        }

        $viewModel = new LandingPageViewModel(
            'Welcome to Web Skeleton App',
            'Build amazing web applications with this modern PHP skeleton',
            'Get Started',
            '/home'
        );

        return $this->responder->setPayload($viewModel)();
    }
}
