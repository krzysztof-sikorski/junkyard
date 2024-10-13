<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix structure of leaderboard tables to match leaderboard DTOs';
    }

    private function checkLeaderboardCount(): void
    {
        $sql = 'SELECT COUNT(*) FROM nexus_leaderboard_category';
        $categoryCount = $this->connection->executeQuery(sql: $sql)->fetchOne();

        $sql = 'SELECT COUNT(*) FROM nexus_leaderboard';
        $leaderboardCount = $this->connection->executeQuery(sql: $sql)->fetchOne();

        $sql = 'SELECT COUNT(*) FROM nexus_leaderboard_entry';
        $entryCount = $this->connection->executeQuery(sql: $sql)->fetchOne();

        $this->abortIf(
            condition: ($categoryCount > 0) || ($leaderboardCount > 0) || ($entryCount > 0),
            message: 'This migration can only be executed on empty leaderboard tables!',
        );
    }

    public function preUp(Schema $schema): void
    {
        $this->checkLeaderboardCount();
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX nexus_leaderboard_category_uniq');
        $this->addSql('ALTER TABLE nexus_leaderboard_category DROP career');
        $this->addSql('ALTER TABLE nexus_leaderboard_category ADD type TEXT NOT NULL');
        $this->addSql(
            'CREATE UNIQUE INDEX nexus_leaderboard_category_uniq ON nexus_leaderboard_category (name, type)'
        );
        $this->addSql(
            'CREATE UNIQUE INDEX nexus_leaderboard_entry_uniq ON nexus_leaderboard_entry (leaderboard_id, position)'
        );
    }

    public function preDown(Schema $schema): void
    {
        $this->checkLeaderboardCount();
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX nexus_leaderboard_entry_uniq');
        $this->addSql('DROP INDEX nexus_leaderboard_category_uniq');
        $this->addSql('ALTER TABLE nexus_leaderboard_category DROP type');
        $this->addSql('ALTER TABLE nexus_leaderboard_category ADD career BOOLEAN NOT NULL');
        $this->addSql(
            'CREATE UNIQUE INDEX nexus_leaderboard_category_uniq ON nexus_leaderboard_category (name, career)'
        );
    }
}
