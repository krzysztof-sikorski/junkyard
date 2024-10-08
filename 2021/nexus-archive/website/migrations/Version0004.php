<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create leaderboard tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
            CREATE TABLE nexus_leaderboard (
                id UUID NOT NULL,
                title TEXT NOT NULL,
                value_title TEXT NOT NULL,
                career BOOLEAN NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                last_modified_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql('CREATE UNIQUE INDEX nexus_leaderboard_uniq ON nexus_leaderboard (title)');
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard.created_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard.last_modified_at IS '(DC2Type:datetimetz_immutable)'");

        $this->addSql(
            <<<'SQL'
            CREATE TABLE nexus_leaderboard_entry (
                id UUID NOT NULL,
                leaderboard_id UUID NOT NULL,
                character_name TEXT NOT NULL,
                position INT NOT NULL,
                value INT NOT NULL,
                created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                last_modified_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql(
            'CREATE INDEX nexus_leaderboard_entry_leaderboard_idx ON nexus_leaderboard_entry (leaderboard_id)'
        );
        $this->addSql(
            'CREATE UNIQUE INDEX nexus_leaderboard_entry_uniq ON nexus_leaderboard_entry (position)'
        );
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.leaderboard_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.created_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.last_modified_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql(
            <<<'SQL'
            ALTER TABLE nexus_leaderboard_entry ADD CONSTRAINT FK_33FD5D095CE067D8
            FOREIGN KEY (leaderboard_id) REFERENCES nexus_leaderboard (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE nexus_leaderboard_entry');
        $this->addSql('DROP TABLE nexus_leaderboard');
    }
}
