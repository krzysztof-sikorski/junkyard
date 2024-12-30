<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Contracts\Entity\DocumentInterface;
use App\Doctrine\Entity\Document\File;
use App\Doctrine\Entity\Document\Page;
use App\Doctrine\Entity\Document\Pointer;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'document')]
#[ORM\Index(columns: ['type'], name: 'document_type_idx')]
#[ORM\Index(columns: ['parent_id'], name: 'document_parent_idx')]
#[ORM\UniqueConstraint(name: 'document_path_idx', columns: ['path'])]
#[ORM\Index(columns: ['pointer_target_id'], name: 'document_pointer_target_idx')]
#[ORM\InheritanceType(value: 'SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(
    name: 'type',
    type: Types::TEXT,
    enumType: DocumentType::class,
)]
#[ORM\DiscriminatorMap(
    value: [
        DocumentType::PAGE->value => Page::class,
        DocumentType::FILE->value => File::class,
        DocumentType::POINTER->value => Pointer::class,
    ],
)]
abstract class Document implements DocumentInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: UuidType::NAME)]
    private null | Uuid $id = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: false)]
    private null | DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private null | DateTimeImmutable $updatedAt = null;

    #[ORM\Column(name: 'deleted_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private null | DateTimeImmutable $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: DocumentInterface::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private null | DocumentInterface $parent = null;

    #[ORM\Column(name: 'slug', type: Types::TEXT, nullable: true)]
    private null | string $slug = null;

    #[ORM\Column(name: 'path', type: Types::TEXT, unique: true, nullable: true)]
    private null | string $path = null;

    final public function getId(): null | Uuid
    {
        return $this->id;
    }

    final public function setId(null | Uuid $value): void
    {
        $this->id = $value;
    }

    final public function getCreatedAt(): null | DateTimeImmutable
    {
        return $this->createdAt;
    }

    final public function setCreatedAt(null | DateTimeImmutable $value): void
    {
        $this->createdAt = $value;
    }

    final public function getUpdatedAt(): null | DateTimeImmutable
    {
        return $this->updatedAt;
    }

    final public function setUpdatedAt(null | DateTimeImmutable $value): void
    {
        $this->updatedAt = $value;
    }

    final public function getDeletedAt(): null | DateTimeImmutable
    {
        return $this->deletedAt;
    }

    final public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    final public function setDeletedAt(null | DateTimeImmutable $value): void
    {
        $this->deletedAt = $value;
    }

    final public function getParent(): null | DocumentInterface
    {
        return $this->parent;
    }

    final public function setParent(null | DocumentInterface $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    final public function getSlug(): null | string
    {
        return $this->slug;
    }

    final public function setSlug(null | string $slug): void
    {
        $this->slug = $slug;
    }

    final public function getPath(): null | string
    {
        return $this->path;
    }

    final public function setPath(null | string $path): void
    {
        $this->path = $path;
    }
}
