<?php

declare(strict_types=1);

namespace App\Service\ParserResult;

use App\Contract\Entity\Nexus\GamePeriodInterface;
use App\Contract\Entity\Nexus\LeaderboardInterface;
use App\Contract\Service\ClockInterface;
use App\Contract\Service\Parser\ParserResultInterface;
use App\Contract\Service\Parser\ParserResultProcessorInterface;
use App\Doctrine\Entity\Nexus\Leaderboard;
use App\Doctrine\Entity\Nexus\LeaderboardCategory;
use App\Doctrine\Entity\Nexus\LeaderboardEntry;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;

final class ParserResultProcessor implements ParserResultProcessorInterface
{
    public function __construct(
        private ClockInterface $clock,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function persist(ParserResultInterface $parserResult): void
    {
        $currentDateTime = $this->clock->getCurrentDateTime();
        $gamePeriod = $parserResult->getGamePeriod();
        $leaderboard = $parserResult->getLeaderboard();

        if (null !== $leaderboard) {
            $this->processLeaderboard(
                currentDateTime: $currentDateTime,
                gamePeriod: $gamePeriod,
                leaderboardDTO: $leaderboard
            );
        }
    }

    private function processLeaderboard(
        DateTimeInterface $currentDateTime,
        GamePeriodInterface $gamePeriod,
        LeaderboardInterface $leaderboardDTO
    ): void {
        $category = $this->findOrCreateLeaderboardCategory(leaderboardDTO: $leaderboardDTO);

        $leaderboard = $this->findOrCreateLeaderboard(
            currentDateTime: $currentDateTime,
            gamePeriod: $gamePeriod,
            category: $category
        );

        foreach ($leaderboardDTO->getEntries() as $position => $entryDTO) {
            $this->findOrCreateLeaderboardEntry(
                leaderboard: $leaderboard,
                position: $position,
                characterName: $entryDTO->getCharacterName(),
                score: $entryDTO->getScore()
            );
        }
    }

    private function findOrCreateLeaderboardCategory(LeaderboardInterface $leaderboardDTO): LeaderboardCategory
    {
        $name = $leaderboardDTO->getName();
        $type = $leaderboardDTO->getType();
        $scoreLabel = $leaderboardDTO->getScoreLabel();

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'cat')
            ->from(from: LeaderboardCategory::class, alias: 'cat')
            ->where(predicates: 'cat.name = :name')
            ->andWhere('cat.type = :type')
            ->setParameter(key: 'name', value: $name)
            ->setParameter(key: 'type', value: $type);

        $query = $queryBuilder->getQuery();

        $category = $query->getOneOrNullResult();

        if (null === $category) {
            $category = new LeaderboardCategory();
            $category->setName(name: $name);
            $category->setType(type: $type);
            $category->setScoreLabel(scoreLabel: $scoreLabel);
            $this->entityManager->persist($category);
            $this->entityManager->flush();
        }

        return $category;
    }

    private function findOrCreateLeaderboard(
        DateTimeInterface $currentDateTime,
        GamePeriodInterface $gamePeriod,
        LeaderboardCategory $category
    ): Leaderboard {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'l')
            ->from(from: Leaderboard::class, alias: 'l')
            ->where(predicates: 'l.gamePeriod = :gamePeriod')
            ->andWhere('l.category = :category')
            ->setParameter(key: 'gamePeriod', value: $gamePeriod)
            ->setParameter(key: 'category', value: $category);

        $query = $queryBuilder->getQuery();

        $leaderboard = $query->getOneOrNullResult();

        if (null === $leaderboard) {
            $leaderboard = new Leaderboard();
            $leaderboard->setCreatedAt(createdAt: $currentDateTime);
            $leaderboard->setCategory(category: $category);
            $leaderboard->setGamePeriod(gamePeriod: $gamePeriod);
        }
        $leaderboard->setLastModifiedAt(lastModifiedAt: $currentDateTime);
        $this->entityManager->persist($leaderboard);
        $this->entityManager->flush();

        return $leaderboard;
    }

    private function findOrCreateLeaderboardEntry(
        Leaderboard $leaderboard,
        int $position,
        string $characterName,
        int $score
    ): LeaderboardEntry {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(select: 'le')
            ->from(from: LeaderboardEntry::class, alias: 'le')
            ->where(predicates: 'le.leaderboard = :leaderboard')
            ->andWhere('le.position = :position')
            ->setParameter(key: 'leaderboard', value: $leaderboard)
            ->setParameter(key: 'position', value: $position);

        $query = $queryBuilder->getQuery();

        $entry = $query->getOneOrNullResult();

        if (null === $entry) {
            $entry = new LeaderboardEntry();
            $entry->setLeaderboard(leaderboard: $leaderboard);
            $entry->setPosition(position: $position);
        }
        $entry->setCharacterName(characterName: $characterName);
        $entry->setScore(score: $score);
        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $entry;
    }
}
