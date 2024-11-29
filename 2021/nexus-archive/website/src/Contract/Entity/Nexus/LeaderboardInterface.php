<?php

declare(strict_types=1);

namespace App\Contract\Entity\Nexus;

use App\Contract\Entity\Nexus\Leaderboard\EntryListInterface;
use InvalidArgumentException;

/**
 * Leaderboard table (header and a list of entries).
 */
interface LeaderboardInterface
{
    public function getName(): ?string;

    public function setName(string $name): void;

    public function getType(): ?string;

    /** @throws InvalidArgumentException when $type is not valid value */
    public function setType(string $type): void;

    public function getScoreLabel(): ?string;

    public function setScoreLabel(string $scoreLabel): void;

    public function getEntries(): EntryListInterface;
}
