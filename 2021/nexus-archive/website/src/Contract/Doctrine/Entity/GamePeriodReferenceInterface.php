<?php

declare(strict_types=1);

namespace App\Contract\Doctrine\Entity;

use App\Contract\Entity\Nexus\GamePeriodInterface;

interface GamePeriodReferenceInterface
{
    public function getGamePeriod(): ?GamePeriodInterface;

    public function setGamePeriod(GamePeriodInterface $gamePeriod): void;
}
