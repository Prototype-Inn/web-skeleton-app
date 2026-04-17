<?php

declare(strict_types=1);

use PrototypeIn\App\Action\HomePageAction;
use PrototypeIn\App\Action\LandingPageAction;
use PrototypeIn\App\Action\RegisterAction;
use PrototypeIn\App\Action\DemoAction;
use PrototypeIn\App\Action\PipelineDemoAction;
use PrototypeIn\App\Action\UrnDemoAction;

return [
    ['GET', '/', [LandingPageAction::class, 'handle']],
    ['GET', '/home', [HomePageAction::class, 'handle']],
    ['POST', '/register', [RegisterAction::class, 'handle']],
    ['GET', '/demo', [DemoAction::class, 'show']],
    ['POST', '/demo', [DemoAction::class, 'submit']],
    ['POST', '/pipeline', [PipelineDemoAction::class, 'handle']],
    ['GET', '/urn-demo', [UrnDemoAction::class, 'handle']],
];
