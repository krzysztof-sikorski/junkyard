<?php

declare(strict_types=1);

namespace App\DTO\Nexus;

use App\Contract\Entity\LeaderboardTypes;
use App\Contract\Entity\Nexus\Leaderboard\EntryListInterface;
use App\Contract\Entity\Nexus\LeaderboardInterface;
use App\DTO\Nexus\Leaderboard\EntryList;
use InvalidArgumentException;

use function in_array;
use function sprintf;

class Leaderboard implements LeaderboardInterface
{
    private EntryListInterface $entries;

    public function __construct(
        private ?string $name = null,
        private ?string $type = null,
        private ?string $scoreLabel = null,
    ) {
        $this->entries = new EntryList();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (false === in_array(needle: $type, haystack: LeaderboardTypes::cases(), strict: true)) {
            throw new InvalidArgumentException(message: sprintf('Value "%s" is not a valid type', $type));
        }
        $this->type = $type;
    }

    public function getScoreLabel(): ?string
    {
        return $this->scoreLabel;
    }

    public function setScoreLabel(string $scoreLabel): void
    {
        $this->scoreLabel = $scoreLabel;
    }

    public function getEntries(): EntryListInterface
    {
        return $this->entries;
    }
}
