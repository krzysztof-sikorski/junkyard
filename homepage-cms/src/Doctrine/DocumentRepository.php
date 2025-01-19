<?php
/**
 * Simple CMS for small websites.
 */

declare(strict_types=1);

namespace App\Doctrine;

use App\Contracts\Entity\DocumentInterface;
use App\Contracts\Repository\DocumentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DocumentRepository implements DocumentRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function findByPath(null | string $path): null | DocumentInterface
    {
        $repository = $this->entityManager->getRepository(DocumentInterface::class);

        return $repository->findOneBy(criteria: ['path' => $path]);
    }
}
