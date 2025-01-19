<?php

declare(strict_types=1);

namespace App\Contract\Entity\Nexus;

use DateTimeInterface;

/**
 * Significant date period, for example a Breath or a part of Breath.
 */
interface GamePeriodInterface
{
    public function getId(): ?int;

    public function setId(?int $value): void;

    public function getName(): ?string;

    public function setName(string $name): void;

    public function getStartedAt(): DateTimeInterface;

    public function setStartedAt(DateTimeInterface $value): void;

    public function getCompletedAt(): DateTimeInterface;

    public function setCompletedAt(?DateTimeInterface $value): void;

    public function isCurrent(): bool;

    public function setCurrent(bool $value): void;
}
