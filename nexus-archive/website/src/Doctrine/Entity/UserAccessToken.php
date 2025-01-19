<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Contract\Config\AppSerializationGroups;
use App\Contract\Doctrine\Entity\DatedEntityInterface;
use App\Contract\Doctrine\Entity\UuidPrimaryKeyInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[
    ORM\Entity(),
    ORM\Table(name: 'user_access_token'),
    ORM\UniqueConstraint(name: 'value_uniq', fields: ['value']),
    ORM\Index(fields: ['owner'], name: 'user_access_token_owner_idx'),
]
class UserAccessToken implements UuidPrimaryKeyInterface, DatedEntityInterface
{
    use UuidPrimaryKeyTrait;
    use DatedEntityTrait;

    #[
        ORM\Column(name: 'value', type: Types::TEXT, unique: true, nullable: false),
        Groups(groups: [AppSerializationGroups::ENTITY_USER_ACCESS_TOKEN]),
        SerializedName(serializedName: 'value'),
    ]
    private ?string $value = null;

    #[ORM\Column(name: 'valid_until', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $validUntil = null;

    #[
        ORM\ManyToOne(targetEntity: User::class),
        ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false),
        Groups(groups: [AppSerializationGroups::ENTITY_USER_ACCESS_TOKEN]),
        SerializedName(serializedName: 'owner'),
    ]
    private ?User $owner = null;

    public function __construct()
    {
        $this->generateId();
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function getValidUntil(): ?DateTimeInterface
    {
        return $this->validUntil;
    }

    public function setValidUntil(?DateTimeInterface $validUntil): void
    {
        $this->validUntil = DateTimeImmutable::createFromInterface(object: $validUntil);
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

//    public function jsonSerialize(): array
//    {
//        return [
//            'id' => $this->getId(),
//            'value' => $this->getValue(),
//            'createdAt' => $this->getCreatedAt()?->format(format: DateTimeInterface::ISO8601),
//            'validUntil' => $this->getValidUntil()?->format(format: DateTimeInterface::ISO8601),
//            'owner_id' => $this->getOwner()?->getId(),
//        ];
//    }
}
