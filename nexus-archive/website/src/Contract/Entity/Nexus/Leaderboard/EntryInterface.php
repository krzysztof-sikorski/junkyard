<?php

declare(strict_types=1);

namespace App\Contract\Entity\Nexus\Leaderboard;

interface EntryInterface
{
    public function getCharacterName(): ?string;

    public function setCharacterName(string $characterName): void;

    public function getScore(): ?int;

    public function setScore(int $value): void;
}
