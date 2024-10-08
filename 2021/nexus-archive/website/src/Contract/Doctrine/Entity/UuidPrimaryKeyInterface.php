<?php

declare(strict_types=1);

namespace App\Contract\Doctrine\Entity;

use Symfony\Component\Uid\Uuid;

interface UuidPrimaryKeyInterface
{
    public function getId(): Uuid;

    public function setId(Uuid $id): void;
}
