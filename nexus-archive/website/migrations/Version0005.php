<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version0005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add parser-related columns to nexus_raw_data table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<'SQL'
            ALTER TABLE nexus_raw_data
                ALTER form_data TYPE JSONB,
                ADD parsed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                ADD parser_errors JSONB DEFAULT NULL
            SQL
        );
        $this->addSql("COMMENT ON COLUMN nexus_raw_data.parsed_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE nexus_raw_data DROP parsed_at, DROP parser_errors, ALTER form_data TYPE JSON');
    }
}
