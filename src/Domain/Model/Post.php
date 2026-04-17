<?php

declare(strict_types=1);

namespace PrototypeIn\App\Domain\Model;

/**
 * @ORM\Entity
 */
class Post extends PostBase
{
    // Inherits all fields and methods from PostBase
    // No additional fields needed for base post type
}