<?php

declare(strict_types=1);

return [
    ['GET', '/demo', [PrototypeIn\App\Action\DemoAction::class, 'show']],
    ['POST', '/demo', [PrototypeIn\App\Action\DemoAction::class, 'submit']],
    ['POST', '/pipeline', [PrototypeIn\App\Action\PipelineDemoAction::class, 'handle']],
];