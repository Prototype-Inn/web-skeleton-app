<?php

declare(strict_types=1);

namespace PrototypeIn\App\Tests\Support;

use Mockery;

abstract class MockeryTestCase extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
