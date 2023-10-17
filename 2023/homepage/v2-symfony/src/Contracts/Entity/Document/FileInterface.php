<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Contracts\Entity\Document;

use App\Contracts\Entity\DocumentInterface;

interface FileInterface extends DocumentInterface
{
    public function getName(): null | string;

    public function setName(null | string $value): void;

    public function getStoragePath(): null | string;

    public function setStoragePath(null | string $value): void;
}
