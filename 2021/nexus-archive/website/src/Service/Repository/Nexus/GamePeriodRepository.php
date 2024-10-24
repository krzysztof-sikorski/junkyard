<?php

declare(strict_types=1);

namespace App\Service\Repository\Nexus;

use App\Doctrine\Entity\Nexus\GamePeriod;
use Doctrine\ORM\EntityManagerInterface;

final class GamePeriodRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findById(int $id): ?GamePeriod
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'gp')
            ->from(from: GamePeriod::class, alias: 'gp')
            ->where(predicates: 'gp.id = :id')
            ->setParameter(key: 'id', value: $id);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @return GamePeriod[]
     */
    public function findAll(): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'gp')
            ->from(from: GamePeriod::class, alias: 'gp')
            ->orderBy(sort: 'gp.id', order: 'ASC');
        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }
}
