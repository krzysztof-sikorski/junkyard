<?php

declare(strict_types=1);

namespace App\Service\Repository;

use App\Contract\Service\ClockInterface;
use App\Doctrine\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserRepository
{
    public function __construct(
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * @param string[] $roles
     */
    public function create(
        string $username,
        string $plaintextPassword,
        array $roles
    ): User {
        $createdAt = $this->clock->getCurrentDateTime();

        $user = new User();
        $user->setCreatedAt(createdAt: $createdAt);
        $user->setLastModifiedAt(lastModifiedAt: $createdAt);
        $user->setUsername(username: $username);
        $user->setRoles(roles: $roles);
        $user->setEnabled(enabled: true);

        $hashedPassword = $this->passwordHasher->hashPassword(user: $user, plainPassword: $plaintextPassword);
        $user->setPassword(password: $hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'u')
            ->from(from: User::class, alias: 'u')
            ->andWhere('u.username = :username')
            ->setParameter(key: 'username', value: $username);

        $query = $queryBuilder->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getTotalCount(): int
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'COUNT(u)')
            ->from(from: User::class, alias: 'u');

        $query = $queryBuilder->getQuery();

        return $query->getSingleScalarResult();
    }
}
