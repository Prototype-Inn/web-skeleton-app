<?php

declare(strict_types=1);

namespace PrototypeIn\App\Action;

use Psr\Http\Message\ServerRequestInterface;
use PrototypeIn\App\ViewModel\HomePageViewModel;
use PrototypeIn\App\Responder\HtmlResponder;
use Psr\Http\Message\ResponseInterface;
use PrototypeIn\Abac\Services\AbacService;
use Laminas\Diactoros\Response\JsonResponse;

class HomePageAction
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

        $role = $subjectAttributes['roles'][0] ?? 'guest';
        $resourceAttributes = ['name' => 'homepage'];
        $actionName = 'view';

        if (!$this->abac->evaluateWithMatrix(
            $role,
            $resourceAttributes['name'],
            $actionName,
            $subjectAttributes,
            $resourceAttributes
        )) {
            return new JsonResponse(['message' => 'Access Denied'], 403);
        }

        $viewModel = new HomePageViewModel(
            'Welcome to the Web Skeleton App!',
            'This is a basic home page. The application is up and running.',
            $args['name'] ?? 'Guest'
        );

        return $this->responder->setPayload($viewModel)();
    }
}
