<?php

declare(strict_types=1);

namespace App\Service\Repository\Nexus;

use App\Doctrine\Entity\Nexus\GamePeriod;
use App\Doctrine\Entity\Nexus\Leaderboard;
use App\Doctrine\Entity\Nexus\LeaderboardCategory;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

final class LeaderboardRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findByGamePeriod(GamePeriod $gamePeriod): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'l')
            ->from(from: Leaderboard::class, alias: 'l')
            ->innerJoin(
                join: LeaderboardCategory::class,
                alias: 'cat',
                conditionType: Join::WITH,
                condition: 'l.category = cat',
            )
            ->where(predicates: 'l.gamePeriod = :gamePeriod')
            ->orderBy(sort: 'cat.name', order: 'ASC')
            ->addOrderBy(sort: 'cat.type', order: 'ASC')
            ->setParameter(key: 'gamePeriod', value: $gamePeriod);

        $query = $queryBuilder->getQuery();

        return $query->getResult(hydrationMode: AbstractQuery::HYDRATE_OBJECT);
    }
}
