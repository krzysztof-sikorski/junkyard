<?php

declare(strict_types=1);

namespace App\Doctrine\Entity\Nexus;

use App\Contract\Entity\Nexus\GamePeriodInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity(),
    ORM\Table(name: 'nexus_game_period'),
]
class GamePeriod implements GamePeriodInterface
{
    #[
        ORM\Id,
        ORM\Column(name: 'id', type: Types::INTEGER, nullable: false),
    ]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::TEXT, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(name: 'started_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private ?DateTimeImmutable $startedAt = null;

    #[ORM\Column(name: 'completed_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    #[ORM\Column(name: 'current', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $current = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $value): void
    {
        $this->id = $value;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStartedAt(): DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(DateTimeInterface $value): void
    {
        $this->startedAt = DateTimeImmutable::createFromInterface(object: $value);
    }

    public function getCompletedAt(): DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?DateTimeInterface $value): void
    {
        if (null !== $value) {
            $this->completedAt = DateTimeImmutable::createFromInterface(object: $value);
        } else {
            $this->completedAt = null;
        }
    }

    public function isCurrent(): bool
    {
        return $this->current;
    }

    public function setCurrent(bool $value): void
    {
        $this->current = $value;
    }
}
