<?php

declare(strict_types=1);

namespace App\Contract\Doctrine\Entity;

use DateTimeInterface;

interface DatedEntityInterface
{
    public function getCreatedAt(): ?DateTimeInterface;

    public function setCreatedAt(DateTimeInterface $createdAt): void;

    public function getLastModifiedAt(): ?DateTimeInterface;

    public function setLastModifiedAt(DateTimeInterface $lastModifiedAt): void;
}
