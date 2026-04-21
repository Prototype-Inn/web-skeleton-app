<?php

declare(strict_types=1);

namespace PrototypeIn\App\Event;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Psr\Log\LoggerInterface;

class ORMEventListener
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();
        $this->logger->debug('Doctrine loaded metadata', [
            'entity' => $classMetadata->getName(),
            'table' => $classMetadata->table['name'] ?? 'unknown',
        ]);
    }
}
