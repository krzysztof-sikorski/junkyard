<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Contracts\Repository;

use App\Contracts\Entity\DocumentInterface;

interface DocumentRepositoryInterface
{
    public function findByPath(null | string $path): null | DocumentInterface;
}
