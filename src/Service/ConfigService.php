<?php

declare(strict_types=1);

namespace PrototypeIn\App\Service;

use League\Config\Configuration;
use League\Config\ConfigurationBuilder;
use Nette\Schema\Schema as NetteSchema;

class ConfigService
{
    private Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config->get($key, $default);
    }
}
