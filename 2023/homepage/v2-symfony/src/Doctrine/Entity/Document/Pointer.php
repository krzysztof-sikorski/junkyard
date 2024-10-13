<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine\Entity\Document;

use App\Contracts\Entity\Document\PointerInterface;
use App\Contracts\Entity\DocumentInterface;
use App\Doctrine\Entity\Document;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Pointer extends Document implements PointerInterface
{
    #[ORM\ManyToOne(targetEntity: DocumentInterface::class, fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'pointer_target_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private null | DocumentInterface $target = null;

    #[ORM\Column(name: 'permanent', type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private bool $permanent = false;

    public function getTarget(): null | DocumentInterface
    {
        return $this->target;
    }

    public function setTarget(null | DocumentInterface $value): void
    {
        $this->target = $value;
    }

    public function isPermanent(): bool
    {
        return $this->permanent;
    }

    public function setPermanent(bool $value): void
    {
        $this->permanent = $value;
    }
}
