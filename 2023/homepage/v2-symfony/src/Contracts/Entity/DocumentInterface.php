<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Contracts\Entity;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

interface DocumentInterface
{
    public function getId(): null | Uuid;

    public function setId(null | Uuid $value): void;

    public function getCreatedAt(): null | DateTimeImmutable;

    public function setCreatedAt(null | DateTimeImmutable $value): void;

    public function getUpdatedAt(): null | DateTimeImmutable;

    public function setUpdatedAt(null | DateTimeImmutable $value): void;

    public function isDeleted(): bool;

    public function getDeletedAt(): null | DateTimeImmutable;

    public function setDeletedAt(null | DateTimeImmutable $value): void;

    public function getParent(): null | self;

    public function setParent(null | self $parent): static;

    public function getSlug(): null | string;

    public function setSlug(null | string $slug): void;

    public function getPath(): null | string;

    public function setPath(null | string $path): void;
}
