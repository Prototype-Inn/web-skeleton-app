<?php

declare(strict_types=1);

use PrototypeIn\App\Action\HomePageAction;

return [
    ['GET', '/', [HomePageAction::class, 'handle']],
];
