<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Contracts\Entity\Document;

use App\Contracts\Entity\DocumentInterface;

interface PointerInterface extends DocumentInterface
{
    public function getTarget(): null | DocumentInterface;

    public function setTarget(null | DocumentInterface $value): void;

    public function isPermanent(): bool;

    public function setPermanent(bool $value): void;
}
