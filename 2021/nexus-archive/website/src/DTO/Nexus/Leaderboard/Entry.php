<?php

declare(strict_types=1);

namespace App\DTO\Nexus\Leaderboard;

use App\Contract\Entity\Nexus\Leaderboard\EntryInterface;

class Entry implements EntryInterface
{
    private ?string $characterName = null;
    private ?int $score = null;

    public function getCharacterName(): ?string
    {
        return $this->characterName;
    }

    public function setCharacterName(string $characterName): void
    {
        $this->characterName = $characterName;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $value): void
    {
        $this->score = $value;
    }
}
