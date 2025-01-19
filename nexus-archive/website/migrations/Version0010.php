<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Contract\Entity\Nexus\GamePeriodIdEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

use function array_merge;

final class Version0010 extends AbstractMigration
{
    private const TS_BREATH_5_LAUNCH = '2021-11-24 00:00:00 UTC';
    private const TS_BREATH_5_OUTER_PLANES = '2021-12-26 00:00:00 UTC';
    private const TS_BREATH_5_STRONGHOLDS = '2022-03-06 00:00:00 UTC';

    public function getDescription(): string
    {
        return 'Insert rows for known game periods (up to B5 Stronghold launch)';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
            INSERT INTO nexus_game_period (id, name, started_at, completed_at, current)
            VALUES (:id, :name, :startedAt, :completedAt, :current)
            SQL;
        $rows = [
            GamePeriodIdEnum::BREATH_5_LAUNCH => [
                'name' => 'Breath 5 (early after launch)',
                'startedAt' => self::TS_BREATH_5_LAUNCH,
                'completedAt' => self::TS_BREATH_5_OUTER_PLANES,
                'current' => false,
            ],
            GamePeriodIdEnum::BREATH_5_OUTER_PLANES => [
                'name' => 'Breath 5 (after opening of Outer Planes)',
                'startedAt' => self::TS_BREATH_5_OUTER_PLANES,
                'completedAt' => self::TS_BREATH_5_STRONGHOLDS,
                'current' => false,
            ],
            GamePeriodIdEnum::BREATH_5_STRONGHOLDS => [
                'name' => 'Breath 5 (after launch of Strongholds)',
                'startedAt' => self::TS_BREATH_5_STRONGHOLDS,
                'completedAt' => null,
                'current' => true,
            ],
        ];
        $types = [
            'name' => Types::TEXT,
            'startedAt' => Types::TEXT,
            'completedAt' => Types::TEXT,
            'current' => Types::BOOLEAN,
        ];
        foreach ($rows as $id => $row) {
            $params = array_merge(['id' => $id], $row);
            $this->addSql(sql: $sql, params: $params, types: $types);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            sql: 'DELETE FROM nexus_game_period WHERE id != :idBreath4',
            params: ['idBreath4' => GamePeriodIdEnum::BREATH_4]
        );
    }
}
