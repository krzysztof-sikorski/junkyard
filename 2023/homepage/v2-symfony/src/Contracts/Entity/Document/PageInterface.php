<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Contracts\Entity\Document;

use App\Contracts\Entity\DocumentInterface;

interface PageInterface extends DocumentInterface
{
    public function getTitle(): null | string;

    public function setTitle(null | string $value): void;

    public function getContent(): null | string;

    public function setContent(null | string $value): void;
}
