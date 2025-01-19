<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Contract\Entity\Nexus\GamePeriodIdEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create dictonary table for game periods';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
            CREATE TABLE nexus_game_period (
            id INT NOT NULL,
            name TEXT NOT NULL,
            started_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
            completed_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
            current BOOLEAN NOT NULL DEFAULT false,
            PRIMARY KEY(id))
            SQL
        );
        $this->addSql("COMMENT ON COLUMN nexus_game_period.started_at IS '(DC2Type:datetimetz_immutable)'");
        $this->addSql("COMMENT ON COLUMN nexus_game_period.completed_at IS '(DC2Type:datetimetz_immutable)'");

        $sql = <<<'SQL'
            INSERT INTO nexus_game_period (id, name, started_at, completed_at, current)
            VALUES (:id, :name, :startedAt, :completedAt, false)
            SQL;
        $params = [
            'id' => GamePeriodIdEnum::BREATH_4,
            'name' => 'Breath 3.5 (also known as Breath 4)',
            'startedAt' => '2015-07-25 00:00:00 UTC',
            'completedAt' => '2021-11-24 00:00:00 UTC',
        ];
        $this->addSql(sql: $sql, params: $params);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE nexus_game_period');
    }
}
