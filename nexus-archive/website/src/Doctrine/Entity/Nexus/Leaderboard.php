<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Nexus;

use App\Contract\Doctrine\Entity\DatedEntityInterface;
use App\Contract\Doctrine\Entity\GamePeriodReferenceInterface;
use App\Contract\Doctrine\Entity\UuidPrimaryKeyInterface;
use App\Contract\Entity\Nexus\GamePeriodInterface;
use App\Doctrine\Entity\DatedEntityTrait;
use App\Doctrine\Entity\UuidPrimaryKeyTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'nexus_leaderboard'),
    ORM\UniqueConstraint(name: 'nexus_leaderboard_uniq', fields: ['category', 'gamePeriod']),
    ORM\Index(fields: ['category'], name: 'nexus_leaderboard_category_idx'),
    ORM\Index(fields: ['gamePeriod'], name: 'nexus_leaderboard_game_period_idx'),
]
class Leaderboard implements UuidPrimaryKeyInterface, GamePeriodReferenceInterface, DatedEntityInterface
{
    use UuidPrimaryKeyTrait;
    use DatedEntityTrait;

    #[
        ORM\ManyToOne(targetEntity: LeaderboardCategory::class),
        ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false),
    ]
    private ?LeaderboardCategory $category = null;

    #[
        ORM\ManyToOne(targetEntity: GamePeriod::class),
        ORM\JoinColumn(name: 'game_period_id', referencedColumnName: 'id', nullable: false),
    ]
    private ?GamePeriodInterface $gamePeriod = null;

    #[
        ORM\OneToMany(mappedBy: 'leaderboard', targetEntity: LeaderboardEntry::class),
        ORM\OrderBy(value: ['position' => 'ASC']),
    ]
    private Collection $entries;

    public function __construct()
    {
        $this->generateId();
        $this->entries = new ArrayCollection();
    }

    public function getCategory(): ?LeaderboardCategory
    {
        return $this->category;
    }

    public function setCategory(LeaderboardCategory $category): void
    {
        $this->category = $category;
    }

    public function getGamePeriod(): ?GamePeriodInterface
    {
        return $this->gamePeriod;
    }

    public function setGamePeriod(GamePeriodInterface $gamePeriod): void
    {
        $this->gamePeriod = $gamePeriod;
    }

    public function getEntries(): Collection
    {
        return $this->entries;
    }
}
