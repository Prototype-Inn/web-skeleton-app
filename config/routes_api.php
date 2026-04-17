<?php

declare(strict_types=1);

return [
    ['POST', '/register', [PrototypeIn\App\Action\RegisterAction::class, 'handle']],
    ['GET', '/urn-demo', [PrototypeIn\App\Action\UrnDemoAction::class, 'handle']],
];