<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Nexus;

use App\Contract\Doctrine\Entity\UuidPrimaryKeyInterface;
use App\Doctrine\Entity\UuidPrimaryKeyTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'nexus_leaderboard_category'),
    ORM\UniqueConstraint(name: 'nexus_leaderboard_category_uniq', fields: ['name', 'type']),
]
class LeaderboardCategory implements UuidPrimaryKeyInterface
{
    use UuidPrimaryKeyTrait;

    #[ORM\Column(name: 'name', type: Types::TEXT, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'score_label', type: Types::TEXT, nullable: false)]
    private ?string $scoreLabel = null;

    #[ORM\Column(name: 'type', type: Types::TEXT, nullable: false)]
    private ?string $type = null;

    public function __construct()
    {
        $this->generateId();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getScoreLabel(): ?string
    {
        return $this->scoreLabel;
    }

    public function setScoreLabel(string $scoreLabel): void
    {
        $this->scoreLabel = $scoreLabel;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
