<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Redesign leaderboard tables to support game periods and better match DTOs';
    }

    private function checkLeaderboardCount(): void
    {
        $sql = 'SELECT COUNT(*) FROM nexus_leaderboard';
        $leaderboardCount = $this->connection->executeQuery(sql: $sql)->fetchOne();
        $this->abortIf(
            condition: $leaderboardCount > 0,
            message: 'This migration can only be executed on empty leaderboard tables!',
        );
    }

    public function preUp(Schema $schema): void
    {
        $this->checkLeaderboardCount();
    }

    public function up(Schema $schema): void
    {
        // create table nexus_leaderboard_category
        $this->addSql(
            <<<'SQL'
            CREATE TABLE nexus_leaderboard_category (
                id UUID NOT NULL,
                name TEXT NOT NULL,
                score_label TEXT NOT NULL,
                career BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )
            SQL
        );
        $this->addSql(
            'CREATE UNIQUE INDEX nexus_leaderboard_category_uniq ON nexus_leaderboard_category (name, career)'
        );
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_category.id IS '(DC2Type:uuid)'");

        // update nexus_leaderboard table
        $this->addSql('DROP INDEX nexus_leaderboard_uniq');
        $this->addSql("ALTER TABLE nexus_leaderboard ADD category_id UUID NOT NULL");
        $this->addSql('ALTER TABLE nexus_leaderboard ADD game_period_id INT NOT NULL');
        $this->addSql('ALTER TABLE nexus_leaderboard DROP title');
        $this->addSql('ALTER TABLE nexus_leaderboard DROP value_title');
        $this->addSql('ALTER TABLE nexus_leaderboard DROP career');
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard.category_id IS '(DC2Type:uuid)'");
        $this->addSql(
            <<<'SQL'
            ALTER TABLE nexus_leaderboard ADD CONSTRAINT FK_2557F33F6140100F
            FOREIGN KEY (category_id) REFERENCES nexus_leaderboard_category (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL
        );
        $this->addSql(
            <<<'SQL'
            ALTER TABLE nexus_leaderboard ADD CONSTRAINT FK_2557F33F3E2DBBDC
            FOREIGN KEY (game_period_id) REFERENCES nexus_game_period (id)
            NOT DEFERRABLE INITIALLY IMMEDIATE
            SQL
        );
        $this->addSql('CREATE INDEX nexus_leaderboard_category_idx ON nexus_leaderboard (category_id)');
        $this->addSql('CREATE INDEX nexus_leaderboard_game_period_idx ON nexus_leaderboard (game_period_id)');
        $this->addSql('CREATE UNIQUE INDEX nexus_leaderboard_uniq ON nexus_leaderboard (category_id, game_period_id)');

        // update nexus_leaderboard_entry table
        $this->addSql('DROP INDEX nexus_leaderboard_entry_uniq');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry DROP CONSTRAINT nexus_leaderboard_entry_pkey');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry DROP id');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry DROP created_at');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry DROP last_modified_at');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry RENAME COLUMN value TO score');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry ADD PRIMARY KEY (leaderboard_id, position)');
    }

    public function preDown(Schema $schema): void
    {
        $this->checkLeaderboardCount();
    }

    public function down(Schema $schema): void
    {
        // update nexus_leaderboard_entry table
        $this->addSql('ALTER TABLE nexus_leaderboard_entry DROP CONSTRAINT nexus_leaderboard_entry_pkey');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry RENAME COLUMN score TO value');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry ADD id UUID NOT NULL');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry ADD created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry ADD last_modified_at TIMESTAMP(0) WITH TIME ZONE NOT NULL');
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.created_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN nexus_leaderboard_entry.last_modified_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql('CREATE UNIQUE INDEX nexus_leaderboard_entry_uniq ON nexus_leaderboard_entry (position)');
        $this->addSql('ALTER TABLE nexus_leaderboard_entry ADD PRIMARY KEY (id)');

        // update nexus_leaderboard table
        $this->addSql('ALTER TABLE nexus_leaderboard DROP category_id');
        $this->addSql('ALTER TABLE nexus_leaderboard DROP game_period_id');
        $this->addSql('ALTER TABLE nexus_leaderboard ADD title TEXT NOT NULL');
        $this->addSql('ALTER TABLE nexus_leaderboard ADD value_title TEXT NOT NULL');
        $this->addSql('ALTER TABLE nexus_leaderboard ADD career BOOLEAN NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX nexus_leaderboard_uniq ON nexus_leaderboard (title)');

        // drop table nexus_leaderboard_category
        $this->addSql('DROP TABLE nexus_leaderboard_category');
    }
}
