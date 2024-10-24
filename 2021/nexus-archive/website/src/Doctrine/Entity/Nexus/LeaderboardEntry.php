<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Nexus;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'nexus_leaderboard_entry'),
    ORM\UniqueConstraint(name: 'nexus_leaderboard_entry_uniq', fields: ['leaderboard', 'position']),
    ORM\Index(fields: ['leaderboard'], name: 'nexus_leaderboard_entry_leaderboard_idx'),
]
class LeaderboardEntry
{
    #[
        ORM\Id,
        ORM\ManyToOne(targetEntity: Leaderboard::class, inversedBy: 'entries'),
        ORM\JoinColumn(name: 'leaderboard_id', referencedColumnName: 'id', nullable: false),
    ]
    private ?Leaderboard $leaderboard = null;

    #[
        ORM\Id,
        ORM\Column(name: 'position', type: Types::INTEGER, nullable: false),
    ]
    private ?int $position = null;

    #[ORM\Column(name: 'character_name', type: Types::TEXT, nullable: false)]
    private ?string $characterName = null;

    #[ORM\Column(name: 'score', type: Types::INTEGER, nullable: false)]
    private ?int $score = null;

    public function getLeaderboard(): ?Leaderboard
    {
        return $this->leaderboard;
    }

    public function setLeaderboard(?Leaderboard $leaderboard): void
    {
        $this->leaderboard = $leaderboard;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

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

    public function setScore(int $score): void
    {
        $this->score = $score;
    }
}
