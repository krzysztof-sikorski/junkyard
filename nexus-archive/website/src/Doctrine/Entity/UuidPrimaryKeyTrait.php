<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Contract\Config\AppParameters;
use App\Contract\Config\AppSerializationGroups;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;

/**
 * Implementation for `\App\Contract\Doctrine\Entity\UuidPrimaryKeyInterface` interface
 */
trait UuidPrimaryKeyTrait
{
    #[
        ORM\Id,
        ORM\Column(name: 'id', type: AppParameters::DOCTRINE_COLUMN_TYPE_UUID),
        Groups(groups: [
            AppSerializationGroups::DEFAULT,
            AppSerializationGroups::ENTITY_USER,
            AppSerializationGroups::ENTITY_USER_ACCESS_TOKEN
        ]),
        SerializedName(serializedName: 'id'),
    ]
    protected Uuid $id;

    public function generateId(): void
    {
        $this->setId(id: Uuid::v4());
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }
}
