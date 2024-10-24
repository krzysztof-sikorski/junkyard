<?php

declare(strict_types=1);

namespace App\Service\Repository;

use App\Doctrine\Entity\PageView;
use App\Doctrine\Entity\User;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

final class PageViewRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(User $owner, PageView $pageView, DateTimeInterface $currentDateTime): void
    {
        $pageView->setCreatedAt(createdAt: $currentDateTime);
        $pageView->setLastModifiedAt(lastModifiedAt: $currentDateTime);
        $pageView->setOwner(owner: $owner);
        $this->entityManager->persist($pageView);
        $this->entityManager->flush();
    }

    public function prune(): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->delete(delete: PageView::class, alias: 'pv')
            ->where(predicates: 'pv.parsedAt IS NOT NULL AND pv.parserErrors IS NULL');

        $query = $queryBuilder->getQuery();

        $query->execute();
    }

    public function getTotalCount(): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'COUNT(pv)')
            ->from(from: PageView::class, alias: 'pv');

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getPartialCount(DateTimeInterface $from, DateTimeInterface $to): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'COUNT(pv)')
            ->from(from: PageView::class, alias: 'pv')
            ->where(predicates: 'pv.createdAt BETWEEN :from AND :to')
            ->setParameter(key: 'from', value: $from)
            ->setParameter(key: 'to', value: $to);

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }

    public function getUnparsed(int $batchSize): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'pv')
            ->from(from: PageView::class, alias: 'pv')
            ->where(predicates: 'pv.parsedAt IS NULL')
            ->orderBy(sort: 'pv.createdAt', order: 'ASC')
            ->addOrderBy(sort: 'pv.requestStartedAt', order: 'ASC')
            ->addOrderBy(sort: 'pv.id', order: 'ASC')
            ->setMaxResults(maxResults: $batchSize);

        $query = $queryBuilder->getQuery();

        return $query->getResult();
    }

    public function saveAsParsed(PageView $pageView, DateTimeInterface $parsedAt, ?array $errors): void
    {
        $pageView->setParsedAt(parsedAt: $parsedAt);
        $pageView->setParserErrors(parserErrors: $errors);
        $this->entityManager->persist($pageView);
        $this->entityManager->flush();
    }
}
