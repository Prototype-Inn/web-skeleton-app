<?php

declare(strict_types=1);

use PrototypeIn\App\Action\HomePageAction;
use PrototypeIn\App\Action\LandingPageAction;

return [
    ['GET', '/', [LandingPageAction::class, 'handle']],
    ['GET', '/home', [HomePageAction::class, 'handle']],
];
