<?php

declare(strict_types=1);

namespace PrototypeIn\App\Responder;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use PrototypeIn\App\ViewModel\HomePageViewModel;
use PrototypeIn\App\ViewModel\LandingPageViewModel;

class HtmlResponder
{
    private Environment $twig;
    private array|HomePageViewModel|LandingPageViewModel $payload;
    private int $statusCode = 200;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param array|HomePageViewModel|LandingPageViewModel $payload
     * @return $this
     */
    public function setPayload(array|HomePageViewModel|LandingPageViewModel $payload): self
    {
        $this->payload = $payload;
        return $this;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function __invoke(): ResponseInterface
    {
        if ($this->payload instanceof HomePageViewModel) {
            $data = $this->payload->toArray();
            $template = 'home.html.twig';
        } elseif ($this->payload instanceof LandingPageViewModel) {
            $data = $this->payload->toArray();
            $template = 'landing.html.twig';
        } elseif (is_array($this->payload)) {
            $data = $this->payload;
            $template = 'home.html.twig';
        } else {
            $data = ['message' => 'Invalid payload type.'];
            $template = 'home.html.twig';
        }

        $content = $this->twig->render($template, $data);

        return new HtmlResponse($content, $this->statusCode);
    }
}

