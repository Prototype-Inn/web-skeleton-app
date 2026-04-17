<?php

declare(strict_types=1);

return [
    ['GET', '/', [PrototypeIn\App\Action\LandingPageAction::class, 'handle']],
    ['GET', '/home', [PrototypeIn\App\Action\HomePageAction::class, 'handle']],
];