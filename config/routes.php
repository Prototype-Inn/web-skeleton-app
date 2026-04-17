<?php

declare(strict_types=1);

use PrototypeIn\App\Action\HomePageAction;
use PrototypeIn\App\Action\LandingPageAction;
use PrototypeIn\App\Action\RegisterAction;

return [
    ['GET', '/', [LandingPageAction::class, 'handle']],
    ['GET', '/home', [HomePageAction::class, 'handle']],
    ['POST', '/register', [RegisterAction::class, 'handle']],
];
