<?php

declare(strict_types=1);

namespace App\Service\Repository;

use App\Contract\Service\ClockInterface;
use App\Doctrine\Entity\User;
use App\Doctrine\Entity\UserAccessToken;
use App\Service\TokenGenerator;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;

final class UserAccessTokenRepository
{
    public function __construct(
        private ClockInterface $clock,
        private TokenGenerator $tokenGenerator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(User $owner, DateInterval $duration): UserAccessToken
    {
        $createdAt = $this->clock->getCurrentDateTime();
        $validUntil = $createdAt->add(interval: $duration);

        $token = new UserAccessToken();
        $token->setOwner(owner: $owner);
        $token->setValue(value: $this->tokenGenerator->generate());
        $token->setCreatedAt(createdAt: $createdAt);
        $token->setLastModifiedAt(lastModifiedAt: $createdAt);
        $token->setValidUntil(validUntil: $validUntil);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function prune(): void
    {
        $currentDateTime = $this->clock->getCurrentDateTime();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->delete(delete: UserAccessToken::class, alias: 'uat')
            ->where(predicates: 'uat.validUntil < :currentDateTime')
            ->setParameter(key: 'currentDateTime', value: $currentDateTime);

        $query = $queryBuilder->getQuery();

        $query->execute();
    }

    public function findByValue(string $value): ?UserAccessToken
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'uat')
            ->from(from: UserAccessToken::class, alias: 'uat')
            ->andWhere('uat.value = :value')
            ->setParameter(key: 'value', value: $value)
            ->andWhere('uat.validUntil > current_timestamp()');

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getTotalCount(): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'COUNT(uat)')
            ->from(from: UserAccessToken::class, alias: 'uat');

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }
}
