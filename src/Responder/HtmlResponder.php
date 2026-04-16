<?php

declare(strict_types=1);

namespace PrototypeIn\App\Responder;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use PrototypeIn\App\ViewModel\HomePageViewModel;

class HtmlResponder
{
    private Environment $twig;
    private array|HomePageViewModel $payload;
    private int $statusCode = 200;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param array|HomePageViewModel $payload
     * @return $this
     */
    public function setPayload(array|HomePageViewModel $payload): self
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
        } elseif (is_array($this->payload)) {
            $data = $this->payload;
        } else {
            $data = ['message' => 'Invalid payload type.'];
        }

        $content = $this->twig->render('home.html.twig', $data);

        return new HtmlResponse($content, $this->statusCode);
    }
}

